<?php

namespace Database\Seeders;

use App\Models\Slider;
use Illuminate\Database\Seeder;

class SliderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sliders = [
            [
                'title' => 'Your Island House, Your Comfort.',
                'description' => 'Experience peaceful stays in traditional and modern island houses designed for families and groups.',
                'image' => 'default/sliders/slider1.jpg',
                'button_text' => 'Book Now',
                'button_link' => '/houses',
                'is_active' => true,
                'order' => 1,
            ],
            [
                'title' => 'Discover Island Living',
                'description' => 'Authentic island houses with modern amenities for an unforgettable vacation experience.',
                'image' => 'default/sliders/slider2.jpg',
                'button_text' => 'Explore Houses',
                'button_link' => '/houses',
                'is_active' => true,
                'order' => 2,
            ],
            [
                'title' => 'Luxury Meets Tradition',
                'description' => 'Stay in beautifully designed island houses that blend traditional charm with contemporary comfort.',
                'image' => 'default/sliders/slider3.jpg',
                'button_text' => 'View Collection',
                'button_link' => '/houses',
                'is_active' => true,
                'order' => 3,
            ],
        ];

        foreach ($sliders as $slider) {
            Slider::create($slider);
        }
    }
}
