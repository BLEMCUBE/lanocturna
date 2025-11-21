<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;


class MercadoLibrePermisos extends Seeder
{

    private $permissions;

    public function __construct()
    {
        /*
        set the default permissions
        */
        $this->permissions =  [

            ['name' => 'mercadoLibre-apis', 'description' => 'Listado de apis mercado libre'],
            ['name' => 'mercadoLibre-preguntas', 'description' => 'Listado de preguntas mercado libre'],
            ['name' => 'mercadoLibre-mensajes', 'description' => 'Listado de mensajes mercado libre'],
            ['name' => 'mercadoLibre-reclamos', 'description' => 'Listado de reclamos mercado libre'],




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
