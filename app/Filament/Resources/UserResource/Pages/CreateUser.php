<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\Role;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function handleRecordCreation(array $data): User
    {
        $data['password'] = bcrypt($data['password']);

        $user = User::create($data);

        if (!empty($data['roles'])) {
            $role = Role::find($data['roles']);
            DB::table('model_has_roles')->insert([
                'role_id'    => $role->id,
                'model_id'   => $user->id,
                'model_type' => User::class,
            ]);
        }

        return $user;
    }
}
