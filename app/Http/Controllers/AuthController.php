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

class AuthController extends Controller
{
    public function loginForm(Request $request)
    {
        if ($request->expectsJson()) {
            return ApiResponse::error("Endpoint requires POST with credentials.", 405);
        }

        return view("auth.login");
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "username" => "required|string|exists:users,username",
            "password" => "required|string",
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return ApiResponse::error("Validation error.", 422, $validator->errors());
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $user = User::where("username", $request->username)->first();
        if (!Hash::check($request->password, $user->password)) {
            if ($request->expectsJson()) {
                return ApiResponse::error("Incorrect username or password.", 401);
            }
            return back()
                ->withErrors(["password" => "The password you entered is incorrect."])
                ->withInput();
        }

        if (!$request->expectsJson() && $user->role !== "admin") {
            return back()
                ->withErrors(["login" => "Access denied: administrators only."])
                ->withInput();
        }

        Auth::login($user, $request->has("remember"));
        $token = $user->createToken("api-token")->plainTextToken;

        if ($request->expectsJson()) {
            $data = [
                "user"  => new UserResource($user->makeHidden('password')),
                "token" => $token,
            ];

            return ApiResponse::success($data, "Successfully logged in.");
        }

        session(["api_token" => $token]);
        return redirect()->intended(route("dashboard"));
    }

    public function logout(Request $request)
    {
        if ($request->expectsJson()) {
            $user = Auth::guard("sanctum")->user();
            if ($user) {
                $user->tokens()->delete();
            }
            return ApiResponse::success(null, "Successfully logged out.");
        }

        $user = Auth::user();
        if ($user) {
            $user->tokens()->delete();
        }

        Auth::guard("web")->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route("login");
    }
}