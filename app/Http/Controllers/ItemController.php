<?php

namespace App\Http\Controllers;

use App\Http\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\ItemResource;
use App\Models\Category;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $allowedSorts = ["id", "name", "category_id", "stock", "created_at", "updated_at"];
        $sort = $request->get("sort", "id");
        $direction = $request->get("direction", "asc") === "desc" ? "desc" : "asc";

        if (!in_array($sort, $allowedSorts)) {
            $sort = "id";
        }

        $query = Item::with("category");
        if ($search = $request->get("search")) {
            $query->where("name", "like", "%{$search}%");
        }

        if ($categoryId = $request->get("category_id")) {
            $query->where("category_id", (int) $categoryId);
        }

        $query->orderBy($sort, $direction);

        $items = $query->paginate(10)
            ->appends($request->only(["search", "category_id", "sort", "direction"]));
        
        if ($request->ajax()) {
            return view("components.table", [
                "items"   => $items,
                "headers" => [
                    "id"         => "ID",
                    "name"       => "Name",
                    "category.name"   => "Category",
                    "stock"      => "Stock",
                    "image"      => "Image",
                    "created_at" => "Created",
                    "updated_at" => "Updated",
                ],
                "sortField"     => $sort,
                "sortDirection" => $direction,
                "actions"       => true,
                "actionProps"   => [
                    "viewFields"  => ["id", "name", "category.name", "stock", "created_at", "updated_at"],
                    "editFields"  => ["id", "name", "category_id"],
                    "deleteRoute" => "items.destroy",
                ],
            ]);
        }

        if ($request->expectsJson()) {
            $data = ItemResource::collection($items);
            return ApiResponse::success($data, "Items retrieved successfully.");
        }

        return view("items.index", [
            "items"     => $items,
            "sort"      => $sort,
            "direction" => $direction,
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

        $categories = Category::all();
        return view("items.create", compact("categories"));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "category_id" => "required|exists:categories,id",
            "name"        => "required|string",
            "image"       => "required|image",
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return ApiResponse::error("Validation error.", 422, $validator->errors());
            }
            return back()->withErrors($validator, "create")->withInput();
        }

        $itemData = $validator->validated();
        if ($request->hasFile("image")) {
            $file = $request->file("image");
            $path = $file->storeAs("item-img", $itemData["name"] . "." . $file->getClientOriginalExtension(), "public");
            $itemData["image"] = url(Storage::url($path));
        }

        $item = Item::create($itemData);
        $data = new ItemResource($item->load("category"));

        if ($request->expectsJson()) {
            return ApiResponse::success($data, "Item created successfully.", 201);
        }

        return redirect()->route("items.index")->with("status", "Item created successfully.");
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id)
    {
        $item = Item::with(["category", "itemUnits"])->find($id);
        if (!$item) {
            if ($request->expectsJson()) {
                return ApiResponse::error("Item not found.", 404);
            }
            abort(404);
        }

        $data = new ItemResource($item);
        if ($request->expectsJson()) {
            return ApiResponse::success($data, "Item details retrieved.");
        }

        return view("items.show", compact("item"));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, $id)
    {
        if ($request->expectsJson()) {
            return ApiResponse::error("Method not allowed.", 405);
        }

        $item = Item::findOrFail($id);
        $categories = Category::all();

        return view("items.edit", compact("item", "categories"));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $item = Item::find($id);
        if (!$item) {
            if ($request->expectsJson()) {
                return ApiResponse::error("Item not found.", 404);
            }
            abort(404);
        }

        $validator = Validator::make($request->all(), [
            "category_id"  => "sometimes|required|exists:categories,id",
            "name"         => "sometimes|required|string",
            "image"        => "sometimes|required|image",
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return ApiResponse::error("Validation error.", 422, $validator->errors());
            }
            return back()->withErrors($validator, "edit")->withInput();
        }

        $itemData = $validator->validated();
        if ($request->hasFile("image")) {
            $file = $request->file("image");
            $path = $file->storeAs("item-img", ($itemData["name"] ?? $item->name) . "." . $file->getClientOriginalExtension(), "public");
            $itemData["image"] = url(Storage::url($path));
        }

        $item->update($itemData);
        $data = new ItemResource($item->load("category"));

        if ($request->expectsJson()) {
            return ApiResponse::success($data, "Item updated successfully.");
        }

        return redirect()->route("items.index")->with("status", "Item updated successfully.");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        $item = Item::find($id);
        if (!$item) {
            if ($request->expectsJson()) {
                return ApiResponse::error("Item not found.", 404);
            }
            abort(404);
        }

        $item->delete();
        if ($request->expectsJson()) {
            return ApiResponse::noContent();
        }

        return redirect()->route("items.index")->with("status", "Item deleted successfully.");
    }

    // Change item image (Postman / API only)
    public function changeImage(Request $request, $id)
    {
        $item = Item::find($id);
        if (!$item) {
            return ApiResponse::error("Item not found.", 404);
        }

        $validator = Validator::make($request->all(), [
            "image" => "required|image"
        ]);

        if ($validator->fails()) {
            return ApiResponse::error("Validation error.", 422, $validator->errors());
        }

        $file = $request->file("image");
        $path = $file->storeAs("item-img", $item->name . "." . $file->extension(), "public");
        $image = url(Storage::url($path));

        $item->update(["image" => $image]);
        $data = new ItemResource($item->load("category"));

        return ApiResponse::success($data, "Item image updated successfully.");
    }
}