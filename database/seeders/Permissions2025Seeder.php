<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;


class Permissions2025Seeder extends Seeder
{

	private $permissions;

	public function __construct()
	{
		/*
        set the default permissions
        */
		$this->permissions =  [

			//menu
			['name' => 'menu-catalogo', 'description' => 'Catálogo'],
			['name' => 'menu-productos', 'description' => 'Productos'],
			['name' => 'menu-contabilidad', 'description' => 'Contabilidad'],
			['name' => 'menu-reportes', 'description' => 'Reportes'],
			['name' => 'menu-configuraciones', 'description' => 'Configuraciones'],
			['name' => 'menu-compras', 'description' => 'Compras'],
			['name' => 'menu-ventas', 'description' => 'Ventas'],
			['name' => 'menu-deposito', 'description' => 'Depósitos'],
			['name' => 'menu-rma', 'description' => 'Rma'],

			['name' => 'catalogo-imagen', 'description' => 'Cambiar Imagen Catálogo'],

			//Productos
			['name' => 'productos-lista', 'description' => 'Listado'],
			['name' => 'productos-crear', 'description' => 'Crear'],
			['name' => 'productos-editar', 'description' => 'Editar'],
			['name' => 'productos-eliminar', 'description' => 'Eliminar'],
			['name' => 'productos-descargarExcel', 'description' => 'Descargar Excel'],
			['name' => 'productos-subirMasivo', 'description' => 'Subir Productos'],
			['name' => 'productos-costoreal', 'description' => 'Agregar/Editar costo real'],

			//Contabilidad
			['name' => 'contabilidad-pagoImportacion', 'description' => 'Pagos importaciones'],
			['name' => 'contabilidad-pagoEnPlaza', 'description' => 'Pagos Compras en plaza'],
			['name' => 'contabilidad-pagoServicio', 'description' => 'Pagos Servicios'],
			['name' => 'contabilidad-pagoConcepto', 'description' => 'Conceptos de Pago'],
			['name' => 'contabilidad-pagoMetodo', 'description' => 'Métodos de Pago'],

			//Reportes
			['name' => 'reportes-graficoVenta', 'description' => 'Gráfico Ventas'],
			['name' => 'reportes-stockFecha', 'description' => 'Stock por fecha'],
			['name' => 'reportes-productosVendidos', 'description' => 'Productos vendidos'],
			['name' => 'reportes-productoRma', 'description' => 'Producto RMA'],
			['name' => 'reportes-vendedoresPedidos', 'description' => 'vendedores con pedidos'],

			//Configuraciones
			['name' => 'configuraciones-usuarios', 'description' => 'Usuarios'],
			['name' => 'configuraciones-roles', 'description' => 'Roles y permisos'],
			['name' => 'configuraciones-tipoCambio', 'description' => 'Tipo de cambio'],
			['name' => 'configuraciones-tipoCambioYuanes', 'description' => 'Tipo de cambio Yuanes'],
			['name' => 'configuraciones-ajusteStock', 'description' => 'Ajuste de Stock'],
			['name' => 'configuraciones-datosWeb', 'description' => 'Datos Web'],
			['name' => 'configuraciones-categorias', 'description' => 'Categorias'],
			['name' => 'configuraciones-codigoMaestro', 'description' => 'Código Maestro'],

			//Usuarios
			['name' => 'usuarios-lista', 'description' => 'Listado'],
			['name' => 'usuarios-crear', 'description' => 'Crear'],
			['name' => 'usuarios-editar', 'description' => 'Editar'],
			['name' => 'usuarios-eliminar', 'description' => 'Eliminar'],

			//Compras
			['name' => 'compras-lista', 'description' => 'Compra en Plaza'],
			['name' => 'compras-crear', 'description' => 'Crear'],
			['name' => 'compras-editar', 'description' => 'Editar'],
			['name' => 'compras-eliminar', 'description' => 'Anular'],
			['name' => 'compras-historial', 'description' => 'Historial de Compras'],
			['name' => 'compras-importaciones', 'description' => 'Importaciones'],
			['name' => 'compras-rotacionStock', 'description' => 'Rotación de Stock'],

			//Importaciones
			['name' => 'importaciones-lista', 'description' => 'Listado'],
			['name' => 'importaciones-importarExcel', 'description' => 'Importar Excel'],
			['name' => 'importaciones-editar', 'description' => 'Editar'],
			['name' => 'importaciones-detalle', 'description' => 'Detalle'],
			['name' => 'importaciones-eliminar', 'description' => 'Eliminar'],

			//Ventas
			['name' => 'ventas-crear', 'description' => 'Crear'],
			['name' => 'ventas-editar', 'description' => 'Editar'],
			['name' => 'ventas-facturar', 'description' => 'Facturar'],
			['name' => 'ventas-eliminar', 'description' => 'Anular'],

			['name' => 'ventas-caja', 'description' => 'Caja'],
			['name' => 'ventas-mercadoLibre', 'description' => 'Mecado Libre'],
			['name' => 'ventas-expediciones', 'description' => 'Expedición'],
			['name' => 'ventas-ues', 'description' => 'Envios UES'],
			['name' => 'ventas-flex', 'description' => 'Envios Flex'],
			['name' => 'ventas-uesweb', 'description' => 'Envios UES WEB'],
			['name' => 'ventas-cadeteria', 'description' => 'Envios Cadeteria'],
			['name' => 'ventas-flash', 'description' => 'Envios Flash'],
			['name' => 'ventas-retiros', 'description' => 'Retiros WEB'],
			['name' => 'ventas-historial', 'description' => 'Historial de Envios'],
			['name' => 'ventas-lista', 'description' => 'Historial de Ventas'],



			// Conceptos de Pago
			['name' => 'conceptopago-lista', 'description' => 'Listado'],
			['name' => 'conceptopago-crear', 'description' => 'Crear'],
			['name' => 'conceptopago-editar', 'description' => 'Editar'],
			['name' => 'conceptopago-eliminar', 'description' => 'Eliminar'],

			// Pagos Servicios
			['name' => 'pagoservicio-lista', 'description' => 'Listado'],
			['name' => 'pagoservicio-crear', 'description' => 'Crear'],
			['name' => 'pagoservicio-editar', 'description' => 'Editar'],
			['name' => 'pagoservicio-eliminar', 'description' => 'Eliminar'],
			['name' => 'pagoservicio-exportar', 'description' => 'Exportar Pagos'],

			//Pago Importacion
			['name' => 'pagoimportacion-crear', 'description' => 'Crear'],
			['name' => 'pagoimportacion-eliminar', 'description' => 'Eliminar'],
			['name' => 'pagoimportacion-exportar', 'description' => 'Exportar Pagos'],

			//Depositos
			['name' => 'depositos-lista', 'description' => 'Depósitos'],
			['name' => 'depositos-subir', 'description' => 'Subir Bultos'],
			['name' => 'depositos-bultos', 'description' => 'Bultos Importados'],
			['name' => 'depositos-historial', 'description' => 'Historial De Depósito'],
			['name' => 'depositos-nombre', 'description' => 'Nombre Depósito'],
			['name' => 'depositos-exportar', 'description' => 'Descargar Excel'],


			//RMA
			['name' => 'rma-lista', 'description' => 'Listado'],
			['name' => 'rma-crear', 'description' => 'Crear'],
			['name' => 'rma-editar', 'description' => 'Editar'],
			['name' => 'rma-eliminar', 'description' => 'Eliminar'],
			['name' => 'rma-historial', 'description' => 'Historial'],
			['name' => 'rma-subir', 'description' => 'Subir Envio'],
			['name' => 'rma-historialEnvio', 'description' => 'Historial Envios'],
			['name' => 'rma-stock', 'description' => 'Stock'],
			['name' => 'rma-validacion', 'description' => 'Validación'],

			// Tipo de Cambio
			['name' => 'tipocambio-lista', 'description' => 'Listado'],
			['name' => 'tipocambio-crear', 'description' => 'Crear'],
			['name' => 'tipocambio-editar', 'description' => 'Editar'],
			['name' => 'tipocambio-eliminar', 'description' => 'Eliminar'],

			// Tipo de Cambio yuang
			['name' => 'tipocambioyuan-lista', 'description' => 'Listado'],
			['name' => 'tipocambioyuan-crear', 'description' => 'Crear'],
			['name' => 'tipocambioyuan-editar', 'description' => 'Editar'],
			['name' => 'tipocambioyuan-eliminar', 'description' => 'Eliminar'],


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
