<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $timestamps = [
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];

        Role::insertOrIgnore([
            array_merge($timestamps, [
                'name' => 'Super Admin',
                'id' => 1
            ]),
            array_merge($timestamps, [
                'name' => 'Operator',
                'id' => 2
            ]),
            array_merge($timestamps, [
                'name' => 'Project Manager',
                'id' => 3
            ]),
        ]);
    }
}
