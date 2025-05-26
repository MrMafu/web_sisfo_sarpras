<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        "category_id",
        "name",
        "stock",
        "image",
    ];

    public function toArray()
    {
        $array = parent::toArray();
        if ($this->relationLoaded("category")) {
            $array["category"] = $this->category->toArray();
        }

        return $array;
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function itemUnits()
    {
        return $this->hasMany(ItemUnit::class);
    }

    public function borrowings()
    {
        return $this->hasMany(Borrowing::class);
    }

    // Count the total of stock items (by item_units)
    public function recalculateStock(): void
    {
        $count = $this->itemUnits()->where("status", "available")->count();

        $this->stock = $count;
        $this->saveQuietly();
    }
}