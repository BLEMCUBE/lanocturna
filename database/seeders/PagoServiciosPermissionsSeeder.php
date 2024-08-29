<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;


class PagoServiciosPermissionsSeeder extends Seeder
{

    private $permissions;

    public function __construct()
    {
        /*
        set the default permissions
        */
        $this->permissions =  [

           //GRUPO Concepto Pago
		   ['name' => 'lista-conceptopago', 'description' => 'Listado Conceptos de Pago'],
		   ['name' => 'crear-conceptopago', 'description' => 'Agregar Concepto de Pago'],
		   ['name' => 'editar-conceptopago', 'description' => 'Editar Concepto de Pago'],
		   ['name' => 'eliminar-conceptopago', 'description' => 'Eliminar Concepto de Pago'],

           //GRUPO Pagos servicios
		   ['name' => 'lista-pagoservicio', 'description' => 'Listado Pagos'],
		   ['name' => 'crear-pagoservicio', 'description' => 'Agregar Pago'],
		   ['name' => 'editar-pagoservicio', 'description' => 'Editar Pago'],
		   ['name' => 'eliminar-pagoservicio', 'description' => 'Eliminar Pago'],
		   ['name' => 'exportar-pagoservicio', 'description' => 'Exportar Pagos'],
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
