<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        \App\Models\User::factory(10)->create();

        \App\Models\User::factory()->create([
            'name' => 'Fadiel Muhammad',
            'email' => 'fadielintip@gmail.com',
            'password' => Hash::make('password'),
            'phone' => '081902570945',
        ]);


        // $this->call([
        //     ProductSeeder::class,
        // ]);
    }
}
