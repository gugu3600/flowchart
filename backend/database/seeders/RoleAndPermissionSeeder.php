<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        // ── permissions ──────────────────────────────────────────────
        $permissions = [
            // Tier-gated feature permissions
            'save-flows',
            'generate-schema',
            'map-structure',
            // Admin-only
            'manage-users',
            'manage-roles',
        ];

        foreach ($permissions as $p) {
            Permission::create(['guard_name' => 'api', 'name' => $p]);
        }

        // ── roles & permission assignments ────────────────────────────
        $superAdmin = Role::create(['guard_name' => 'api', 'name' => 'super-admin']);
        $superAdmin->givePermissionTo(Permission::all());

        $free = Role::create(['guard_name' => 'api', 'name' => 'free']);

        $silver = Role::create(['guard_name' => 'api', 'name' => 'silver']);
        $silver->givePermissionTo('save-flows');

        $gold = Role::create(['guard_name' => 'api', 'name' => 'gold']);
        $gold->givePermissionTo(['save-flows', 'generate-schema']);

        $platinum = Role::create(['guard_name' => 'api', 'name' => 'platinum']);
        $platinum->givePermissionTo(['save-flows', 'generate-schema', 'map-structure']);

        // ── default super-admin user ──────────────────────────────────
        $admin = User::firstOrCreate(
            ['email' => 'admin@flowchart.dev'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
            ],
        );
        $admin->assignRole('super-admin');
    }
}
