<?php

namespace App\Http\Controllers;

use App\Http\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\ItemUnitResource;
use App\Models\Item;
use App\Models\ItemUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ItemUnitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $allowedSorts = ["id", "sku", "status", "item_id", "created_at", "updated_at"];
        $sort = $request->get("sort", "id");
        $direction = $request->get("direction", "asc") === "desc" ? "desc" : "asc";

        if (!in_array($sort, $allowedSorts)) {
            $sort = "id";
        }

        $query = ItemUnit::with("item");
        if ($search = $request->get("search")) {
            $query->where("sku", "like", "%{$search}%");
        }

        if ($status = $request->get("status")) {
            $query->where("status", $status);
        }

        $units = $query->orderBy($sort, $direction)
            ->paginate(10)
            ->appends($request->only(["search", "status", "sort", "direction"]));

        if ($request->ajax()) {
            return view("components.table", [
                "items"   => $units,
                "headers" => [
                    "id"         => "ID",
                    "sku"        => "SKU",
                    "status"     => "Status",
                    "item.name"  => "Item",
                    "created_at" => "Created",
                    "updated_at" => "Updated",
                ],
                "sortField"     => $sort,
                "sortDirection" => $direction,
                "actions"       => true,
                "actionProps"   => [
                    "viewFields"  => ["id", "sku", "status", "item.name", "created_at", "updated_at"],
                    "editFields"  => ["id", "sku", "status", "item_id"],
                    "deleteRoute" => "item_units.destroy",
                ],
            ]);
        }

        if ($request->expectsJson()) {
            $units = ItemUnit::all();
            $data = ItemUnitResource::collection($units);

            return ApiResponse::success($data, "Item units retrieved successfully.");
        }

        return view("item_units.index", [
            "itemUnits" => $units,
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

        $items = Item::all();
        return view("item_units.create", compact("items"));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "item_id" => "required|exists:items,id",
            "sku"     => "required|string|unique:item_units,sku",
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return ApiResponse::error("Validation error.", 422, $validator->errors());
            }
            return back()->withErrors($validator, "create")->withInput();
        }

        $unit = ItemUnit::create($validator->validated());
        $data = new ItemUnitResource($unit->load("item"));

        if ($request->expectsJson()) {
            return ApiResponse::success($data, "Item unit created successfully.", 201);
        }

        return redirect()->route("item_units.index")->with("status", "Item unit created successfully.");
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id)
    {
        $unit = ItemUnit::with("item")->find($id);
        if (!$unit) {
            if ($request->expectsJson()) {
                return ApiResponse::error("Item unit not found.", 404);
            }
            abort(404);
        }

        $data = new ItemUnitResource($unit);
        if ($request->expectsJson()) {
            return ApiResponse::success($data, "Item unit details retrieved.");
        }

        return view("item_units.show", compact("unit"));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, $id)
    {
        if ($request->expectsJson()) {
            return ApiResponse::error("Method not allowed.", 405);
        }

        $unit = ItemUnit::findOrFail($id);
        $items = Item::all();

        return view("item_units.edit", compact("unit", "items"));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $unit = ItemUnit::find($id);
        if (!$unit) {
            if ($request->expectsJson()) {
                return ApiResponse::error("Item unit not found.", 404);
            }
            abort(404);
        }

        $validator = Validator::make($request->all(), [
            "item_id" => "sometimes|required|exists:items,id",
            "sku"     => "sometimes|required|string|unique:item_units,sku,{$unit->id}",
            "status"  => "required|in:available,borrowed",
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return ApiResponse::error("Validation error.", 422, $validator->errors());
            }
            return back()->withErrors($validator, "edit")->withInput();
        }

        $unit->update($validator->validated());
        $data = new ItemUnitResource($unit->load("item"));

        if ($request->expectsJson()) {
            return ApiResponse::success($data, "Item unit updated successfully.");
        }

        return redirect()->route("item_units.index")->with("status", "Item unit updated successfully.");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        $unit = ItemUnit::find($id);
        if (!$unit) {
            if ($request->expectsJson()) {
                return ApiResponse::error("Item unit not found.", 404);
            }
            abort(404);
        }

        $unit->delete();
        if ($request->expectsJson()) {
            return ApiResponse::noContent();
        }

        return redirect()->route("item_units.index")->with("status", "Item unit deleted successfully.");
    }
}
