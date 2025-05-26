<?php

namespace App\Http\Controllers;

use App\Http\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\BorrowingResource;
use App\Models\Borrowing;
use App\Models\BorrowingDetail;
use App\Models\ItemUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BorrowingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $allowedSorts = ["id", "item_id", "user_id", "status", "due", "created_at", "updated_at"];
        $sort = $request->get("sort", "id");
        $direction = $request->get("direction", "asc") === "desc" ? "desc" : "asc";

        if (!in_array($sort, $allowedSorts)) {
            $sort = "id";
        }

        $query = Borrowing::with(["user", "item", "approver", "borrowingDetails.itemUnit"]);
        if ($search = $request->get("search")) {
            $query->whereHas("item", fn($q) => $q->where("name", "like", "%{$search}%"));
        }

        if ($status = $request->get("status")) {
            $query->where("status", $status);
        }

        $borrowings = $query->orderBy($sort, $direction)
            ->paginate(10)
            ->appends($request->only(["search", "status", "sort", "direction"]));

        if ($request->ajax()) {
            return view("components.table", [
                "items"   => $borrowings,
                "headers" => [
                    "id"         => "ID",
                    "item.name"  => "Item",
                    "user.username"  => "User",
                    "status"     => "Status",
                    "due"        => "Due Date",
                    "created_at" => "Created",
                    "updated_at" => "Updated",
                ],
                "sortField"     => $sort,
                "sortDirection" => $direction,
                "actions"       => true,
                "actionProps"   => [
                    "viewFields"  => ["id", "item.name", "user.username", "status", "due", "created_at", "updated_at"],
                    "editFields"  => ["status"],
                    "deleteRoute" => "borrowings.destroy",
                ],
            ]);
        }

        if ($request->expectsJson()) {
            if (Auth::user()->role === "user") {
                $query->where("user_id", Auth::id());
            }
            $data = BorrowingResource::collection($query->get());
            return ApiResponse::success($data, "Borrowings retrieved successfully.");
        }

        return view("borrowings.index", [
            "borrowings" => $borrowings,
            "sort"       => $sort,
            "direction"  => $direction,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        if ($request->expectsJson()) {
            return ApiResponse::error("Method not allowed.", 405);
        }
        abort(404);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!$request->expectsJson()) {
            abort(404);
        }

        if (Auth::user()->role !== "user") {
            return ApiResponse::error("Forbidden: only users can request borrowing.", 403);
        }

        $validator = Validator::make($request->all(), [
            "item_id"  => "required|exists:items,id",
            "quantity" => "required|integer|min:1",
            "due"      => "required|date|after:now",
        ]);

        if ($validator->fails()) {
            return ApiResponse::error("Validation error.", 422, $validator->errors());
        }

        $borrowingData = $validator->validated();
        $availableUnits = ItemUnit::where("item_id", $borrowingData["item_id"])
            ->where("status", "available")
            ->count();

        if ($availableUnits < $borrowingData["quantity"]) {
            return ApiResponse::error(
                "Not enough available units. Requested {$borrowingData["quantity"]}, but only {$availableUnits} available."
            );
        }

        $borrowing = Borrowing::create([
            "user_id"  => Auth::id(),
            "item_id"  => $borrowingData["item_id"],
            "quantity" => $borrowingData["quantity"],
            "due"      => $borrowingData["due"],
        ]);

        $data = new BorrowingResource($borrowing);
        return ApiResponse::success($data, "Borrowing request created.", 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id)
    {
        $borrowing = Borrowing::with([
            "user", "item", "approver", "borrowingDetails.itemUnit"
        ])->find($id);

        if (!$borrowing) {
            if ($request->expectsJson()) {
                return ApiResponse::error("Borrowing not found.", 404);
            }
            abort(404);
        }

        $data = new BorrowingResource($borrowing);
        if ($request->expectsJson()) {
            if (Auth::user()->role === "user" && $borrowing->user_id !== Auth::id()) {
                return ApiResponse::error("Forbidden.", 403);
            }
            return ApiResponse::success($data, "Borrowing details retrieved.");
        }

        return view("borrowings.show", compact("borrowing"));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $borrowing = Borrowing::find($id);
        if (!$borrowing) {
            if ($request->expectsJson()) {
                return ApiResponse::error("Borrowing not found.", 404);
            }
            abort(404);
        }

        if (Auth::user()->role !== "admin") {
            if ($request->expectsJson()) {
                return ApiResponse::error("Forbidden.", 403);
            }
            abort(403);
        }

        $validator = Validator::make($request->all(), [
            "status" => "required|in:approved,rejected,returned"
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return ApiResponse::error("Validation error.", 422, $validator->errors());
            }
            return back()->withErrors($validator, "edit")->withInput();
        }

        $newStatus = $request->status;
        DB::beginTransaction();
        try {
            $borrowing->status = $newStatus;
            if ($newStatus === "approved") {
                $borrowing->approved_at = now();
                $borrowing->approved_by = Auth::id();
                $borrowing->save();

                $units = ItemUnit::where("item_id", $borrowing->item_id)
                    ->where("status", "available")
                    ->orderBy("sku", "asc")
                    ->lockForUpdate()
                    ->take($borrowing->quantity)
                    ->get();
                
                if ($units->count() < $borrowing->quantity) {
                    DB::rollBack();
                    
                    $msg = "Not enough available units.";
                    if ($request->expectsJson()) {
                        return ApiResponse::error($msg);
                    } else {
                        return back()->withErrors(["quantity" => $msg]);
                    }
                }

                foreach ($units as $unit) {
                    $unit->update(["status" => "borrowed"]);
                    BorrowingDetail::create([
                        "borrowing_id" => $borrowing->id,
                        "item_unit_id" => $unit->id,
                    ]);
                }

            } else if ($newStatus === "rejected") {
                $borrowing->update();
            } else if ($newStatus === "returned") {
                $borrowing->update();
            }

            DB::commit();

            $borrowing->load(["user", "item", "approver", "borrowingDetails.itemUnit", "returning"]);
            $data = new BorrowingResource($borrowing);

            if ($request->expectsJson()) {
                return ApiResponse::success($data, "Borrowing updated successfully.");
            }

            return redirect()->route("borrowings.show", $borrowing->id)
                ->with("status", "Borrowing updated successfully.");

        } catch (\Throwable $throw) {
            DB::rollBack();
            if ($request->expectsJson()) {
                return ApiResponse::error("Failed to update borrowing.", 500, $throw->getMessage());
            }

            return back()->withErrors(["error" => "Failed to update borrowing."]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        if (!$request->expectsJson()) {
            abort(404);
        }

        if (Auth::user()->role !== "user") {
            return ApiResponse::error("Forbidden: only users can cancel.", 403);
        }

        $borrowing = Borrowing::find($id);
        if (!$borrowing) {
            return ApiResponse::error("Borrowing not found.", 404);
        }

        if ($borrowing->status !== "pending") {
            return ApiResponse::error("Only pending borrowings can be cancelled.");
        }

        $borrowing->delete();
        return ApiResponse::noContent();
    }
}