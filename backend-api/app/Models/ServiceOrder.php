<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'service_id',
        'booking_time',
        'status',
        'notes',
        'slip_number',
        'invoice_number',
        'invoice_generated_at'
    ];

    protected $casts = [
        'booking_time' => 'datetime',
        'invoice_generated_at' => 'datetime'
    ];

    // Relationship with Service
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    // Relationship with User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function serviceCenter()
    {
        return $this->hasOneThrough(
            ServiceCenter::class,
            Service::class,
            'id', // Foreign key on services table
            'id', // Foreign key on service_centers table
            'service_id', // Local key on service_orders table
            'service_center_id' // Local key on services table
        );
    }
}