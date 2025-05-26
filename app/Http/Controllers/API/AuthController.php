<?php

namespace App\Http\Controllers\API;

use App\Http\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            "username" => "required|string|exists:users,username",
            "password" => "required|string",
        ]);

        if ($validator->fails()) {
            return ApiResponse::error("Validation error.", 422, $validator->errors());
        }

        $user = User::where("username", $request->username)->first();
        if (!Hash::check($request->password, $user->password)) {
            return ApiResponse::error("Incorect username or password.", 401);
        }

        $data = [
            "user" => new UserResource($user->makeHidden("password")),
            "token" => $user->createToken("api-token")->plainTextToken,
        ];

        return ApiResponse::success($data, "Login successful.");
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::guard("sanctum")->user()->tokens()->delete();
        return ApiResponse::success(null, "Successfully logged out.");
    }
}