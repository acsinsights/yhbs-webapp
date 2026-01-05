<?php

namespace Database\Seeders;

use App\Models\Testimonial;
use Illuminate\Database\Seeder;

class TestimonialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $testimonials = [
            [
                'customer_name' => 'Ahmed Hassan',
                'customer_designation' => 'Travel Enthusiast',
                'customer_image' => 'default/testimonials/user.jpg',
                'testimonial' => 'Amazing experience! The island house was exactly what we needed for our family vacation. Clean, comfortable, and the location was perfect. Highly recommend!',
                'rating' => 5,
                'is_active' => true,
                'order' => 1,
            ],
            [
                'customer_name' => 'Fatima Ali',
                'customer_designation' => 'Frequent Traveler',
                'customer_image' => 'default/testimonials/user.jpg',
                'testimonial' => 'We had a wonderful stay. The house was well-maintained and the booking process was smooth. The staff was very helpful throughout our trip.',
                'rating' => 5,
                'is_active' => true,
                'order' => 2,
            ],
            [
                'customer_name' => 'Mohamed Ibrahim',
                'customer_designation' => 'Vacation Planner',
                'customer_image' => 'default/testimonials/user.jpg',
                'testimonial' => 'Perfect getaway for our group! The island house had all the amenities we needed. Beautiful views and peaceful environment. Will definitely book again!',
                'rating' => 5,
                'is_active' => true,
                'order' => 3,
            ],
            [
                'customer_name' => 'Aisha Mohamed',
                'customer_designation' => 'Family Traveler',
                'customer_image' => 'default/testimonials/user.jpg',
                'testimonial' => 'Outstanding service and beautiful property. The traditional design mixed with modern comforts made our stay memorable. Great value for money!',
                'rating' => 5,
                'is_active' => true,
                'order' => 4,
            ],
            [
                'customer_name' => 'Omar Abdullah',
                'customer_designation' => 'Adventure Seeker',
                'customer_image' => 'default/testimonials/user.jpg',
                'testimonial' => 'Loved every moment of our stay! The island house exceeded our expectations. Perfect location for exploring nearby islands and relaxing by the beach.',
                'rating' => 5,
                'is_active' => true,
                'order' => 5,
            ],
            [
                'customer_name' => 'Mariam Rasheed',
                'customer_designation' => 'Holiday Maker',
                'customer_image' => 'default/testimonials/user.jpg',
                'testimonial' => 'Fantastic experience from start to finish. The house was spacious, clean, and had everything we needed. Customer service was excellent!',
                'rating' => 5,
                'is_active' => true,
                'order' => 6,
            ],
        ];

        foreach ($testimonials as $testimonial) {
            Testimonial::create($testimonial);
        }
    }
}
