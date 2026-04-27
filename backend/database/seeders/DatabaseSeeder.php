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
    // Kaamerad
    ['name' => 'Canon 700D', 'serial_number' => 'CAM-F-001', 'barcode' => '1001001001001', 'category' => 'Kaamerad'],
    ['name' => 'Canon 700D', 'serial_number' => 'CAM-F-002', 'barcode' => '1001001001002', 'category' => 'Kaamerad'],
    ['name' => 'Canon 700D', 'serial_number' => 'CAM-F-003', 'barcode' => '1001001001003', 'category' => 'Kaamerad'],
    ['name' => 'Canon 700D', 'serial_number' => 'CAM-F-004', 'barcode' => '1001001001004', 'category' => 'Kaamerad'],
    ['name' => 'Canon 700D', 'serial_number' => 'CAM-F-005', 'barcode' => '1001001001005', 'category' => 'Kaamerad'],
    ['name' => 'Canon 700D', 'serial_number' => 'CAM-F-006', 'barcode' => '1001001001006', 'category' => 'Kaamerad'],
    ['name' => 'Canon 750D', 'serial_number' => 'CAM-F-007', 'barcode' => '1001001001007', 'category' => 'Kaamerad'],
    ['name' => 'Canon 6D',   'serial_number' => 'CAM-F-008', 'barcode' => '1001001001008', 'category' => 'Kaamerad'],
    ['name' => 'Canon R6',   'serial_number' => 'CAM-F-009', 'barcode' => '1001001001009', 'category' => 'Kaamerad'],
    ['name' => 'Canon R6',   'serial_number' => 'CAM-F-010', 'barcode' => '1001001001010', 'category' => 'Kaamerad'],
    ['name' => 'Canon R6',   'serial_number' => 'CAM-F-011', 'barcode' => '1001001001011', 'category' => 'Kaamerad'],
    ['name' => 'Canon R6',   'serial_number' => 'CAM-F-012', 'barcode' => '1001001001012', 'category' => 'Kaamerad'],

    // Mälukaardid
    ['name' => 'Mälukaart 8GB',   'serial_number' => 'MEM-001', 'barcode' => '1001001003001', 'category' => 'Mälukaardid', 'capacity' => '8GB'],
    ['name' => 'Mälukaart 16GB',  'serial_number' => 'MEM-002', 'barcode' => '1001001003002', 'category' => 'Mälukaardid', 'capacity' => '16GB'],
    ['name' => 'Mälukaart 32GB',  'serial_number' => 'MEM-003', 'barcode' => '1001001003003', 'category' => 'Mälukaardid', 'capacity' => '32GB'],
    ['name' => 'Mälukaart 64GB',  'serial_number' => 'MEM-004', 'barcode' => '1001001003004', 'category' => 'Mälukaardid', 'capacity' => '64GB'],
    ['name' => 'Mälukaart 256GB', 'serial_number' => 'MEM-005', 'barcode' => '1001001003005', 'category' => 'Mälukaardid', 'capacity' => '256GB'],

    // Statiivid
    ['name' => 'Manfrotto foto statiiv',  'serial_number' => 'STT-001', 'barcode' => '1001001004001', 'category' => 'Statiivid'],
    ['name' => 'Manfrotto video statiiv', 'serial_number' => 'STT-002', 'barcode' => '1001001004002', 'category' => 'Statiivid'],
    ['name' => 'Velbon statiiv',          'serial_number' => 'STT-003', 'barcode' => '1001001004003', 'category' => 'Statiivid'],

    // Akud
    ['name' => 'Aku LP-E6',  'serial_number' => 'AKU-001', 'barcode' => '1001001005001', 'category' => 'Akud'],
    ['name' => 'Aku LP-E6',  'serial_number' => 'AKU-002', 'barcode' => '1001001005002', 'category' => 'Akud'],
    ['name' => 'Aku LP-E17', 'serial_number' => 'AKU-003', 'barcode' => '1001001005003', 'category' => 'Akud'],
    ['name' => 'Aku LP-E17', 'serial_number' => 'AKU-004', 'barcode' => '1001001005004', 'category' => 'Akud'],

    // Akulaadijad
    ['name' => 'Canon akulaadija LC-E6',  'serial_number' => 'LAA-001', 'barcode' => '1001001006001', 'category' => 'Akulaadijad'],
    ['name' => 'Canon akulaadija LC-E17', 'serial_number' => 'LAA-002', 'barcode' => '1001001006002', 'category' => 'Akulaadijad'],

    // Objektiivid
    ['name' => 'Sigma 50mm',     'serial_number' => 'OBJ-001', 'barcode' => '1001001007001', 'category' => 'Objektiivid'],
    ['name' => 'Sigma 50mm',     'serial_number' => 'OBJ-002', 'barcode' => '1001001007002', 'category' => 'Objektiivid'],
    ['name' => 'Sigma 35mm',     'serial_number' => 'OBJ-003', 'barcode' => '1001001007003', 'category' => 'Objektiivid'],
    ['name' => 'Sigma 105mm',    'serial_number' => 'OBJ-004', 'barcode' => '1001001007004', 'category' => 'Objektiivid'],
    ['name' => 'Sigma 70-200mm', 'serial_number' => 'OBJ-005', 'barcode' => '1001001007005', 'category' => 'Objektiivid'],
    ['name' => 'Sigma 24-70mm',  'serial_number' => 'OBJ-006', 'barcode' => '1001001007006', 'category' => 'Objektiivid'],
    ['name' => 'Sigma 24-70mm',  'serial_number' => 'OBJ-007', 'barcode' => '1001001007007', 'category' => 'Objektiivid'],
    ['name' => 'Canon 85mm',     'serial_number' => 'OBJ-008', 'barcode' => '1001001007008', 'category' => 'Objektiivid'],

    // Kotid
    ['name' => 'Kaamerakott',    'serial_number' => 'KOT-001', 'barcode' => '1001001008001', 'category' => 'Kotid'],
    ['name' => 'Kaamerakott',    'serial_number' => 'KOT-002', 'barcode' => '1001001008002', 'category' => 'Kotid'],

    // Sülearvutid
    ['name' => 'Sülearvuti',     'serial_number' => 'SYL-001', 'barcode' => '1001001009001', 'category' => 'Sülearvuti'],
    ['name' => 'Sülearvuti',     'serial_number' => 'SYL-002', 'barcode' => '1001001009002', 'category' => 'Sülearvuti'],

    // Digilaud
    ['name' => 'Digilaud',       'serial_number' => 'DIG-001', 'barcode' => '1001001010001', 'category' => 'Digilaud'],

    // Graafikalaud
    ['name' => 'Graafikalaud',   'serial_number' => 'GRA-001', 'barcode' => '1001001011001', 'category' => 'Graafikalaud'],
    ['name' => 'Graafikalaud',   'serial_number' => 'GRA-002', 'barcode' => '1001001011002', 'category' => 'Graafikalaud'],

    // Stuudiovälgud
    ['name' => 'Stuudiovälk',    'serial_number' => 'STU-001', 'barcode' => '1001001012001', 'category' => 'Stuudiovälgud'],
    ['name' => 'Stuudiovälk',    'serial_number' => 'STU-002', 'barcode' => '1001001012002', 'category' => 'Stuudiovälgud'],

    // Valgustid (video)
    ['name' => 'Videovalgusti',  'serial_number' => 'VAL-001', 'barcode' => '1001001013001', 'category' => 'Valgustid (video)'],
    ['name' => 'Videovalgusti',  'serial_number' => 'VAL-002', 'barcode' => '1001001013002', 'category' => 'Valgustid (video)'],

    // Välgud (kaamera)
    ['name' => 'Kaameravälk',    'serial_number' => 'VLG-001', 'barcode' => '1001001014001', 'category' => 'Välgud (kaamera)'],
    ['name' => 'Kaameravälk',    'serial_number' => 'VLG-002', 'barcode' => '1001001014002', 'category' => 'Välgud (kaamera)'],

    // Filtrid
    ['name' => 'UV Filter 77mm', 'serial_number' => 'FIL-001', 'barcode' => '1001001015001', 'category' => 'Filtrid'],
    ['name' => 'ND Filter 82mm', 'serial_number' => 'FIL-002', 'barcode' => '1001001015002', 'category' => 'Filtrid'],
];

        foreach ($devices as $data) {
            Device::create(array_merge($data, ['status' => 'available', 'description' => 'School equipment available for borrowing.']));
        }
    }
}
