<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;


class PermissionsSeeder extends Seeder
{

    private $permissions;

    public function __construct()
    {
        /*
        set the default permissions
        */
        $this->permissions =  [

            //Usuarios
            ['name' => 'lista-usuarios', 'description' => 'Lista Usuarios'],
            ['name' => 'crear-usuarios', 'description' => 'Crear Usuario'],
            ['name' => 'editar-usuarios', 'description' => 'Editar Usuario'],
            ['name' => 'eliminar-usuarios', 'description' => 'Eliminar Usuario'],

            //Clientes
            /*['name' => 'lista-clientes', 'description' => 'Lista Clientes'],
            ['name' => 'crear-clientes', 'description' => 'Crear Cliente'],
            ['name' => 'editar-clientes', 'description' => 'Editar Cliente'],
            ['name' => 'eliminar-clientes', 'description' => 'Eliminar Cliente'],*/

            //Productos
            ['name' => 'lista-productos', 'description' => 'Lista Productos'],
            ['name' => 'crear-productos', 'description' => 'Crear Producto'],
            ['name' => 'editar-productos', 'description' => 'Editar Producto'],
            ['name' => 'eliminar-productos', 'description' => 'Eliminar Producto'],

            //Importaciones
            ['name' => 'lista-importaciones', 'description' => 'Lista Importaciones'],
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

            //Compras
            ['name' => 'lista-compras', 'description' => 'Historial de Compras'],
            ['name' => 'crear-compras', 'description' => 'Crear Compra'],
            ['name' => 'editar-compras', 'description' => 'Editar Compra'],
            ['name' => 'eliminar-compras', 'description' => 'Anular Compra'],

            //Caja
            ['name' => 'lista-cajas', 'description' => 'Caja'],

            //Expedición
            ['name' => 'lista-expediciones', 'description' => 'Expedición'],

            //Envios
            ['name' => 'lista-envios', 'description' => 'Envios'],
            ['name' => 'historial-envios', 'description' => 'Historial de Envios'],
            ['name' => 'subir-envios', 'description' => 'Mercado Libre'],

            //configuraciones
            ['name' => 'configuraciones', 'description' => 'Código Maestro'],

            //Roles y permisos
            ['name' => 'ver-roles', 'description' => 'Roles y permisos'],

            //graficos
            ['name' => 'grafico-ventas', 'description' => 'Gráfico Ventas'],

            //Deposito
            ['name' => 'lista-depositos', 'description' => 'Depósitos'],
            ['name' => 'subir-depositos', 'description' => 'Subir Bultos Depósito'],
            ['name' => 'bultos-depositos', 'description' => 'Bultos Importados'],
            ['name' => 'historial-depositos', 'description' => 'Historial De Depósito'],
            ['name' => 'nombre-depositos', 'description' => 'Nombre Depósito'],

            //lista productos mas vendidos
            ['name' => 'productos-vendidos', 'description' => 'Listado productos más vendidos'],

            //Tipo de cambio yuanes
            ['name' => 'lista-tipocambio-yuanes', 'description' => 'Tipo de cambio Yuanes'],

            //Menu
            ['name' => 'menu-configuraciones', 'description' => 'Menú configuraciones'],
            ['name' => 'menu-compras', 'description' => 'Menú Compras'],
            ['name' => 'menu-ventas', 'description' => 'Menú Ventas'],
            ['name' => 'menu-depositos', 'description' => 'Menú Depósitos'],
            ['name' => 'menu-servicios', 'description' => 'Menú Servicio Técnico'],

            //Servicio Tecnico
            ['name' => 'lista-servicios', 'description' => 'Servicio Técnico'],







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
           $sup = Role::where('name' , 'Super Administrador')->first();

           foreach ($this->permissions as $value) {
               $sup->givePermissionTo($value['name']);
           }
    }
}
