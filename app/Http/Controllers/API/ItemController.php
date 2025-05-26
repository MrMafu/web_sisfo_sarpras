<?php

namespace App\Http\Controllers\API;

use App\Http\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\ItemResource;
use App\Models\Item;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $data = ItemResource::collection(Item::with("category")->get());
        return ApiResponse::success($data, "Items retrieved successfully.");
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            "category_id" => "required|exists:categories,id",
            "name"        => "required|string",
            "image"       => "nullable|image",
        ]);

        if ($validator->fails()) {
            return ApiResponse::error("Validation error.", 422, $validator->errors());
        }

        $itemData = $validator->validated();

        // force stock to int (postman)
        $itemData["stock"] = intval($itemData["stock"]);

        if ($request->hasFile("image")) {
            $file = $request->file("image");
            $path = $file->storeAs("item-img", $itemData["name"] . "." . $file->extension(), "public");
            $itemData["image"] = url(Storage::url($path));
        }
        
        $item = Item::create($itemData);
        $data = new ItemResource($item->load("category"));

        return ApiResponse::success($data, "Item created successfully.", 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id): JsonResponse
    {
        $item = Item::find($id);

        if (!$item) {
            return ApiResponse::error("Item not found.", 404);
        }

        $data = new ItemResource($item->load("category"));
        return ApiResponse::success($data, "Item details retrieved.");
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Item $item): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            "category_id"  => "sometimes|exists:categories,id",
            "name"         => "sometimes|string",
            "image"        => "sometimes|image",
        ]);

        if ($validator->fails()) {
            return ApiResponse::error("Validation error.", 422, $validator->errors());
        }

        $itemData = $validator->validated();

        if ($request->hasFile("image")) {
            $file = $request->file("image");
            $path = $file->storeAs("item-img", $itemData["name"] . "." . $file->extension(), "public");
            $itemData["image"] = url(Storage::url($path));
        }

        $item->update($itemData);
        // $item->load("category");
        $data = new ItemResource($item->load("category"));

        return ApiResponse::success($data, "Item updated successfully.");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Item $item): JsonResponse
    {
        $item->delete();
        return ApiResponse::noContent();
    }

    public function changeImage(Request $request, Item $item): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            "image" => "required|image"
        ]);

        if ($validator->fails()) {
            return ApiResponse::error("Validation error.", 422, $validator->errors());
        }

        $itemData = $validator->validated();

        $file = $request->file("image");
        $path = $file->storeAs("item-img", $item->name . "." . $file->extension(), "public");
        $image = url(Storage::url($path));

        $item->update([
            "image" => $image
        ]);

        $data = new ItemResource($item->load("category"));
        return ApiResponse::success($data, "Item updated successfully.");
    }
}