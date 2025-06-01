<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Borrowing extends Model
{
    use HasFactory;

    protected $fillable = [
        "item_id",
        "user_id",
        "quantity",
        "status",
        "due",
        "approved_at",
        "approved_by",
    ];

    const statusPending = "pending";
    const statusApproved = "approved";
    const statusRejected = "rejected";
    const statusOverdue = "overdue";
    const statusReturned = "returned";

    // Borrower
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    // Approver (Admin)
    public function approver()
    {
        return $this->belongsTo(User::class, "approved_by");
    }
    
    public function borrowingDetails()
    {
        return $this->hasMany(BorrowingDetail::class);
    }

    public function returning()
    {
        return $this->hasOne(Returning::class, "borrowing_id");
    }
}