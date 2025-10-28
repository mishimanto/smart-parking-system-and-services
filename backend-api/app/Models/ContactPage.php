<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactPage extends Model
{
    use HasFactory;

    protected $table = 'contact_us'; // টেবিল নাম স্পেসিফাই করুন

    protected $fillable = [
        'title',
        'subtitle',
        'address', 
        'phone',
        'email',
        'business_hours',
        'social_links',
        'map_embed',
        'form_title',
        'form_subtitle'
    ];

    protected $casts = [
        'business_hours' => 'array',
        'social_links' => 'array'
    ];
}