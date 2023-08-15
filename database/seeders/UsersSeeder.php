<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = new User;
        $user->name     = 'Super Administrador';
        $user->username     = 'admin';
        $user->email    = '';
        $user->photo    = '/images/usuarios/user.png';
        $user->password = Hash::make('Adm1N202E@');
        $user->save();

        $user->assignRole('Super Administrador');


        $user = new User;
        $user->name     = 'Usuario';
        $user->username     = 'usuario';
        $user->email    = '';
        $user->photo    = '/images/usuarios/user.png';
        $user->password = Hash::make('123456789');
        $user->save();
        $user->assignRole('Administrador');
    }
}
