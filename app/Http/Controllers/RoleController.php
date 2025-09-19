<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Http\Resources\RoleResource;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller implements HasMiddleware
{

    public static function middleware()
    {
        return [
            new Middleware('permission:view-roles', only: ['index', 'show']),
            new Middleware('permission:create-roles', only: ['create', 'store']),
            new Middleware('permission:edit-roles', only: ['edit', 'update']),
            new Middleware('permission:delete-roles', only: ['destroy']),
            new Middleware('permission:view-permissions', only: ['permissions']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);

        if ($request->has('search')) {
            $roles = Role::query()
                ->where('name', '!=', 'super-admin')
                ->where('name', 'like', '%' . $request->search . '%')
                ->orderBy('name', 'asc')
                ->paginate($perPage)
                ->withQueryString();
        } else {
            $roles = Role::query()
                ->where('name', '!=', 'super-admin')
                ->orderBy('name', 'asc')
                ->paginate($perPage)
                ->withQueryString();
        }

        return Inertia::render('roles/Index', [
            'roles' => RoleResource::collection($roles),
            'searchTerm' => $request->only('search'),
            'perPage' => $perPage,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $permissions = Permission::all()->groupBy('module_name');

        return Inertia::render('roles/Create', [
            'permissions' => $permissions
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRoleRequest $request)
    {
        $validated = $request->validated();

        $role = Role::create(['name' => $validated['name']]);
        $role->syncPermissions($validated['permissions']);

        return redirect()->route('roles.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role)
    {
        $permissions = $role->permissions->groupBy('module_name');

        return Inertia::render('roles/Show', [
            'role' => $role,
            'permissions' => $permissions,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role)
    {
        $permissions = Permission::all()->groupBy('module_name');

        $rolePermissions = $role->permissions->pluck('id');

        return Inertia::render('roles/Edit', [
            'role' => $role,
            'rolePermissions' => $rolePermissions,
            'permissions' => $permissions,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRoleRequest $request, Role $role)
    {
        $validated = $request->validated();

        $role->update(['name' => $validated['name']]);
        $role->syncPermissions($validated['permissions']);

        return redirect()->route('roles.index')->with('success', 'Role updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $role = Role::find($id);
        $role->delete();

        return redirect()->route('roles.index');
    }

    public function permissions(Request $request)
    {
        if ($request->has('search')) {
            $permissions = Permission::query()
                ->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('module_name', 'like', '%' . $request->search . '%')
                ->orderBy('name', 'asc')
                ->get()
                ->groupBy('module_name');
        } else {
            $permissions = Permission::all()->groupBy('module_name');
        }
        // $permissions = Permission::all()->groupBy('module_name');

        return Inertia::render('roles/Permissions', [
            'permissions' => $permissions,
            'searchTerm' => request()->only('search'),
        ]);
    }
}