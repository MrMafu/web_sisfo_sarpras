<?php

namespace App\Http\Controllers\API;

use App\Http\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\BorrowingResource;
use App\Models\Borrowing;
use App\Models\BorrowingDetail;
use App\Models\ItemUnit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BorrowingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $user = Auth::user();
        $query = Borrowing::with(["user", "item", "approver", "borrowingDetails.itemUnit"]);

        if ($user->role === "user") {
            $query->where("user_id", $user->id);
        }

        $data = BorrowingResource::collection($query->get());
        return ApiResponse::success($data, "Borrowings retrieved successfully.");
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
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
    public function show($id): JsonResponse
    {
        $borrowing = Borrowing::find($id);

        if (!$borrowing) {
            return ApiResponse::error("Borrowing not found.", 404);
        }

        $data = new BorrowingResource($borrowing->load([
            "user", "item", "approver", "borrowingDetails.itemUnit"
        ]));

        return ApiResponse::success($data, "Borrowing details retrieved.");
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Borrowing $borrowing): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            "status" => "required|in:approved,rejected,returned"
        ]);

        if ($validator->fails()) {
            return ApiResponse::error("Validation error.", 422, $validator->errors());
        }

        $newStatus = $request->status;

        if (Auth::user()->role !== "admin") {
            return ApiResponse::error("Forbidden.", 403);
        }

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
                    return ApiResponse::error("Not enough available units.");
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

            return ApiResponse::success($data, "Borrowing updated successfully.");

        } catch (\Throwable $throw) {
            DB::rollBack();
            return ApiResponse::error("Failed to update borrowing.", 500, $throw->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Borrowing $borrowing): JsonResponse
    {
        if ($borrowing->status !== "pending") {
            return ApiResponse::error("Only pending borrowings can be cancelled.");
        }

        $borrowing->delete();
        return ApiResponse::noContent();
    }
}