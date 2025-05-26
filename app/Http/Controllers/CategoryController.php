<?php

namespace App\Http\Controllers;

use App\Http\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $allowedSorts = ["id", "slug", "name", "created_at", "updated_at"];
        $sort = $request->get("sort", "id");
        $direction = $request->get("direction", "asc") === "desc" ? "desc" : "asc";

        if (!in_array($sort, $allowedSorts)) {
            $sort = "id";
        }

        $query = Category::query();
        if ($search = $request->get("search")) {
            $query->where("name", "like", "%{$search}%");
        }

        $categories = $query->orderBy($sort, $direction)
            ->paginate(10)
            ->appends($request->only(["search", "sort", "direction"]));

        if ($request->ajax()) {
            return view("components.table", [
                "items"   => $categories,
                "headers" => [
                    "id"         => "ID",
                    "slug"       => "Slug",
                    "name"       => "Name",
                    "created_at" => "Created",
                    "updated_at" => "Updated",
                ],
                "sortField"     => $sort,
                "sortDirection" => $direction,
                "actions"       => true,
                "actionProps"   => [
                    "viewFields"  => ["id", "slug", "name", "created_at", "updated_at"],
                    "editFields"  => ["id", "slug", "name"],
                    "deleteRoute" => "categories.destroy",
                ]
            ]);
        }

        if ($request->expectsJson()) {
            $categories = Category::all();
            $data = CategoryResource::collection($categories);

            return ApiResponse::success($data, "Categories retrieved successfully.");
        }

        return view("categories.index", [
            "categories" => $categories,
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

        return view("categories.create");
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "slug" => "required|string|unique:categories,slug",
            "name" => "required|string",
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return ApiResponse::error("Validation error.", 422, $validator->errors());
            }
            return back()->withErrors($validator, "create")->withInput();
        }

        $category = Category::create($validator->validated());
        $data = new CategoryResource($category);

        if ($request->expectsJson()) {
            return ApiResponse::success($data, "Category created successfully.", 201);
        }

        return redirect()->route("categories.index")->with("status", "Category created successfully.");
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id)
    {
        $category = Category::find($id);
        if (!$category) {
            if ($request->expectsJson()) {
                return ApiResponse::error("Category not found.", 404);
            }
            abort(404);
        }

        $data = new CategoryResource($category);
        if ($request->expectsJson()) {
            return ApiResponse::success($data, "Category details retrieved.");
        }

        return view("categories.show", ["category" => $category]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, $id)
    {
        if ($request->expectsJson()) {
            return ApiResponse::error("Method not allowed.", 405);
        }

        $category = Category::findOrFail($id);
        $data = new CategoryResource($category);

        return view("categories.edit", compact("category"));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $category = Category::find($id);
        if (!$category) {
            if ($request->expectsJson()) {
                return ApiResponse::error("Category not found.", 404);
            }
            abort(404);
        }

        $validator = Validator::make($request->all(), [
            "slug" => "sometimes|required|string|unique:categories,slug,{$category->id}",
            "name" => "sometimes|required|string",
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return ApiResponse::error("Validation error.", 422, $validator->errors());
            }
            return back()->withErrors($validator, "edit")->withInput();
        }

        $category->update($validator->validated());
        $data = new CategoryResource($category);

        if ($request->expectsJson()) {
            return ApiResponse::success($data, "Category updated successfully.");
        }

        return redirect()->route("categories.index")->with("status", "Category updated successfully.");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        $category = Category::find($id);
        if (!$category) {
            if ($request->expectsJson()) {
                return ApiResponse::error("Category not found.", 404);
            }
            abort(404);
        }

        $category->delete();
        if ($request->expectsJson()) {
            return ApiResponse::noContent();
        }

        return redirect()->route("categories.index")->with("status", "Category deleted successfully.");
    }
}