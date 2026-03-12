<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Branch::create([
        'name' => 'Sede 1',
        'address' => 'Centro, calle meneco',
    ]);

    \App\Models\Branch::create([
        'name' => 'Sede 2',
        'address' => 'Zona industrial, Lucianero II',
    ]);
    }
}
