<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;


class RolesAndPermissionsSeeder extends Seeder
{

    private $permissions, $user_permissions;

    public function __construct()
    {
        /*
        set the default permissions
        */
        $this->permissions =  [

            ['name' => 'importaciones', 'description' => 'Importaciones'],

            //Usuarios
            ['name' => 'lista-usuarios', 'description' => 'Lista Usuarios'],
            ['name' => 'crear-usuarios', 'description' => 'Crear Usuario'],
            ['name' => 'editar-usuarios', 'description' => 'Editar Usuario'],
            ['name' => 'eliminar-usuarios', 'description' => 'Eliminar Usuario'],

            //Clientes
            ['name' => 'lista-clientes', 'description' => 'Lista Clientes'],
            ['name' => 'crear-clientes', 'description' => 'Crear Cliente'],
            ['name' => 'editar-clientes', 'description' => 'Editar Cliente'],
            ['name' => 'eliminar-clientes', 'description' => 'Eliminar Cliente'],

            //Productos
            ['name' => 'lista-productos', 'description' => 'Lista Productos'],
            ['name' => 'crear-productos', 'description' => 'Crear Producto'],
            ['name' => 'detalle-productos', 'description' => 'Detalle Producto'],
            ['name' => 'editar-productos', 'description' => 'Editar Producto'],
            ['name' => 'eliminar-productos', 'description' => 'Eliminar Producto'],

            //Importaciones
            ['name' => 'lista-importaciones', 'description' => 'Lista Importaciones'],
            //['name' => 'crear-importaciones', 'description' => 'Crear Importación'],
            ['name' => 'excel-importaciones', 'description' => 'Importar Excel'],
            ['name' => 'detalle-importaciones', 'description' => 'Detalle Importación'],
            ['name' => 'editar-importaciones', 'description' => 'Editar Importación'],
            ['name' => 'eliminar-importaciones', 'description' => 'Eliminar Importación'],

            //Tipo de cambio
            ['name' => 'lista-tipocambio', 'description' => 'Tipo de cambio'],


            //Ventas
            ['name' => 'lista-ventas', 'description' => 'Historial de Ventas'],
            ['name' => 'crear-ventas', 'description' => 'Crear Venta'],
            ['name' => 'facturar-ventas', 'description' => 'Facturar Venta'],
            ['name' => 'editar-ventas', 'description' => 'Editar Venta'],
            ['name' => 'eliminar-ventas', 'description' => 'Anular Venta'],

            //Caja
            ['name' => 'lista-cajas', 'description' => 'Caja'],

            //Expedición
            ['name' => 'lista-expediciones', 'description' => 'Expedición'],

            //Envios
            ['name' => 'lista-envios', 'description' => 'Envios'],
            ['name' => 'historial-envios', 'description' => 'Historial de Envios'],
            ['name' => 'subir-envios', 'description' => 'Mercado Libre'],

            //Deposito
            ['name' => 'lista-depositos', 'description' => 'Depósitos'],
            ['name' => 'crear-depositos', 'description' => 'Crear Depósito'],
            ['name' => 'editar-depositos', 'description' => 'Editar Depósito'],
            ['name' => 'nombre-depositos', 'description' => 'Nombre Depósitos'],
            ['name' => 'mover-depositos', 'description' => 'Mover Bultos'],

            //configuraciones
            ['name' => 'configuraciones', 'description' => 'Código Maestro'],

            //Roles y permisos
            ['name' => 'ver-roles', 'description' => 'Roles y permisos'],

             //graficos
             ['name' => 'grafico-ventas', 'description' => 'Gráfico Ventas'],



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

        // create the admin role and set all default permissions
        $sup = Role::create(['name' => 'Super Administrador']);

        foreach ($this->permissions as $value) {
            $sup->givePermissionTo($value['name']);
        }

        $adm = Role::create(['name' => 'Administrador']);
        foreach ($this->permissions as $key => $value) {
            $adm->givePermissionTo($this->permissions[$key]['name']);
        }
        $enc = Role::create(['name' => 'Encargado']);
        $ven = Role::create(['name' => 'Vendedor']);
        $caj = Role::create(['name' => 'Caja']);
        $exp = Role::create(['name' => 'Expedicion']);
        $env = Role::create(['name' => 'Envio']);
        $dep = Role::create(['name' => 'Deposito']);
        $ser = Role::create(['name' => 'Servicio Tecnico']);
        //$rep->givePermissionTo($this->permissions[0]['name']);
    }
}
