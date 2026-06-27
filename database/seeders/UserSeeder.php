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
        $it = Department::where('name', 'IT')->first(); // Assuming IT department exists

        // Super Administrator
        User::updateOrCreate(
            ['email' => 'admin@ntg.com.bd'],
            [
                'employee_id' => 'SUPER001',
                'name' => 'Super Administrator',
                'password' => bcrypt('12345678'),
                'password_plain' => '12345678',
                'user_image' => asset('images/users/avatar.png'),
                'role' => 'super_admin',
                'department_id' => null,
                'date_of_joining' => '2026-04-28',
                'tenure_in_current_role' => 'N/A',
                'line_manager_id' => null,
                'is_active' => 1,
            ]
        );

        // HR Admin
        User::updateOrCreate(
            ['email' => 'hr.admin@ntg.com.bd'],
            [
                'employee_id' => 'HR001',
                'name' => 'Iqram Raihan',
                'password' => bcrypt('12345678'),
                'password_plain' => '12345678',
                'user_image' => asset('images/users/avatar.png'),
                'role' => 'hr_admin',
                'department_id' => $hr->id,
                'date_of_joining' => null,
                'tenure_in_current_role' => null,
                'line_manager_id' => null,
                'is_active' => 1,
            ]
        );

        // Board Member (from model)
        User::updateOrCreate(
            ['email' => 'board@ntg.com.bd'],
            [
                'employee_id' => 'B001',
                'name' => 'Board Member',
                'password' => bcrypt('12345678'),
                'password_plain' => '12345678',
                'user_image' => asset('images/users/avatar.png'),
                'role' => 'board',
                'department_id' => $hr->id,
                'date_of_joining' => null,
                'tenure_in_current_role' => null,
                'line_manager_id' => null,
                'is_active' => 1,
            ]
        );

        // Board Member (Muhim Hassan - from model)
        User::updateOrCreate(
            ['email' => 'muhimhassan@til.com'],
            [
                'employee_id' => 'TIL001',
                'name' => 'Muhim Hassan',
                'password' => bcrypt('12345678'),
                'password_plain' => '12345678',
                'user_image' => 'user_images/v6nbkXfx0wRyQJ6WOxQaGYLza5A66EFobg0eNrBO.jpg',
                'role' => 'board',
                'department_id' => $management->id,
                'date_of_joining' => null,
                'tenure_in_current_role' => null,
                'line_manager_id' => null,
                'is_active' => 1,
            ]
        );

        // Line Manager (Hasibul Islam Santo)
        User::updateOrCreate(
            ['email' => 'manager@ntg.com.bd'],
            [
                'employee_id' => 'LM001',
                'name' => 'Hasibul Islam Santo',
                'password' => bcrypt('12345678'),
                'password_plain' => '12345678',
                'user_image' => asset('images/users/avatar.png'),
                'role' => 'line_manager',
                'department_id' => $mfg->id,
                'date_of_joining' => null,
                'tenure_in_current_role' => null,
                'line_manager_id' => 2, // HR Admin ID
                'is_active' => 1,
            ]
        );

        // Line Manager (Jakir Hossain - from model)
        User::updateOrCreate(
            ['email' => 'jakir@ntg.com'],
            [
                'employee_id' => 'TIL0035',
                'name' => 'Jakir Hossain',
                'password' => bcrypt('12345678'),
                'password_plain' => '12345678',
                'user_image' => null,
                'role' => 'line_manager',
                'department_id' => $it->id,
                'date_of_joining' => null,
                'tenure_in_current_role' => null,
                'line_manager_id' => 6, // Muhim Hassan ID
                'is_active' => 1,
            ]
        );

        // Employee (Abir Ahmed)
        $manager = User::where('email', 'manager@ntg.com.bd')->first();
        User::updateOrCreate(
            ['email' => 'employee@ntg.com.bd'],
            [
                'employee_id' => 'E001',
                'name' => 'Abir Ahmed',
                'password' => bcrypt('12345678'),
                'password_plain' => '12345678',
                'user_image' => asset('images/users/avatar.png'),
                'role' => 'employee',
                'department_id' => $mfg->id,
                'date_of_joining' => null,
                'tenure_in_current_role' => null,
                'line_manager_id' => $manager->id,
                'is_active' => 1,
            ]
        );

        // Employee (Imran Sultan - from model)
        $managerSultan = User::where('email', 'manager@ntg.com.bd')->first();
        User::updateOrCreate(
            ['email' => 'employeethree@ntg.com'],
            [
                'employee_id' => 'TIL0038',
                'name' => 'Imran Sultan',
                'password' => bcrypt('12345678'),
                'password_plain' => '12345678',
                'user_image' => null,
                'role' => 'employee',
                'department_id' => $mfg->id,
                'date_of_joining' => null,
                'tenure_in_current_role' => null,
                'line_manager_id' => $managerSultan->id,
                'is_active' => 1,
            ]
        );

        // Employee (Musanna Al - from model)
        $managerMusanna = User::where('email', 'manager@ntg.com.bd')->first();
        User::updateOrCreate(
            ['email' => 'empfour@ntg.com'],
            [
                'employee_id' => 'TIL0039',
                'name' => 'Musanna Al',
                'password' => bcrypt('12345678'),
                'password_plain' => '12345678',
                'user_image' => null,
                'role' => 'employee',
                'department_id' => $mfg->id,
                'date_of_joining' => null,
                'tenure_in_current_role' => null,
                'line_manager_id' => $managerMusanna->id,
                'is_active' => 1,
            ]
        );
    }
}