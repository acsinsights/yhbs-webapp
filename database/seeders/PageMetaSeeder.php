<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PageMeta;

class PageMetaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pages = [
            [
                'route_name' => 'home',
                'page_name' => 'Home',
                'meta_title' => 'House & Room Booking System - Luxury Rentals & Reservations',
                'meta_description' => 'Book premium houses and comfortable rooms with our advanced booking system. Seamless reservations, secure payments, and exceptional service for your perfect getaway.',
                'meta_keywords' => 'house rental, room booking, luxury rentals, vacation homes, booking system, holiday homes, hotel rooms'
            ],
            [
                'route_name' => 'about',
                'page_name' => 'About',
                'meta_title' => 'About Us - Premium House & Room Booking Platform',
                'meta_description' => 'Learn about our mission to provide exceptional house and room rental experiences. Trusted platform for luxury accommodations.',
                'meta_keywords' => 'about us, rental company, house booking platform, luxury rentals, rental services'
            ],
            [
                'route_name' => 'contact',
                'page_name' => 'Contact',
                'meta_title' => 'Contact Us - House & Room Booking Support',
                'meta_description' => 'Get in touch with our team for house and room bookings, and customer support. We are here to help you plan your perfect vacation.',
                'meta_keywords' => 'contact us, customer support, booking inquiries, rental assistance, help center'
            ],
            [
                'route_name' => 'houses.index',
                'page_name' => 'Houses',
                'meta_title' => 'Premium House Rentals - Vacation Homes & Villas',
                'meta_description' => 'Discover stunning vacation homes and villas for rent. Book premium houses with modern amenities for your perfect holiday retreat.',
                'meta_keywords' => 'house rentals, vacation homes, villa rental, holiday houses, premium properties, vacation rentals'
            ],
            [
                'route_name' => 'rooms.index',
                'page_name' => 'Rooms',
                'meta_title' => 'Hotel Rooms - Comfortable Accommodations',
                'meta_description' => 'Browse our selection of comfortable hotel rooms. Book the perfect room for your stay with modern amenities and great service.',
                'meta_keywords' => 'hotel rooms, room booking, accommodation, hotel reservation'
            ],
            [
                'route_name' => 'blogs.index',
                'page_name' => 'Blog',
                'meta_title' => 'Travel Blog - Vacation Home & Room Tips & Guides',
                'meta_description' => 'Read expert tips and guides on vacation home rentals, travel destinations, and booking advice.',
                'meta_keywords' => 'travel blog, vacation guides, rental advice, travel destinations'
            ],
        ];

        foreach ($pages as $page) {
            PageMeta::updateOrCreate(
                ['route_name' => $page['route_name']],
                [
                    'page_name' => $page['page_name'],
                    'meta_title' => $page['meta_title'],
                    'meta_description' => $page['meta_description'],
                    'meta_keywords' => $page['meta_keywords'],
                ]
            );
        }
    }
}
