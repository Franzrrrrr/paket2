<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use BezhanSalleh\FilamentShield\Support\Utils;

class ShieldGenerateSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $resources = [
            'AreaParkir', 'Kendaraan', 'ParkingSession',
            'Tarif', 'Transaksi', 'User', 'LogAktivitas',
        ];

        $actions = ['view_any', 'view', 'create', 'update', 'delete', 'delete_any', 'force_delete', 'force_delete_any', 'restore', 'restore_any'];

        foreach ($resources as $resource) {
            foreach ($actions as $action) {
                Permission::firstOrCreate([
                    'name'       => "{$action}_{$resource}",
                    'guard_name' => 'web',
                ]);
            }
        }

        $superAdminRole = Role::firstOrCreate([
            'name'       => 'super_admin',
            'guard_name' => 'web',
        ]);
        $superAdminRole->syncPermissions(Permission::all());

        $user = User::where('email', 'admin@example.com')->first();
        if ($user) {
            $user->syncRoles(['super_admin', 'admin']);
            $this->command->info('✓ super_admin assigned to admin@example.com');
        } else {
            $this->command->error('User admin@example.com not found!');
        }
    }
}
