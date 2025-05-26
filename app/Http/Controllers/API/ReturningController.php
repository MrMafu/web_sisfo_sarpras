<?php

namespace App\Http\Controllers\API;

use App\Http\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\ReturningResource;
use App\Models\Borrowing;
use App\Models\Returning;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ReturningController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $user = Auth::user();
        $query = Returning::with(["borrowing.user", "handler"]);

        if ($user->role === "user") {
            $query->whereHas("borrowing", fn($q) => $q->where("user_id", $user->id));
        }

        $data = ReturningResource::collection($query->get());
        return ApiResponse::success($data, "Returnings retrieved successfully.");
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        if (Auth::user()->role !== "user") {
            return ApiResponse::error("Forbidden: only users can request returning.", 403);
        }

        $validator = Validator::make($request->all(), [
            "borrowing_id"         => "required|exists:borrowings,id",
            "returned_quantity"    => "required|integer|min:1",
        ]);

        if ($validator->fails()) {
            return ApiResponse::error("Validation error.", 422, $validator->errors());
        }

        $returningData = $validator->validated();

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
    public function show($id): JsonResponse
    {
        $returning = Returning::find($id);

        if (!$returning) {
            return ApiResponse::error("Returning not found.");
        }

        $data = new ReturningResource($returning->load(["borrowing", "handler"]));
        return ApiResponse::success($data, "Return details retrieved.");
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Returning $returning): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            "status" => "required|in:approved,rejected"
        ]);

        if ($validator->fails()) {
            return ApiResponse::error("Validation error.", 422, $validator->errors());
        }

        if (Auth::user()->role !== "admin") {
            return ApiResponse::error("Forbidden", 403);
        }

        DB::beginTransaction();
        try {
            $returning->status = $request->status;
            $returning->handled_by = Auth::id();

            $borrowing = $returning->borrowing;
            $details = $borrowing->borrowingDetails()->with("itemUnit")->get();

            if ($returning->status === "approved" &&
                $returning->returned_quantity === $borrowing->quantity) {
                foreach ($details as $detail) {
                    $detail->itemUnit->update(["status" => "available"]);
                }

                $returning->returned_at = now();
                $borrowing->status = "returned";
                $borrowing->save();

            } else {
                foreach ($details as $detail) {
                    $detail->itemUnit->update(["status" => "unknown"]);
                }
            }

            $returning->save();
            DB::commit();
            
            $data = new ReturningResource($returning->load([
                "borrowing.borrowingDetails.itemUnit",
                "handler",
            ]));

            return ApiResponse::success($data, "Return updated successfully.");

        } catch (\Throwable $e) {
            DB::rollBack();
            return ApiResponse::error("Failed to update return.", 500, $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Returning $returning): JsonResponse
    {
        if ($returning->status !== "pending") {
            return ApiResponse::error("Only pending returns can be cancelled.");
        }

        $returning->delete();
        return ApiResponse::noContent();
    }
}