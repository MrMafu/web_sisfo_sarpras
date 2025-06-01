<?php

namespace App\Http\Controllers;

use App\Http\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\ReturningResource;
use App\Models\Returning;
use App\Models\Borrowing;
use App\Models\ItemUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ReturningController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $allowedSorts = ["id", "borrowing_id", "returned_quantity", "status", "created_at", "updated_at"];
        $sort = $request->get("sort", "id");
        $direction = $request->get("direction", "asc") === "desc" ? "desc" : "asc";

        if (!in_array($sort, $allowedSorts)) {
            $sort = "id";
        }

        $query = Returning::with(["borrowing.user", "borrowing.item", "handler"]);
        if ($search = $request->get("search")) {
            $query->whereHas("borrowing.item", fn($q) => $q->where("name", "like", "%{$search}%"));
        }

        if ($status = $request->get("status")) {
            $query->where("status", $status);
        }

        $returnings = $query->orderBy($sort, $direction)
            ->paginate(10)
            ->appends($request->only(["search", "status", "sort", "direction"]));
        
        if ($request->ajax()) {
            return view("components.table", [
                "items"   => $returnings,
                "headers" => [
                    "id"                      => "ID",
                    "borrowing.item.name"     => "Item",
                    "borrowing.user.username" => "User",
                    "returned_quantity"       => "Qty Returned",
                    "status"                  => "Status",
                    "created_at"              => "Requested",
                    "updated_at"              => "Updated",
                ],
                "sortField"     => $sort,
                "sortDirection" => $direction,
                "actions"       => true,
                "actionProps"   => ["showRoute" => "returnings.show"],
            ]);
        }

        if ($request->expectsJson()) {
            if (Auth::user()->role === "user") {
                $query->whereHas("borrowing", fn($q) => $q->where("user_id", Auth::id()));
            }
            $data = ReturningResource::collection($query->get());
            return ApiResponse::success($data, "Returnings retrieved successfully.");
        }

        return view("returnings.index", [
            "returnings" => $returnings,
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
            "borrowing_id"      => "required|exists:borrowings,id",
            "returned_quantity" => "required|integer|min:1",
        ]);

        if ($validator->fails()) {
            return ApiResponse::error("Validation error.", 422, $validator->errors());
        }

        $returningData = $validator->validated();
        $borrowing = Borrowing::findOrFail($returningData["borrowing_id"]);

        if ($returningData["returned_quantity"] != $borrowing->quantity) {
            return ApiResponse::error("Returned quantity must be exactly {$borrowing->quantity}.", 422);
        }

        $returning = Returning::create([
            "borrowing_id"      => $returningData["borrowing_id"],
            "returned_quantity" => $returningData["returned_quantity"],
            "status"            => "pending",
        ]);

        $data = new ReturningResource($returning);
        return ApiResponse::success($data, "Return request created.", 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id)
    {
        $returning = Returning::with([
            "borrowing.borrowingDetails.itemUnit", "borrowing.user", "handler", "borrowing.item"
        ])->find($id);

        if (!$returning) {
            if ($request->expectsJson()) {
                return ApiResponse::error("Returning not found.", 404);
            }
            abort(404);
        }

        $returnedUnits = $returning->borrowing->borrowingDetails
            ->take($returning->returned_quantity);

        $notReturnedUnits = $returning->borrowing->borrowingDetails
            ->slice($returning->returned_quantity);

        $data = new ReturningResource($returning);
        if ($request->expectsJson()) {
            if (Auth::user()->role === "user" && $returning->borrowing->user_id !== Auth::id()) {
                return ApiResponse::error("Forbidden.", 403);
            }
            return ApiResponse::success($data, "Return details retrieved.");
        }

        return view("returnings.show", [
            "returning"        => $returning,
            "returnedUnits"    => $returnedUnits,
            "notReturnedUnits" => $notReturnedUnits
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request)
    {
        if ($request->expectsJson()) {
            return ApiResponse::error("Method not allowed.", 405);
        }
        abort(404);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $returning = Returning::find($id);
        if (!$returning) {
            if ($request->expectsJson()) {
                return ApiResponse::error("Returning not found.", 404);
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
            "status" => "required|in:approved,rejected"
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return ApiResponse::error("Validation error.", 422, $validator->errors());
            }
            return back()->withErrors($validator, "edit")->withInput();
        }

        DB::beginTransaction();
        try {
            $returning->status = $request->status;
            $returning->handled_by = Auth::id();

            $borrowing = $returning->borrowing;
            $details = $borrowing->borrowingDetails()->with("itemUnit")->get();

            if ($returning->status === "approved") {
                foreach ($details as $detail) {
                    $detail->itemUnit->update(["status" => ItemUnit::statusAvailable]);
                }

                $returning->returned_at = now();
                $borrowing->status = Borrowing::statusReturned;
                $borrowing->save();

            } else {
                $borrowing->status = Borrowing::statusOverdue;
                foreach ($details as $detail) {
                    $detail->itemUnit->update(["status" => ItemUnit::statusLost]);
                }
            }

            $returning->save();
            DB::commit();
            
            $data = new ReturningResource($returning->load([
                "borrowing.borrowingDetails.itemUnit",
                "handler",
            ]));

            if ($request->expectsJson()) {
                return ApiResponse::success($data, "Return updated successfully.");
            }

            return redirect()->route("returnings.show", $returning->id)
                ->with("status", "Return updated successfully.");

        } catch (\Throwable $throw) {
            DB::rollBack();
            if ($request->expectsJson()) {
                return ApiResponse::error("Failed to update return.", 500, $throw->getMessage());
            }

            return back()->withErrors(["error" => "Failed to update return."]);
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

        $returning = Returning::find($id);
        if (!$returning) {
            return ApiResponse::error("Returning not found.", 404);
        }

        if ($returning->status !== "pending") {
            return ApiResponse::error("Only pending returns can be cancelled.");
        }

        $returning->delete();
        return ApiResponse::noContent();
    }
}