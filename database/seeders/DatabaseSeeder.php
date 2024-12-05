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
            'phone' => '3256356',
            'first_name' => 'Austin',
            'last_name' => 'Post',
            'function' => '123Austin',
            'billing_email' => 'sdkfl@djkf.dc',
            'kvk_number' => '123Austin',
            'organization' => '123Austin',
            'address' => '123Austin',
            'location' => '123Austin',
            'postcode' => '123Austin'
        ]);

        DB::table('document_types')->insert([
            'employer_id' => 1,
            'title' => 'DUO DIPLOMA UITTREKSEL'
        ]);
    }
}
