<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Department;

class UserSeeder extends Seeder
{
    public function run()
    {
        $management = Department::where('name', 'Management')->first();
        $hr = Department::where('name', 'HR')->first();
        $mfg = Department::where('name', 'Manufacturing')->first();

        $hrAdmin = User::updateOrCreate(
            ['email' => 'hr.admin@ntg.com.bd'],
            [
                'employee_id' => 'HR001',
                'name' => 'Iqram Raihan',
                'email' => 'hr.admin@ntg.com.bd',
                'password' => bcrypt('12345678'),
                'password_plain' => '12345678',
                'user_image' => asset('images/users/avatar.png'),
                'role' => 'hr_admin',
                'department_id' => $hr->id
            ]
        );

        $board = User::updateOrCreate(
            ['email' => 'muhimhassan@til.com'],
            [
                'employee_id' => 'TIL001',
                'name' => 'Muhim Hassan',
                'email' => 'muhimhassan@til.com',
                'password' => bcrypt('12345678'),
                'password_plain' => '12345678',
                'user_image' => asset('images/users/avatar.png'),
                'role' => 'board',
                'department_id' => $management->id
            ]
        );

        $manager = User::updateOrCreate(
            ['email' => 'manager@ntg.com.bd'],
            [
                'employee_id' => 'LM001',
                'name' => 'Hasibul Islam Santo',
                'email' => 'manager@ntg.com.bd',
                'password' => bcrypt('12345678'),
                'password_plain' => '12345678',
                'user_image' => asset('images/users/avatar.png'),
                'role' => 'line_manager',
                'department_id' => $mfg->id
            ]
        );

        $emp = User::updateOrCreate(
            ['email' => 'employee@ntg.com.bd'],
            [
                'employee_id' => 'E001',
                'name' => 'Abir Ahmed',
                'email' => 'employee@ntg.com.bd',
                'password' => bcrypt('12345678'),
                'password_plain' => '12345678',
                'user_image' => asset('images/users/avatar.png'),
                'role' => 'employee',
                'department_id' => $mfg->id,
                'line_manager_id' => $manager->id
            ]
        );
    }
}
