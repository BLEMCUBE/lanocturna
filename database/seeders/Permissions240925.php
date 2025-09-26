<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;


class Permissions240925 extends Seeder
{

    private $permissions;

    public function __construct()
    {
        /*
        set the default permissions
        */
        $this->permissions =  [

            //Productos
            ['name' => 'productos-editar_precio', 'description' => 'Editar precio del producto'],
            ['name' => 'ventas-editar_precio', 'description' => 'Editar precio del producto'],



        ];
    }

    public function run()
    {
        // Reset cached roles and permissions
        app()['cache']->forget('spatie.permission.cache');

  		//eliminando permisos
        foreach ($this->permissions as $permission) {
            Permission::where('name', $permission['name'])->delete();
        }

        // create permissions
        foreach ($this->permissions as $permission) {
            Permission::create($permission);
        }

        // agregando todos los permiso Super Administrador
        $sup = Role::where('name', 'Super Administrador')->first();

        foreach ($this->permissions as $value) {
            $sup->givePermissionTo($value['name']);
        }
    }
}
