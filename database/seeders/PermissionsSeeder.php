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

            //Productos
            ['name' => 'lista-productos', 'description' => 'Lista Productos'],
            ['name' => 'crear-productos', 'description' => 'Crear Producto'],
            ['name' => 'editar-productos', 'description' => 'Editar Producto'],
            ['name' => 'eliminar-productos', 'description' => 'Eliminar Producto'],

            //Grupo configuraciones
            ['name' => 'menu-reportes', 'description' => 'Menú Reportes'],

            //graficos
            ['name' => 'reporte-grafico-ventas', 'description' => 'Gráfico Ventas'],

            //lista productos mas vendidos
            ['name' => 'reporte-productos-vendidos', 'description' => 'Listado productos más vendidos'],

            //Grupo configuraciones
            ['name' => 'menu-configuraciones', 'description' => 'Menú configuraciones'],
            //Usuarios
            ['name' => 'lista-usuarios', 'description' => 'Lista Usuarios'],
            ['name' => 'crear-usuarios', 'description' => 'Crear Usuario'],
            ['name' => 'editar-usuarios', 'description' => 'Editar Usuario'],
            ['name' => 'eliminar-usuarios', 'description' => 'Eliminar Usuario'],
            //Roles y permisos
            ['name' => 'ver-roles', 'description' => 'Roles y permisos'],
            //codigo
            ['name' => 'configuraciones', 'description' => 'Código Maestro'],
            //Tipo de cambio
            ['name' => 'lista-tipocambio', 'description' => 'Tipo de cambio'],
            //Tipo de cambio yuanes
            ['name' => 'lista-tipocambio-yuanes', 'description' => 'Tipo de cambio Yuanes'],



            //GRUPO COMPRAS
            ['name' => 'menu-compras', 'description' => 'Menú Compras'],
            //Compras
            ['name' => 'lista-compras', 'description' => 'Historial de Compras'],
            ['name' => 'crear-compras', 'description' => 'Crear Compra'],
            ['name' => 'editar-compras', 'description' => 'Editar Compra'],
            ['name' => 'eliminar-compras', 'description' => 'Anular Compra'],
            //Importaciones
            ['name' => 'lista-importaciones', 'description' => 'Lista Importaciones'],
            ['name' => 'excel-importaciones', 'description' => 'Importar Excel'],
            ['name' => 'detalle-importaciones', 'description' => 'Detalle Importación'],
            ['name' => 'editar-importaciones', 'description' => 'Editar Importación'],
            ['name' => 'eliminar-importaciones', 'description' => 'Eliminar Importación'],
            //Rotación de Stock
            ['name' => 'rotacion-stock', 'description' => 'Rotación de Stock'],


            //GRUPO VENTAS
            ['name' => 'menu-ventas', 'description' => 'Menú Ventas'],
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


            //GRUPO DEPOSITOS
            ['name' => 'menu-depositos', 'description' => 'Menú Depósitos'],
            //Deposito
            ['name' => 'lista-depositos', 'description' => 'Depósitos'],
            ['name' => 'subir-depositos', 'description' => 'Subir Bultos Depósito'],
            ['name' => 'bultos-depositos', 'description' => 'Bultos Importados'],
            ['name' => 'historial-depositos', 'description' => 'Historial De Depósito'],
            ['name' => 'nombre-depositos', 'description' => 'Nombre Depósito'],


            //GRUPO RMA
            ['name' => 'menu-rma', 'description' => 'RMA'],
            ['name' => 'lista-rma', 'description' => 'LISTADO RMA'],
            ['name' => 'historial-rma', 'description' => 'HISTORIAL RMA'],
            ['name' => 'subir-rma', 'description' => 'SUBIR ENVIO RMA'],


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
