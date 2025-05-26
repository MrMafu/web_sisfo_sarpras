<?php

use App\Http\ApiResponse;
use App\Http\Controllers\API\{
    ReturningController,
};

use App\Http\Controllers\{
    AuthController,
    UserController,
    CategoryController,
    ItemController,
    ItemUnitController,
    BorrowingController,
};

use Illuminate\Support\Facades\Route;

// Public routes
Route::post("login", [AuthController::class, "login"]);
Route::post("logout", [AuthController::class, "logout"])->middleware("auth:sanctum");

// Admin routes
Route::prefix("admin")->middleware(["auth:sanctum", "admin-only"])->group(function () {
    Route::resource("users", UserController::class);
    Route::resource("categories", CategoryController::class);
    Route::resource("items", ItemController::class);
    Route::resource("item_units", ItemUnitController::class);

    // Change image item (postman)
    Route::post("items/{item}/change-image", [ItemController::class, "changeImage"]);
});

// Transactional data (can be accessed by both admins & users)
Route::middleware("auth:sanctum")->group(function () {
    Route::resource("borrowings", BorrowingController::class);
    Route::apiResources([
        "returnings" => ReturningController::class,
    ]);
});

// For when unauthenticated users do requests
Route::get("fallback", function () {
    return ApiResponse::error("Unauthorized.", 401);
})->name("login");