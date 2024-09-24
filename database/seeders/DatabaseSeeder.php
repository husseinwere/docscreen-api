<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            'name' => 'Hussein Were',
            'slug' => 'hussein-were',
            'phone' => '0727854413',
            'email' => 'hussein.were@tezi.co.ke',
            'account_type' => 0,
            'password' => '$2a$12$Lx.UD20iAN1SG9iLwUr0g.3WijIyOKJPIpR.3dtqcRDb9dbD6u8UK'
        ]);
    }
}
