<?php

namespace App\Http\Controllers\API;

use App\Http\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $users = User::all();
        $data = UserResource::collection($users);

        return ApiResponse::success($data, "Users retrieved successfully.");
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            "username" => "required|string|unique:users,username",
            "password" => "required|string|confirmed|min:5",
            "role"     => "required|in:admin,user",
        ]);

        if ($validator->fails()) {
            return ApiResponse::error("Validation error.", 422, $validator->errors());
        }

        if ($request->user()->role !== "admin") {
            return ApiResponse::error("Forbidden.", 403);
        }

        $user = User::create([
            "username" => $request->username,
            "password" => Hash::make($request->password),
            "role"     => $request->role,
        ]);

        $data = new UserResource($user);
        return ApiResponse::success($data, "User created successfully.", 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id): JsonResponse
    {
        $user = User::find($id);
        
        if (!$user) {
            return ApiResponse::error("User not found", 404);
        }

        $data = new UserResource($user);
        return ApiResponse::success($data, "User details retrieved.");
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $user = User::find($id);

        if (!$user) {
            return ApiResponse::error("User not found.", 404);
        }

        $validator = Validator::make($request->all(), [
            "username" => "sometimes|string|unique:users,username," . $user->id,
            "role"     => "sometimes|in:admin,user",
        ]);

        if ($validator->fails()) {
            return ApiResponse::error("Validation error.", 422, $validator->errors());
        }

        $userData = $validator->validated();
        if (isset($userData["password"])) {
            $userData = Hash::make($userData["password"]);
        }

        $user->update($userData);
        $data = new UserResource($user);

        return ApiResponse::success($data, "User updated successfully.");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            return ApiResponse::error("User not found", 404);
        }

        $user->delete();
        return ApiResponse::noContent();
    }
}