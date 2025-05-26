<?php

namespace App\Http\Controllers;

use App\Http\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $allowedSorts = ["id", "username", "role", "created_at", "updated_at"];
        $sort = $request->get("sort", "id");
        $direction = $request->get("direction", "asc") === "desc" ? "desc" : "asc";

        if (!in_array($sort, $allowedSorts)) {
            $sort = "id";
        }

        $query = User::query();
        if ($search = $request->get("search")) {
            $query->where("username", "like", "%{$search}%");
        }

        if ($role = $request->get("role")) {
            $query->where("role", $role);
        }

        $users = $query->orderBy($sort, $direction)
            ->paginate(10)
            ->appends($request->only(["search", "role", "sort", "direction"]));

        if ($request->ajax()) {
            return view("components.table", [
                "items"   => $users,
                "headers" => [
                    "id"         => "ID",
                    "username"   => "Username",
                    "role"       => "Role",
                    "created_at" => "Created",
                    "updated_at" => "Updated",
                ],
                "sortField"     => $sort,
                "sortDirection" => $direction,
                "actions"       => true,
                "actionProps"   => [
                    "viewFields"  => ["id", "username", "role", "created_at", "updated_at"],
                    "editFields"  => ["id", "username", "role"],
                    "deleteRoute" => "users.destroy",
                ]
            ]);
        }
        
        if ($request->expectsJson()) {
            $users = User::all();
            $data = UserResource::collection($users);

            return ApiResponse::success($data, "Users retrieved successfully.");
        }

        return view("users.index", [
            "users"     => $users,
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

        return view("users.create");
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "username" => "required|string|unique:users,username",
            "password" => "required|string|min:5",
            "role"     => "required|in:admin,user",
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return ApiResponse::error("Validation error.", 422, $validator->errors());
            }
            return back()->withErrors($validator, "create")->withInput();
        }

        $user = Auth::user();
        if (!$user || $user->role !== "admin") {
            if ($request->expectsJson()) {
                return ApiResponse::error("Forbidden.", 403);
            }
            abort(403);
        }

        $user = User::create([
            "username" => $request->username,
            "password" => Hash::make($request->password),
            "role"     => $request->role,
        ]);

        $data = new UserResource($user);

        if ($request->expectsJson()) {
            return ApiResponse::success($data, "User created successfully.", 201);
        }

        return redirect()->route("users.index")->with("status", "User created successfully.");
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            if ($request->expectsJson()) {
                return ApiResponse::error("User not found", 404);
            }
            abort(404);
        }

        $data = new UserResource($user);

        if ($request->expectsJson()) {
            return ApiResponse::success($data, "User details retrieved.");
        }

        return view("users.show", ["user" => $user]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, $id)
    {
        if ($request->expectsJson()) {
            return ApiResponse::error("Method not allowed.", 405);
        }

        $user = User::findOrFail($id);
        $data = new UserResource($user);

        return view("users.edit", compact("user"));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            if ($request->expectsJson()) {
                return ApiResponse::error("User not found.", 404);
            }
            abort(404);
        }

        $validator = Validator::make($request->all(), [
            "username" => "sometimes|required|string|unique:users,username,{$user->id}",
            "password" => "sometimes|nullable|string|min:5",
            "role"     => "sometimes|required|in:admin,user",
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return ApiResponse::error("Validation error.", 422, $validator->errors());
            }
            return back()->withErrors($validator, "edit")->withInput();
        }

        $userData = $validator->validated();
        if (!empty($userData["password"])) {
            $userData["password"] = Hash::make($userData["password"]);
        } else {
            unset($userData["password"]);
        }

        $user->update($userData);
        $data = new UserResource($user);

        if ($request->expectsJson()) {
            return ApiResponse::success($data, "User updated successfully.");
        }

        return redirect()->route("users.index")->with("status", "User updated successfully.");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            if ($request->expectsJson()) {
                return ApiResponse::error("User not found", 404);
            }
            abort(404);
        }

        $user->delete();
        if ($request->expectsJson()) {
            return ApiResponse::noContent();
        }

        return redirect()->route("users.index")->with("status", "User deleted successfully.");
    }
}