<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Parking extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'image',
        'description',
        'total_slots',
        'available_slots',
        'price_per_hour',
        'distance'
    ];

    // Slots relationship
    public function slots() {
        return $this->hasMany(Slot::class);
    }

    // Bookings relationship
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
