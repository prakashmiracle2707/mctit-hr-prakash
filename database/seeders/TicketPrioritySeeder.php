<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TicketPriority;

class TicketPrioritySeeder extends Seeder
{
    public function run(): void
    {
        $priorities = [
            ['name' => 'Low', 'color' => 'green', 'description' => 'Low urgency. Can be handled later.'],
            ['name' => 'Medium', 'color' => 'blue', 'description' => 'Standard task. Should be completed soon.'],
            ['name' => 'High', 'color' => 'orange', 'description' => 'High importance. Needs attention.'],
            ['name' => 'Critical', 'color' => 'red', 'description' => 'Requires immediate resolution.'],
        ];

        foreach ($priorities as $priority) {
            TicketPriority::updateOrCreate(['name' => $priority['name']], $priority);
        }
    }
}
