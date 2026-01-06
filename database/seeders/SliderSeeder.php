<?php

namespace Database\Seeders;

use App\Models\Slider;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

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
                'image' => 'slider1.jpg',
                'button_text' => 'Book Now',
                'button_link' => '/houses',
                'is_active' => true,
                'order' => 1,
            ],
            [
                'title' => 'Discover Island Living',
                'description' => 'Authentic island houses with modern amenities for an unforgettable vacation experience.',
                'image' => 'slider2.jpg',
                'button_text' => 'Explore Houses',
                'button_link' => '/houses',
                'is_active' => true,
                'order' => 2,
            ],
            [
                'title' => 'Luxury Meets Tradition',
                'description' => 'Stay in beautifully designed island houses that blend traditional charm with contemporary comfort.',
                'image' => 'slider3.jpg',
                'button_text' => 'View Collection',
                'button_link' => '/houses',
                'is_active' => true,
                'order' => 3,
            ],
        ];

        // Copy images from public/default/sliders folder to storage/app/public/sliders
        foreach ($sliders as $key => $slider) {
            $imageName = $slider['image'];
            $sourcePath = public_path('default/sliders/' . $imageName);

            if (file_exists($sourcePath)) {
                Storage::disk('public')->put(
                    'sliders/' . $imageName,
                    file_get_contents($sourcePath)
                );
            }

            Slider::create([
                'title' => $slider['title'],
                'description' => $slider['description'],
                'image' => 'sliders/' . $imageName,
                'button_text' => $slider['button_text'],
                'button_link' => $slider['button_link'],
                'is_active' => $slider['is_active'],
                'order' => $slider['order'],
            ]);
        }
    }
}
