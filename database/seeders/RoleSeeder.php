<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // $admin = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin']);
        // $petugas = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'petugas']);
        // $owner = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'owner']);

        // $allPermissions = \Spatie\Permission\Models\Permission::all();

        // $admin->syncPermissions($allPermissions);

        // $userAdmin = \App\Models\User::firstOrCreate(
        //     ['email' => 'admin@example.com'],
        //     [
        //         'name' => 'Admin',
        //         'password' => bcrypt('password'),
        //     ]
        // );
        // $userAdmin->assignRole($admin);

        // $userPetugas = \App\Models\User::firstOrCreate(
        //     ['email' => 'petugas@example.com'],
        //     [
        //         'name' => 'Petugas',
        //         'password' => bcrypt('password'),
        //     ]
        // );
        // $userPetugas->assignRole($petugas);

        // $userOwner = \App\Models\User::firstOrCreate(
        //     ['email' => 'owner@example.com'],
        //     [
        //         'name' => 'Owner',
        //         'password' => bcrypt('password'),
        //     ]
        // );
        // $userOwner->assignRole($owner);
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        $resources = [
            'AreaParkir',
            'Kendaraan',
            'ParkingSession',
            'Tarif',
            'Transaksi',
            'User',
            'LogAktivitas',
        ];
        $actions = ['view_any', 'view', 'create', 'update', 'delete', 'delete_any', 'force_delete', 'force_delete_any', 'restore', 'restore_any'];
        foreach ($resources as $resource) {
            foreach ($actions as $action) {
                Permission::firstOrCreate([
                    'name' => "{$action}_{$resource}",
                    'guard_name' => 'web',
                ]);
            }
        }
        $superAdminRole = Role::firstOrCreate([
            'name' => 'super_admin',
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
