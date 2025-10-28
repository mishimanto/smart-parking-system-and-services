<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'amount',
        'payment_method',
        'mobile_number',
        'pin',
        'generated_transaction_id',
        'verification_code', // নতুন যোগ করুন
        'approved_by',
        'approved_at',
        'verified_at', // নতুন যোগ করুন
        'transaction_id',
        'status',
        'description'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'verified_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Scope for pending transactions
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    // Scope for verified transactions (waiting for admin approval)
    public function scopeVerified($query)
    {
        return $query->where('status', 'verified');
    }

    // Scope for topup transactions
    public function scopeTopup($query)
    {
        return $query->where('type', 'topup');
    }
}