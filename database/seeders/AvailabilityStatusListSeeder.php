<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AvailabilityStatusListSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        \App\Models\AvailabilityStatusList::insert([
            ['name' => 'Available'],
            ['name' => 'Not Available'],
            ['name' => 'Maternity Leave'],
            ['name' => 'Medical Leave'],
        ]);
    }
}
