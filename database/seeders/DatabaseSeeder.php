<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Owner',
            'username' => 'owner',
            'email' => 'owner@karomah.test',
            'role' => 'owner',
        ]);

        User::factory()->create([
            'name' => 'Karyawan',
            'username' => 'karyawan',
            'email' => 'karyawan@karomah.test',
            'role' => 'karyawan',
        ]);

        $this->call(KategoriSeeder::class);
    }
}
