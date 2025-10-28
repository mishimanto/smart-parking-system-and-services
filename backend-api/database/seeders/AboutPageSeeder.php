<?php

namespace Database\Seeders;

use App\Models\AboutPage;
use Illuminate\Database\Seeder;

class AboutPageSeeder extends Seeder
{
    public function run()
    {
        AboutPage::create([
            'title' => 'About Smart Parking',
            'subtitle' => 'Revolutionizing Urban Mobility in Bangladesh',
            'mission' => 'Our mission is to transform the parking experience in urban Bangladesh by providing smart, efficient, and accessible parking solutions through cutting-edge technology.',
            'vision' => 'We envision a future where finding parking is no longer a daily struggle, but a seamless experience that enhances urban mobility and reduces traffic congestion.',
            'story' => 'Founded in 2023, Smart Parking System emerged from the growing need for efficient parking solutions in Bangladesh\'s rapidly urbanizing cities. Our team of tech enthusiasts and urban planners came together to solve one of the most persistent problems faced by urban commuters.',
            'values' => json_encode(['Innovation', 'Reliability', 'Customer Focus', 'Sustainability', 'Excellence']),
            'team' => json_encode([
                [
                    'name' => 'Rahim Ahmed',
                    'position' => 'CEO & Founder',
                    'image' => '/images/team/ceo.jpg',
                    'bio' => 'Tech entrepreneur with 10+ years in software development'
                ],
                [
                    'name' => 'Fatima Begum', 
                    'position' => 'CTO',
                    'image' => '/images/team/cto.jpg',
                    'bio' => 'Former software engineer at leading tech companies'
                ]
            ]),
            'stats' => json_encode([
                'parking_locations' => 500,
                'happy_customers' => 50000, 
                'cities_covered' => 15,
                'booking_success_rate' => 99.8
            ]),
            'image' => '/images/about-hero.jpg'
        ]);
    }
}