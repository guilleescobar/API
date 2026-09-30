<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'juan@example.com'],
            [
                'name' => 'Juan Perez',
                'username' => 'juanperez',
                'password' => Hash::make('password123'),
            ]
        );

        User::firstOrCreate(
            ['email' => 'maria@example.com'],
            [
                'name' => 'Maria Garcia',
                'username' => 'mariagarcia',
                'password' => Hash::make('password123'),
            ]
        );
    }
}
