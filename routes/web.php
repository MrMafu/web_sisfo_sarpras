<?php

use App\Http\Controllers\{
    AuthController,
    DashboardController,
    UserController,
    CategoryController,
    ItemController,
    ItemUnitController,
    BorrowingController,
    ReturningController,
};

use Illuminate\Support\Facades\Route;

// Home page
Route::get("/", [DashboardController::class, "index"])
    ->middleware(["auth", "admin-only"])
    ->name("dashboard");
    
// Log in
Route::middleware("guest")->group(function () {
    Route::get("login", [AuthController::class, "loginForm"])->name("login");
    Route::post("login", [AuthController::class, "login"])->name("login.perform");
});

// Log out
Route::post("logout", [AuthController::class, "logout"])
    ->middleware("auth")
    ->name("logout");

    Route::middleware("auth")->group(function() {
    Route::get("/borrowings/export", [BorrowingController::class, "exportExcel"])
        ->name("borrowings.export");
    Route::get("/returnings/export", [ReturningController::class, "exportExcel"])
        ->name("returnings.export");
    });

Route::middleware(["auth", "admin-only"])->group(function() {
    Route::resource("users", UserController::class);
    Route::resource("categories", CategoryController::class);
    Route::resource("items", ItemController::class)->except("changeImage");
    Route::resource("item_units", ItemUnitController::class)->parameters(["item_units" => "itemUnit"]);
    Route::resource("borrowings", BorrowingController::class);
    Route::resource("returnings", ReturningController::class);
});