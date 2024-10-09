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
            'email' => 'hussein.were@tezi.co.ke',
            'account_type' => 'ADMIN',
            'password' => '$2a$12$Lx.UD20iAN1SG9iLwUr0g.3WijIyOKJPIpR.3dtqcRDb9dbD6u8UK'
        ]);
        DB::table('admins')->insert([
            'user_id' => 1,
            'email' => 'hussein.were@tezi.co.ke',
            'name' => 'Hussein Were',
        ]);

        DB::table('users')->insert([
            'email' => 'austin.post@tezi.co.ke',
            'account_type' => 'EMPLOYER',
            'password' => '$2a$12$Lx.UD20iAN1SG9iLwUr0g.3WijIyOKJPIpR.3dtqcRDb9dbD6u8UK'
        ]);
        DB::table('employers')->insert([
            'user_id' => 2,
            'email' => 'austin.post@tezi.co.ke',
            'name' => 'Austin Post',
            'phone' => '3256356',
            'address' => '123Austin',
        ]);

        DB::table('users')->insert([
            'email' => 'travis.scott@tezi.co.ke',
            'account_type' => 'EMPLOYER',
            'password' => '$2a$12$Lx.UD20iAN1SG9iLwUr0g.3WijIyOKJPIpR.3dtqcRDb9dbD6u8UK'
        ]);
        DB::table('employers')->insert([
            'user_id' => 3,
            'email' => 'travis.scott@tezi.co.ke',
            'name' => 'Travis Scott',
            'phone' => '3259356',
            'address' => '123Scott',
        ]);
    }
}
