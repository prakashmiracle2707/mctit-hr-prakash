<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\IssueCategory;
use App\Models\IssueTitle;

class IssueDataSeeder extends Seeder
{
    public function run()
    {
        // Group categories by main_category
        $mainCategories = [
            'IT-TICKET' => [
                'Hardware' => [
                    'Mouse', 'Keyboard', 'Monitor', 'Laptop', 'Desktop', 'Printer', 'Scanner',
                    'Webcam', 'Headphone', 'CPU', 'Mobile Device', 'Cable', 'Mic not working', 'Speakers not working',
                ],
                'Software' => [
                    'Microsoft visual studio', 'System Update Failure', 'Teams not working',
                    'Software installation request',
                ],
                'Network' => [
                    'Internet not working', 'Wi-Fi not connecting', 'LAN port not working',
                    'Slow network speed', 'Frequent disconnection',
                ],
                'System Performance' => [
                    'System running slow', 'Overheating issue', 'System freezing',
                ],
                'Power & Battery' => [
                    'No power supply', 'Battery draining fast',
                ],
                'Other' => [
                    'Unlisted issue – needs manual check'
                ],
            ],

            'Complaint' => [
                'Office Maintenance' => [
                    'Chair repair', 'AC not working', 'Lights flickering', 'Fan not working',
                    'Electrical socket issue', 'Toilet flush not working', 'Toilet cleaning required',
                    'Foul smell in toilet', 'Water leakage in toilet', 'No tissue paper in toilet',
                    'Exhaust fan not working', 'Broken toilet door lock', 'Hand wash liquid empty',
                    'Water tap broken', 'Water cooler not cooling', 'Water cooler leaking',
                    'No drinking water in cooler', 'Water cooler needs cleaning',
                ],
                'Other' => [
                    'Unlisted issue – needs manual check'
                ],
            ],
        ];

        // Create categories with their titles
        foreach ($mainCategories as $mainCategory => $categories) {
            foreach ($categories as $categoryName => $titles) {
                $category = IssueCategory::create([
                    'name' => $categoryName,
                    'main_category' => $mainCategory
                ]);

                foreach ($titles as $title) {
                    IssueTitle::create([
                        'issue_category_id' => $category->id,
                        'name' => $title
                    ]);
                }
            }
        }
    }
}

