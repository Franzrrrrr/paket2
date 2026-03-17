<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

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

    }
}
