<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $password = bcrypt('qwerty12345');
        $adminRole = Role::where("name", 'Super Admin')->first()->id;
        $user = User::firstOrCreate(
            ['email' => 'superadmin@mail.com'],
            [
                'name' => 'Super Admin',
                'password' => $password,
            ]
        );

        DB::table('model_has_roles')->insert([
            'role_id'    => $adminRole,
            'model_id'   => $user->id,
            'model_type' => User::class,
        ]);
    }
}
