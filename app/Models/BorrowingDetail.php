<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BorrowingDetail extends Model
{
    use HasFactory;

    protected $table = "borrowing_detail";

    protected $fillable = [
        "borrowing_id",
        "item_unit_id",
    ];

    public function borrowing()
    {
        return $this->belongsTo(Borrowing::class);
    }

    public function itemUnit()
    {
        return $this->belongsTo(itemUnit::class);
    }
}