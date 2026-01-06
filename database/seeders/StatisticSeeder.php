<?php

namespace Database\Seeders;

use App\Models\Statistic;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class StatisticSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statistics = [
            [
                'title' => 'Tour Completed',
                'count' => '26K+',
                'icon' => 'tour-completed.png',
                'is_active' => true,
                'order' => 1,
            ],
            [
                'title' => 'Travel Experience',
                'count' => '12+',
                'icon' => 'travel-experience.png',
                'is_active' => true,
                'order' => 2,
            ],
            [
                'title' => 'Happy Traveler',
                'count' => '20+',
                'icon' => 'happy-traveler.png',
                'is_active' => true,
                'order' => 3,
            ],
            [
                'title' => 'Retention Rate',
                'count' => '98%',
                'icon' => 'retention-rate.png',
                'is_active' => true,
                'order' => 4,
            ],
        ];

        // Copy icons from public/default/statistics folder to storage/app/public/statistics
        foreach ($statistics as $key => $statistic) {
            $iconName = $statistic['icon'];
            $sourcePath = public_path('default/statistics/' . $iconName);

            if (file_exists($sourcePath)) {
                Storage::disk('public')->put(
                    'statistics/' . $iconName,
                    file_get_contents($sourcePath)
                );
            }

            Statistic::create([
                'title' => $statistic['title'],
                'count' => $statistic['count'],
                'icon' => 'statistics/' . $iconName,
                'is_active' => $statistic['is_active'],
                'order' => $statistic['order'],
            ]);
        }
    }
}
