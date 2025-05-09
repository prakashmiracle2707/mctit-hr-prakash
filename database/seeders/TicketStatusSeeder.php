<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TicketStatus;

class TicketStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            ['name' => 'TO DO', 'color' => 'lightgray', 'description' => 'Task is yet to be started.'],
            ['name' => 'BLOCKED', 'color' => 'red', 'description' => 'Task is blocked due to a dependency or issue.'],
            ['name' => 'IN PROGRESS', 'color' => 'blue', 'description' => 'Task is currently being worked on.'],
            ['name' => 'REVIEW', 'color' => 'orange', 'description' => 'Task is under review.'],
            ['name' => 'CLOSED', 'color' => 'green', 'description' => 'Task is completed and closed.'],
        ];

        foreach ($statuses as $status) {
            TicketStatus::updateOrCreate(['name' => $status['name']], $status);
        }
    }
}
