<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['name' => 'view_users', 'group' => 'Usuarios'],
            ['name' => 'create_users', 'group' => 'Usuarios'],
            ['name' => 'edit_users', 'group' => 'Usuarios'],
            ['name' => 'delete_users', 'group' => 'Usuarios'],

            ['name' => 'view_roles', 'group' => 'Roles'],
            ['name' => 'create_roles', 'group' => 'Roles'],
            ['name' => 'edit_roles', 'group' => 'Roles'],
            ['name' => 'delete_roles', 'group' => 'Roles'],

            ['name' => 'view_permisos', 'group' => 'Permisos'],
            ['name' => 'edit_permisos', 'group' => 'Permisos'],

            ['name' => 'view_categorias', 'group' => 'Categorías'],
            ['name' => 'create_categorias', 'group' => 'Categorías'],
            ['name' => 'edit_categorias', 'group' => 'Categorías'],
            ['name' => 'delete_categorias', 'group' => 'Categorías'],

            ['name' => 'view_productos', 'group' => 'Productos'],
            ['name' => 'create_productos', 'group' => 'Productos'],
            ['name' => 'edit_productos', 'group' => 'Productos'],
            ['name' => 'delete_productos', 'group' => 'Productos'],

            ['name' => 'view_servicios', 'group' => 'Servicios'],
            ['name' => 'create_servicios', 'group' => 'Servicios'],
            ['name' => 'edit_servicios', 'group' => 'Servicios'],
            ['name' => 'delete_servicios', 'group' => 'Servicios'],

            ['name' => 'view_galeria', 'group' => 'Galería'],
            ['name' => 'create_galeria', 'group' => 'Galería'],
            ['name' => 'edit_galeria', 'group' => 'Galería'],
            ['name' => 'delete_galeria', 'group' => 'Galería'],

            ['name' => 'view_clientes', 'group' => 'Clientes'],
            ['name' => 'create_clientes', 'group' => 'Clientes'],
            ['name' => 'edit_clientes', 'group' => 'Clientes'],
            ['name' => 'delete_clientes', 'group' => 'Clientes'],

            ['name' => 'view_consultas', 'group' => 'Consultas'],
            ['name' => 'edit_consultas', 'group' => 'Consultas'],
            ['name' => 'delete_consultas', 'group' => 'Consultas'],

            ['name' => 'view_cotizaciones', 'group' => 'Cotizaciones'],
            ['name' => 'create_cotizaciones', 'group' => 'Cotizaciones'],
            ['name' => 'edit_cotizaciones', 'group' => 'Cotizaciones'],
            ['name' => 'delete_cotizaciones', 'group' => 'Cotizaciones'],

            ['name' => 'view_pagos', 'group' => 'Pagos'],
            ['name' => 'create_pagos', 'group' => 'Pagos'],
            ['name' => 'edit_pagos', 'group' => 'Pagos'],
            ['name' => 'delete_pagos', 'group' => 'Pagos'],

            ['name' => 'view_reportes', 'group' => 'Reportes'],
            ['name' => 'export_reportes', 'group' => 'Reportes'],

            ['name' => 'view_configuracion', 'group' => 'Configuración'],
            ['name' => 'edit_configuracion', 'group' => 'Configuración'],
        ];

        $createdPermissions = [];
        foreach ($permissions as $perm) {
            $createdPermissions[$perm['name']] = Permission::findOrCreate($perm['name'], 'web');
            $createdPermissions[$perm['name']]->update(['group' => $perm['group']]);
        }

        $superAdmin = Role::findOrCreate('Super Admin', 'web');
        $admin = Role::findOrCreate('Admin', 'web');
        $vendedor = Role::findOrCreate('Vendedor', 'web');

        $superAdmin->syncPermissions(Permission::all());

        $admin->syncPermissions([
            'view_users', 'create_users', 'edit_users',
            'view_roles', 'create_roles', 'edit_roles',
            'view_permisos', 'edit_permisos',
            'view_categorias', 'create_categorias', 'edit_categorias',
            'view_productos', 'create_productos', 'edit_productos',
            'view_servicios', 'create_servicios', 'edit_servicios',
            'view_galeria', 'create_galeria', 'edit_galeria',
            'view_clientes', 'create_clientes', 'edit_clientes',
            'view_consultas', 'edit_consultas',
            'view_cotizaciones', 'create_cotizaciones', 'edit_cotizaciones',
            'view_pagos', 'create_pagos', 'edit_pagos',
            'view_reportes', 'export_reportes',
            'view_configuracion', 'edit_configuracion',
        ]);

        $vendedor->syncPermissions([
            'view_categorias',
            'view_productos',
            'view_servicios',
            'view_galeria',
            'view_clientes',
            'view_consultas', 'edit_consultas',
            'view_cotizaciones', 'create_cotizaciones', 'edit_cotizaciones',
            'view_pagos', 'create_pagos',
        ]);
    }
}
