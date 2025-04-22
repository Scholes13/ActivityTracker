<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $scRole = Role::firstOrCreate(['name' => 'sc']);
        $captainRole = Role::firstOrCreate(['name' => 'captain']);
        $leaderRole = Role::firstOrCreate(['name' => 'leader']);
        $memberRole = Role::firstOrCreate(['name' => 'member']);

        // Create admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );
        
        $admin->assignRole($adminRole);

        // Create sample SC user
        $sc = User::firstOrCreate(
            ['email' => 'sc@example.com'],
            [
                'name' => 'Steering Committee',
                'password' => Hash::make('password'),
                'role' => 'sc',
                'email_verified_at' => now(),
            ]
        );
        
        $sc->assignRole($scRole);

        // Create sample Captain
        $captain = User::firstOrCreate(
            ['email' => 'captain@example.com'],
            [
                'name' => 'Captain',
                'password' => Hash::make('password'),
                'role' => 'captain',
                'parent_id' => $sc->id,
                'email_verified_at' => now(),
            ]
        );
        
        $captain->assignRole($captainRole);

        // Create sample Leader
        $leader = User::firstOrCreate(
            ['email' => 'leader@example.com'],
            [
                'name' => 'Leader',
                'password' => Hash::make('password'),
                'role' => 'leader',
                'parent_id' => $captain->id,
                'email_verified_at' => now(),
            ]
        );
        
        $leader->assignRole($leaderRole);

        // Create sample Member
        $member = User::firstOrCreate(
            ['email' => 'member@example.com'],
            [
                'name' => 'Team Member',
                'password' => Hash::make('password'),
                'role' => 'member',
                'parent_id' => $leader->id,
                'email_verified_at' => now(),
            ]
        );
        
        $member->assignRole($memberRole);
    }
}
