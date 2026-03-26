<?php

namespace Database\Seeders;

use App\Models\Device;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create(['name' => 'Admin Teacher',  'email' => 'admin@school.edu',   'password' => Hash::make('password'), 'role' => 'admin']);
        User::create(['name' => 'John Student',   'email' => 'student@school.edu', 'password' => Hash::make('password'), 'role' => 'student']);

        $devices = [
            ['name' => 'MacBook Pro 14"',    'serial_number' => 'MBP-2024-001', 'barcode' => '1001001001001'],
            ['name' => 'iPad Pro 12.9"',     'serial_number' => 'IPD-2024-001', 'barcode' => '1001001001002'],
            ['name' => 'Nikon D7500 Camera', 'serial_number' => 'CAM-2024-001', 'barcode' => '1001001001003'],
            ['name' => 'Canon EOS R50',      'serial_number' => 'CAM-2024-002', 'barcode' => '1001001001004'],
            ['name' => 'Dell XPS 15',        'serial_number' => 'DLL-2024-001', 'barcode' => '1001001001005'],
            ['name' => 'Sony WH-1000XM5',    'serial_number' => 'SNY-2024-001', 'barcode' => '1001001001006'],
            ['name' => 'Raspberry Pi 5',     'serial_number' => 'RPI-2024-001', 'barcode' => '1001001001007'],
            ['name' => 'Arduino Mega Kit',   'serial_number' => 'ARD-2024-001', 'barcode' => '1001001001008'],
        ];

        foreach ($devices as $data) {
            Device::create(array_merge($data, ['status' => 'available', 'description' => 'School equipment available for borrowing.']));
        }
    }
}
