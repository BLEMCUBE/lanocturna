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

        /*// create permissions
        foreach ($this->permissions as $permission) {
            Permission::create($permission);
        }*/

        // create the admin role and set all default permissions
        $sup = Role::create(['name' => 'Super Administrador']);

        /*foreach ($this->permissions as $value) {
            $sup->givePermissionTo($value['name']);
        }*/

        $adm = Role::create(['name' => 'Administrador']);
        /*foreach ($this->permissions as $key => $value) {
            $adm->givePermissionTo($this->permissions[$key]['name']);
        }*/
        $enc = Role::create(['name' => 'Encargado']);
        $ven = Role::create(['name' => 'Vendedor']);
        $caj = Role::create(['name' => 'Caja']);
        $exp = Role::create(['name' => 'Expedicion']);
        $env = Role::create(['name' => 'Envio']);
        $dep = Role::create(['name' => 'Deposito']);
        $ser = Role::create(['name' => 'Servicio Tecnico']);

    }
}
