<?php

namespace Database\Seeders;

use App\Models\ContactPage;
use Illuminate\Database\Seeder;

class ContactPageSeeder extends Seeder
{
    public function run()
    {
        ContactPage::create([
            'title' => 'Contact Us',
            'subtitle' => 'Get in Touch with Smart Parking Team',
            'address' => "123 Parking Tower, Gulshan Avenue\nDhaka 1212, Bangladesh",
            'phone' => '+880 1XXX-XXXXXX',
            'email' => 'info@smartparking.com',
            'business_hours' => json_encode([
                "Monday - Friday" => "9:00 AM - 6:00 PM",
                "Saturday" => "10:00 AM - 4:00 PM", 
                "Sunday" => "Closed"
            ]),
            'social_links' => json_encode([
                "facebook" => "https://facebook.com/smartparking",
                "twitter" => "https://twitter.com/smartparking",
                "linkedin" => "https://linkedin.com/company/smartparking",
                "instagram" => "https://instagram.com/smartparking"
            ]),
            'map_embed' => '<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3651.897918155182!2d90.404274375054!3d23.7508619880737!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3755b8bd552c2b3b%3A0x4e70f117856f0c22!2sGulshan%2C%20Dhaka!5e0!3m2!1sen!2sbd!4v1698765432100!5m2!1sen!2sbd" width="100%" height="300" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>',
            'form_title' => 'Send Us a Message',
            'form_subtitle' => 'Have questions about our services? We are here to help!'
        ]);
    }
}