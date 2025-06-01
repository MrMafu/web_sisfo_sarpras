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

    const statusAvailable = "available";
    const statusBorrowed = "borrowed";
    const statusOverdue = "overdue";
    const statusLost = "lost";

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
            if ($unit->isDirty("status")) {
                $unit->item->recalculateStock();
            }
        });
    }
}