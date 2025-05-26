<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        "slug",
        "name",
    ];

    public function items()
    {
        return $this->hasMany(Item::class);
    }

    // public function item_categories()
    // {
    //     return $this->hasMany(ItemCategory::class);
    // }

    // public function items()
    // {
    //     return $this->belongsToMany(
    //         Item::class,
    //         "item_categories",
    //         "category_id",
    //         "item_id"
    //     );
    // }
}