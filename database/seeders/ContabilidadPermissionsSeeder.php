<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;


class ContabilidadPermissionsSeeder extends Seeder
{

    private $permissions;

    public function __construct()
    {
        /*
        set the default permissions
        */
        $this->permissions =  [

           //GRUPO Contabilidad
		   ['name' => 'menu-contabilidad', 'description' => 'Menú Contabilidad'],
		   ['name' => 'pagoimportacion.lista', 'description' => 'Listado Pagos importaciones'],
		   ['name' => 'pagoimportacion-crear', 'description' => 'Agregar Pago Importación'],
		   ['name' => 'pagoimportacion.editar', 'description' => 'Editar Pago Importación'],
		   ['name' => 'pagoimportacion-eliminar', 'description' => 'Eliminar Pago Importación'],
		   ['name' => 'exportar-pagos', 'description' => 'Exportar Pago Importacion'],
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
