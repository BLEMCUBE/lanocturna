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
use Illuminate\Support\Facades\Redirect;


class RoleController extends Controller
{
	public function __construct()
	{
		//protegiendo el controlador segun el rol
		$this->middleware(['auth', 'permission:ver-roles'])->only('index');
	}
	public function index()
	{
		$roles = Role::select('id','name')->whereNotIn('id', [1])
		->with(['users','permissions'])
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
		return Inertia::render('Role/Edit', [
			'role' => $roles,
			'rolePermissions' => $rolePermissions,
			'permissions' => $lista_permisos,
		]);
	}

	public function update(Request $request, $id)
	{
		$rol = Role::find($id);
		$rol->syncPermissions($request->input('permisos'));

		//return Redirect::route('roles.index');
	}

    public function destroy($id)
    {
        $cliente = Role::find($id);
        $cliente->delete();

    }
}
