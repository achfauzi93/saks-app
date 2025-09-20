<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('student_class_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('classroom_id')->constrained()->onDelete('cascade');
            $table->date('start_date'); // kapan mulai di kelas ini
            $table->date('end_date')->nullable(); // kapan pindah/keluar — null = masih aktif
            $table->timestamps();
        });

        // Index untuk optimasi query pencarian kelas berdasarkan tanggal
        Schema::table('student_class_assignments', function (Blueprint $table) {
            $table->index(['student_id', 'start_date']);
            $table->index('end_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_class_assignments');
    }
};