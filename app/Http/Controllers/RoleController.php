<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoleStoreRequest;
use App\Http\Requests\RoleUpdateRequest;
use App\Http\Resources\RoleCollection;
use Spatie\Permission\Models\Role;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RoleController extends Controller
{
	public function __construct()
	{
		//protegiendo el controlador segun el rol
		//$this->middleware('permission:configuraciones-roles')->only('index');
	}
	public function index()
	{
		$roles = Role::select('id', 'name')->whereNotIn('id', [1])
			->with(['users', 'permissions'])
			->orderBy('name')->get();
		return Inertia::render('Role/Index', [
			'roles' =>  new RoleCollection(
				$roles
			)
		]);
	}
	public function store(RoleStoreRequest $request)
	{

		Role::create(['name' => $request->name]);
	}

	public function show($id)
	{
		$cliente = Role::findOrFail($id);
		return response()->json([
			"rol" => $cliente
		]);
	}
	public function updateRol(RoleUpdateRequest $request, $id)
	{
		$tipo_cambio = Role::findOrFail($id);
		$tipo_cambio->update($request->all());
	}

	public function edit($id)
	{
		if ($id == 1) {
			return redirect('roles');
		}
		$groupNames = [
			'menu' => 'Menú',
			'productos' => 'Productos',
			'pagoservicio' => 'Pago Servicio',
			'pagoimportacion' => 'Pago Importacion',
			'conceptopago' => 'Concepto Pago',
		];
		$permissions = Permission::all();
		$roles = Role::find($id);

		$rolePermissions = DB::table("role_has_permissions")->where("role_has_permissions.role_id", $id)
			->pluck('role_has_permissions.permission_id')
			->all();
		$lista_permisos = [];
		foreach ($permissions as $key => $permiso) {
			array_push($lista_permisos, [
				"id" => $permiso->id,
				"name" => $permiso->description,
			]);
		}


		// Mapear permisos con display_name
		$permissions2 = Permission::all()->map(function ($perm) {
			$parts = explode('-', $perm->name);
			$action = $parts[1] ?? '';
			$target = $parts[0] ?? '';

			$actionsMap = [
				'create' => 'Crear',
				'edit'   => 'Editar',
				'delete' => 'Eliminar',
				'view'   => 'Ver',
				'list'   => 'Listar',
			];

			$displayAction = $actionsMap[$action] ?? Str::ucfirst($action);
			$displayTarget = Str::ucfirst(str_replace('_', ' ', $target));

			return [
				'id' => $perm->id,
				'original_name' => $perm->name,
				'description' => $perm->description,
				//'display_name' => "$displayAction $displayTarget",
				'group' => $target,
			];
		});

		// Agrupar por grupo
		$grouped = $permissions2->groupBy('group');

		// Construir grupos con nombre legible y permisos ordenados
		$result = collect($grouped)->map(function ($items, $key) use ($groupNames) {
			return [
				'key' => $key,
				'name' => $groupNames[$key] ?? ucfirst($key),
				'permissions' => $items->sortBy('description')->values()->map(fn($p) => [
					'id' => $p['id'],
					//'name' => $p['display_name'], // ya puedes usarlo directamente en Vue
					'description' => $p['description'], // ya puedes usarlo directamente en Vue
				]),
			];
		});

		// Ordenar los grupos por nombre legible
		$sorted = $result->sortBy('name')->values();

		return Inertia::render('Role/Edit', [
			'role' => $roles,
			'rolePermissions' => $rolePermissions,
			'permissions' => $lista_permisos,
			'namedGroups' => $sorted,
		]);
	}

	public function update(Request $request, $id)
	{
		$rol = Role::find($id);
		$rol->syncPermissions($request->input('permisos'));
	}

	public function destroy($id)
	{
		$cliente = Role::find($id);
		$cliente->delete();
	}
}
