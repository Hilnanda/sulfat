<?php

namespace Database\Seeders;

use App\Models\Periode;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        

        $user = [
            [
                'nama' => 'Developer',
                'username' => 'developer',
                'email' => 'developer@gmail.com',
                'password' => bcrypt('12345678'),
                'jabatan' => 'manager',
                'status' => 1,
                'no_hp' => '0812345678',
            ],
            [
                'nama' => 'Kepala',
                'username' => 'kepala',
                'email' => 'kepala@gmail.com',
                'password' => bcrypt('12345678'),
                'jabatan' => 'kepala',
                'status' => 1,
                'no_hp' => '0812345678',
            ],
        ];

        User::insert($user);
    }
}
