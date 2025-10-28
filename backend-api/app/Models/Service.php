<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'description',
        'image',
        'price',
        'duration', 
        'status',
        'is_active',
        'service_center_id'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    public function serviceCenter()
    {
        return $this->belongsTo(ServiceCenter::class);
    }

    // Relationship with ServiceOrders
    public function serviceOrders()
    {
        return $this->hasMany(ServiceOrder::class);
    }
}