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
            Permission::firstOrCreate(['guard_name' => 'api', 'name' => $p]);
        }

        // ── roles & permission assignments ────────────────────────────
        $superAdmin = Role::firstOrCreate(['guard_name' => 'api', 'name' => 'super-admin']);
        $superAdmin->givePermissionTo(Permission::all());

        $free = Role::firstOrCreate(['guard_name' => 'api', 'name' => 'free']);

        $silver = Role::firstOrCreate(['guard_name' => 'api', 'name' => 'silver']);
        $silver->givePermissionTo('save-flows');

        $gold = Role::firstOrCreate(['guard_name' => 'api', 'name' => 'gold']);
        $gold->givePermissionTo(['save-flows', 'generate-schema']);

        $platinum = Role::firstOrCreate(['guard_name' => 'api', 'name' => 'platinum']);
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

        // ── tier demo users ───────────────────────────────────────────
        $freeUser = User::firstOrCreate(
            ['email' => 'freeuser@mail.com'],
            [
                'name' => 'Free User',
                'password' => Hash::make('password'),
            ],
        );
        $freeUser->assignRole('free');

        $silverUser = User::firstOrCreate(
            ['email' => 'silveruser@mail.com'],
            [
                'name' => 'Silver User',
                'password' => Hash::make('password'),
                'subscription_expires_at' => now()->addDays(33),
            ],
        );
        $silverUser->assignRole('silver');

        $goldUser = User::firstOrCreate(
            ['email' => 'golduser@mail.com'],
            [
                'name' => 'Gold User',
                'password' => Hash::make('password'),
                'subscription_expires_at' => now()->addDays(37),
            ],
        );
        $goldUser->assignRole('gold');

        $platinumUser = User::firstOrCreate(
            ['email' => 'platinumuser@mail.com'],
            [
                'name' => 'Platinum User',
                'password' => Hash::make('password'),
                'subscription_expires_at' => now()->addDays(44),
            ],
        );
        $platinumUser->assignRole('platinum');
    }
}
