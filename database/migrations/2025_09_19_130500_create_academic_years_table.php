<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('academic_years', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Format: "2024/2025"
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        // Menambahkan constraint unik untuk memastikan hanya satu tahun akademik yang aktif
        DB::statement('
            CREATE UNIQUE INDEX idx_unique_active_academic_year
            ON academic_years (is_active)
            WHERE is_active = true;
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_years');
        DB::statement('DROP INDEX IF EXISTS idx_unique_active_academic_year');
    }
};