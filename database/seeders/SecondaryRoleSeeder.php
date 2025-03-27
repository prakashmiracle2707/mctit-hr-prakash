<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SecondaryRole;

class SecondaryRoleSeeder extends Seeder
{
    public function run()
    {
        $roles = [
            'IT-Support-Engineer',
            'Complaint-Reviewer',
        ];

        foreach ($roles as $role) {
            SecondaryRole::firstOrCreate(['name' => $role]);
        }
    }
}

