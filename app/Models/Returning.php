<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Returning extends Model
{
    use HasFactory;

    protected $fillable = [
        "borrowing_id",
        "returned_quantity",
        "status",
        "handled_by",
        "returned_at",
    ];
    
    public function borrowing()
    {
        return $this->belongsTo(Borrowing::class, "borrowing_id");
    }

    // Returning handler (Admin)
    public function handler()
    {
        return $this->belongsTo(User::class, "handled_by");
    }
}