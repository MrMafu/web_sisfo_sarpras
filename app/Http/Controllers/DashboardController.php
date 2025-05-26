<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Category;
use App\Models\Item;
use App\Models\ItemUnit;
use App\Models\Borrowing;
use App\Models\Returning;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        return view("dashboard", [
            "totalUsers"        => User::count(),
            "totalCategories"   => Category::count(),
            "totalItems"        => Item::count(),
            "totalUnits"        => ItemUnit::count(),
            "pendingBorrowings" => Borrowing::where("status","pending")->count(),
            "pendingReturnings" => Returning::where("status","pending")->count(),
        ]);
    }
}