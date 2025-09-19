<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'owe.achmad@yahoo.com',
            'password' => bcrypt('password'),
        ])->assignRole('super-admin');

        User::factory(200)->create()->each(function ($user) {
            $user->assignRole('writer');
        });
    }
}