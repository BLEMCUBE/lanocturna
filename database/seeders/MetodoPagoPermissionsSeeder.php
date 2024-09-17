<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;


class MetodoPagoPermissionsSeeder extends Seeder
{

    private $permissions;

    public function __construct()
    {
        /*
        set the default permissions
        */
        $this->permissions =  [

           //GRUPO Método Pago
		   ['name' => 'lista-metodopago', 'description' => 'Listado Métodos de Pago'],
		   ['name' => 'crear-metodopago', 'description' => 'Agregar Método de Pago'],
		   ['name' => 'editar-metodopago', 'description' => 'Editar Método de Pago'],
		   ['name' => 'eliminar-metodopago', 'description' => 'Eliminar Método de Pago'],


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
