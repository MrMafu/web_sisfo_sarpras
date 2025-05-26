<?php

namespace App\Http\Controllers\API;

use App\Http\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\ItemUnitResource;
use App\Models\ItemUnit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class ItemUnitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $data = ItemUnitResource::collection(ItemUnit::with("item")->get());
        return ApiResponse::success($data, "Item units retrieved successfully.");
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            "item_id" => "required|exists:items,id",
            "sku"     => "required|string|unique:item_units,sku",
        ]);

        if ($validator->fails()) {
            return ApiResponse::error("Validation error.", 422, $validator->errors());
        }

        $unit = ItemUnit::create($validator->validated());
        $data = new ItemUnitResource($unit->load("item"));

        return ApiResponse::success($data, "Item unit created successfully.", 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id): JsonResponse
    {
        $itemUnit = ItemUnit::find($id);

        if (!$itemUnit) {
            return ApiResponse::error("Item unit not found.", 404);
        }

        $data = new ItemUnitResource($itemUnit->load("item"));
        return ApiResponse::success($data, "Item unit details retrieved.");
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ItemUnit $itemUnit): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            "status" => "required|in:available,borrowed",
        ]);

        if ($validator->fails()) {
            return ApiResponse::error("Validation error.", 422, $validator->errors());
        }

        $itemUnit->update($validator->validated());
        $data = new ItemUnitResource($itemUnit->load("item"));

        return ApiResponse::success($data, "Item unit updated successfully.");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ItemUnit $itemUnit): JsonResponse
    {
        $itemUnit->delete();
        return ApiResponse::noContent();
    }
}