<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemUnit extends Model
{
    use HasFactory;

    protected $fillable = [
        "item_id",
        "sku",
        "status",
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function borrowingDetail()
    {
        return $this->hasOne(BorrowingDetail::class);
    }

    // Stock trigger
    protected static function booted()
    {
        static::created(function (ItemUnit $unit) {
            $unit->item->recalculateStock();
        });

        static::deleted(function (ItemUnit $unit) {
            $unit->item->recalculateStock();
        });

        static::updated(function (ItemUnit $unit) {
            $unit->item->recalculateStock();
        });
    }
}
