<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AboutPage extends Model
{
    use HasFactory;

    protected $table = 'about'; // টেবিল নাম স্পেসিফাই করুন

    protected $fillable = [
        'title',
        'subtitle', 
        'mission',
        'vision',
        'story',
        'values',
        'team',
        'stats',
        'image'
    ];

    protected $casts = [
        'values' => 'array',
        'team' => 'array',
        'stats' => 'array'
    ];
}