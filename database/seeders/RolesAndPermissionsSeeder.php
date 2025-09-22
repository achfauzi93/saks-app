<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // create permissions
        Permission::create(['module_name' => 'dashboard', 'name' => 'view-dashboard']);
        // roles
        Permission::create(['module_name' => 'manage roles', 'name' => 'view-roles']);
        Permission::create(['module_name' => 'manage roles', 'name' => 'create-roles']);
        Permission::create(['module_name' => 'manage roles', 'name' => 'edit-roles']);
        Permission::create(['module_name' => 'manage roles', 'name' => 'delete-roles']);
        // permissions
        Permission::create(['module_name' => 'manage permissions', 'name' => 'view-permissions']);
        // users
        Permission::create(['module_name' => 'manage users', 'name' => 'view-users']);
        Permission::create(['module_name' => 'manage users', 'name' => 'create-users']);
        Permission::create(['module_name' => 'manage users', 'name' => 'edit-users']);
        Permission::create(['module_name' => 'manage users', 'name' => 'delete-users']);





        // update cache to know about the newly created permissions (required if using WithoutModelEvents in seeders)
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();


        // create roles and assign created permissions

        Role::create(['name' => 'admin']);
        Role::create(['name' => 'siswa']);
        Role::create(['name' => 'tu']);
        Role::create(['name' => 'guru']);
        Role::create(['name' => 'sekretaris']);
        Role::create(['name' => 'guru-bk']);
        // this can be done as separate statements
        $role = Role::create(['name' => 'writer']);
        $role->givePermissionTo('view-dashboard');

        // or may be done by chaining
        $role = Role::create(['name' => 'moderator'])
            ->givePermissionTo(['view-roles', 'edit-roles']);

        $role = Role::create(['name' => 'super-admin']);
        // $role->givePermissionTo(Permission::all());
    }
}