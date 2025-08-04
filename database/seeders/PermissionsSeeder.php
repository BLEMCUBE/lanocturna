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
            ['name' => 'productos-lista', 'description' => 'Lista Productos'],
            ['name' => 'productos-crear', 'description' => 'Crear Producto'],
            ['name' => 'productos-editar', 'description' => 'Editar Producto'],
            ['name' => 'productos-eliminar', 'description' => 'Eliminar Producto'],

            //Grupo configuraciones
            ['name' => 'menu-reportes', 'description' => 'Menú Reportes'],

            //graficos
            ['name' => 'reporte-grafico-ventas', 'description' => 'Gráfico Ventas'],

            //lista productos mas vendidos
            ['name' => 'reporte-productos-vendidos', 'description' => 'Listado productos más vendidos'],

            ['name' => 'reporte-productos-rma', 'description' => 'Listado productos RMA'],

            //Grupo configuraciones
            ['name' => 'menu-configuraciones', 'description' => 'Menú configuraciones'],
            //Usuarios
            ['name' => 'usuarios-lista', 'description' => 'Lista Usuarios'],
            ['name' => 'usuarios-crear', 'description' => 'Crear Usuario'],
            ['name' => 'usuarios-editar', 'description' => 'Editar Usuario'],
            ['name' => 'usuarios-eliminar', 'description' => 'Eliminar Usuario'],
            //Roles y permisos
            ['name' => 'configuraciones-roles', 'description' => 'Roles y permisos'],
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
            ['name' => 'compras-crear', 'description' => 'Crear Compra'],
            ['name' => 'compras-editar', 'description' => 'Editar Compra'],
            ['name' => 'compras-eliminar', 'description' => 'Anular Compra'],
            //Importaciones
            ['name' => 'importaciones-lista', 'description' => 'Lista Importaciones'],
            ['name' => 'importaciones-importarExcel', 'description' => 'Importar Excel'],
            ['name' => 'importaciones-detalle', 'description' => 'Detalle Importación'],
            ['name' => 'importaciones-editar', 'description' => 'Editar Importación'],
            ['name' => 'importaciones-eliminar', 'description' => 'Eliminar Importación'],
            //Rotación de Stock
            ['name' => 'rotacion-stock', 'description' => 'Rotación de Stock'],


            //GRUPO VENTAS
            ['name' => 'menu-ventas', 'description' => 'Menú Ventas'],
            //Ventas
            ['name' => 'ventas-lista', 'description' => 'Historial de Ventas'],
            ['name' => 'ventas-crear', 'description' => 'Crear Venta'],
            ['name' => 'ventas-facturar', 'description' => 'Facturar Venta'],
            ['name' => 'ventas-editar', 'description' => 'Editar Venta'],
            ['name' => 'ventas-eliminar', 'description' => 'Anular Venta'],
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
            ['name' => 'depositos-lista', 'description' => 'Depósitos'],
            ['name' => 'depositos-subir', 'description' => 'Subir Bultos Depósito'],
            ['name' => 'depositos-bultos', 'description' => 'Bultos Importados'],
            ['name' => 'depositos-historial', 'description' => 'Historial De Depósito'],
            ['name' => 'depositos-nombre', 'description' => 'Nombre Depósito'],


            //GRUPO RMA
            ['name' => 'menu-rma', 'description' => 'Menú Rma'],
            ['name' => 'rma-lista', 'description' => 'Listado Rma'],
            ['name' => 'rma-crear', 'description' => 'Crear Rma'],
            ['name' => 'rma-editar', 'description' => 'Editar Rma'],
            ['name' => 'rma-eliminar', 'description' => 'Eliminar Rma'],
            ['name' => 'rma-historial', 'description' => 'Historial Rma'],
            ['name' => 'rma-subir', 'description' => 'Subir Envio Rma'],
            ['name' => 'rma-historialEnvio', 'description' => 'Historial Envios RMA'],
            ['name' => 'rma-stock', 'description' => 'Stock RMA'],

            //lista vendedor - pedidos
            ['name' => 'reporte-vendedor-pedidos', 'description' => 'Reporte Vendedores con mas pedidos'],

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
