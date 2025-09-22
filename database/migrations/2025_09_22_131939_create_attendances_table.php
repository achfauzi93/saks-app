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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('classroom_id')->constrained()->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained()->onDelete('cascade'); // Untuk filter cepat
            $table->foreignId('homeroom_teacher_id')->constrained('users')->onDelete('cascade'); // Untuk audit/filter
            $table->date('date');
            $table->enum('status', ['present', 'sick', 'leave', 'absent'])->default('present');
            $table->boolean('is_late')->default(false); // Untuk mencatat keterlambatan
            $table->time('late_time')->nullable(); // Jam keterlambatan (opsional)
            $table->text('notes')->nullable(); // Keterangan tambahan
            $table->foreignId('recorded_by')->constrained('users')->onDelete('cascade'); // Siapa yang mencatat
            $table->timestamps();

            // Indeks untuk performa query
            $table->unique(['student_id', 'classroom_id', 'date']); // Mencegah duplikat
            $table->index(['classroom_id', 'date']);
            $table->index(['academic_year_id', 'date']);
            $table->index(['homeroom_teacher_id', 'date']);
            $table->index('date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};