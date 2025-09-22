<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AcademicYearSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // AcademicYear::factory()->count(5)->create();
        // Atau buat manual
        AcademicYear::create([
            'name' => '2024/2025',
            'is_active' => false,
        ]);
        AcademicYear::create([
            'name' => '2025/2026',
            'is_active' => true, // Jadikan aktif
        ]);
    }
}