<?php

namespace App\Http\Controllers\API;

use App\Http\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $data = CategoryResource::collection(Category::all());
        return ApiResponse::success($data, "Categories retrieved successfully.");
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            "slug" => "required|string|unique:categories,slug",
            "name" => "required|string",
        ]);

        if ($validator->fails()) {
            return ApiResponse::error("Validation error.", 422, $validator->errors());
        }

        $cat = Category::create($validator->validated());
        $data = new CategoryResource($cat);

        return ApiResponse::success($data, "Category created successfully.", 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id): JsonResponse
    {
        $category = Category::find($id);

        if (!$category) {
            return ApiResponse::error("Category not found.", 404);
        }

        $data = new CategoryResource($category);
        return ApiResponse::success($data, "Category details retrieved.");
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            "slug" => "required|string|unique:categories,slug," . $category->id,
            "name" => "required|string",
        ]);

        if ($validator->fails()) {
            return ApiResponse::error("Validation error.", 422, $validator->errors());
        }

        $category->update($validator->validated());
        $data = new CategoryResource($category);

        return ApiResponse::success($data, "Category updated successfully.");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category): JsonResponse
    {
        $category->delete();
        return ApiResponse::noContent();
    }
}