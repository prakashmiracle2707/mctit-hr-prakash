<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class MilestoneSeeder extends Seeder
{
    public function run()
    {
        $milestones = [
            ['name' => 'Planning'],
            ['name' => 'Design'],
            ['name' => 'Development'],
            ['name' => 'Testing'],
            ['name' => 'Deployment'],
            ['name' => 'Maintenance'],
            ['name' => 'Bug Fixes'],
            ['name' => 'Documentation'],
            ['name' => 'Meeting'],
            ['name' => 'Support']
        ];

        DB::table('milestones')->insert($milestones);
    }
}
