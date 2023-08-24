<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //User::factory(10)->create();

        $user = new User;
        $user->name = 'Admin';
        $user->username = '';
        $user->email = 'admin@gtmx.com';
        $user->status = 'A';
        $user->origin = 'app';
        $user->rol_id = 1;
        $user->password = Hash::make('Admin2020*');
        $user->save();
    }
}
