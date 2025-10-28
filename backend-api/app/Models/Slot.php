<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Slot extends Model
{
    use HasFactory;

    protected $fillable = [
        'parking_id',
        'slot_code',
        'type',
        'available'
    ];

    // Parking relationship
    public function parking() {
        return $this->belongsTo(Parking::class);
    }

    // Bookings relationship
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
