<?php

namespace Database\Seeders;

use App\Models\ViolationType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ViolationTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ViolationType::create([
            'name' => 'Terlambat',
            'description' => 'Siswa datang terlambat ke sekolah',
            'is_active' => true,
        ]);
        ViolationType::create([
            'name' => 'Tidak Memakai Seragam',
            'description' => 'Siswa tidak memakai seragam sekolah yang sesuai',
            'is_active' => true,
        ]);
        ViolationType::create([
            'name' => 'Bolos',
            'description' => 'Siswa tidak hadir tanpa izin',
            'is_active' => true,
        ]);
        ViolationType::create([
            'name' => 'Merokok',
            'description' => 'Siswa kedapatan merokok di lingkungan sekolah',
            'is_active' => true,
        ]);
        ViolationType::create([
            'name' => 'Perkelahian',
            'description' => 'Siswa terlibat dalam perkelahian dengan siswa lain',
            'is_active' => true,
        ]);
    }
}