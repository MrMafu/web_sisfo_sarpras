<?php

namespace App\Http\Middleware;

use App\Http\ApiResponse;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminOnly
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $currentUser = Auth::guard("sanctum")->user();
        $userRole = User::query()->find($currentUser->id)->role;

        if ($userRole !== "admin") {
            return ApiResponse::error("Forbidden.", 403);
        }

        if (!Auth::check()) {
            return redirect()->route("login");
        }

        if (Auth::user()->role !== "admin") {
            abort(403, "Forbidden: You are not an admin.");
        }

        return $next($request);
    }
}