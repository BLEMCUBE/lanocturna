<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;


class CatalogoPermissionsSeeder extends Seeder
{

    private $permissions;

    public function __construct()
    {
        /*
        set the default permissions
        */
        $this->permissions =  [

            //GRUPO Catalogo
			['name' => 'menu-catalogo', 'description' => 'Menú Catálogo'],
			['name' => 'catalogo-imagen', 'description' => 'Cambiar Imagen Catálogo'],
        ];
    }

    public function run()
    {
        // Reset cached roles and permissions
        app()['cache']->forget('spatie.permission.cache');

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
