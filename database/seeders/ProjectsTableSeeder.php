<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class ProjectsTableSeeder extends Seeder
{
    public function run()
    {
        // ✅ Create permissions only if they don't exist
        $permissions = [
            "Manage Project",
            "Create Project",
            "Edit Project",
            "Delete Project",
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(
                ['name' => $perm, 'guard_name' => 'web'],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }

        // ✅ Ensure the 'company' role exists
        $companyRole = Role::firstOrCreate(
            ['name' => 'company'],
            ['created_by' => 0]
        );

        // ✅ Assign permissions to role AFTER ensuring they exist
        $companyPermissions = Permission::whereIn('name', $permissions)->pluck('name')->toArray();
        $companyRole->syncPermissions($companyPermissions);

        // ✅ Ensure the company user exists
        $company = User::firstOrCreate(
            ['email' => 'company@example.com'],
            [
                'name' => 'company',
                'password' => Hash::make('1234'),
                'type' => 'company',
                'lang' => 'en',
                'email_verified_at' => now(),
                'created_by' => 0,
            ]
        );

        $company->assignRole($companyRole);

        // ✅ Insert sample projects only if they don't exist
        DB::table('projects')->insertOrIgnore([
            ['name' => 'Acceinfo Bank', 'created_by' => $company->id, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'RealEstate', 'created_by' => $company->id, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
