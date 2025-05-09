<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TicketType;

class TicketTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'name' => 'Bug',
                'color' => 'red',
                'description' => 'A system defect or error that needs fixing.',
                'image' => 'uploads/ticket/ticket-type/Bug.svg', // e.g., 'ticket_types/bug.png' if using uploaded image
                'logo_name' => 'fas fa-bug',
            ],
            [
                'name' => 'Story',
                'color' => 'blue',
                'description' => 'A new feature or user requirement.',
                'image' => 'uploads/ticket/ticket-type/Story.svg', // e.g., 'ticket_types/story.png'
                'logo_name' => 'fas fa-book-open',
            ],
        ];

        foreach ($types as $type) {
            TicketType::updateOrCreate(['name' => $type['name']], $type);
        }
    }
}
