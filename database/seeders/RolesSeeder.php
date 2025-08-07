<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
//use Spatie\Permission\Models\Permission;


class RolesSeeder extends Seeder
{

    private $permissions, $user_permissions;

    public function __construct()
    {
    }

    public function run()
    {
        // Reset cached roles and permissions
        app()['cache']->forget('spatie.permission.cache');

        // create the admin role and set all default permissions
        $sup = Role::create(['name' => 'Super Administrador']);

        $adm = Role::create(['name' => 'Administrador']);

        $enc = Role::create(['name' => 'Encargado']);
        $ven = Role::create(['name' => 'Vendedor']);
        $caj = Role::create(['name' => 'Caja']);
        $exp = Role::create(['name' => 'Expedicion']);
        $env = Role::create(['name' => 'Envio']);
        $dep = Role::create(['name' => 'Deposito']);
        $ser = Role::create(['name' => 'Servicio Tecnico']);

    }
}
