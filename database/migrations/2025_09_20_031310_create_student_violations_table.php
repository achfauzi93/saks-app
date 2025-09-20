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
        Schema::create('student_violations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('violation_type_id')->constrained()->onDelete('cascade');
            $table->foreignId('classroom_id')->constrained()->onDelete('cascade'); // kelas saat kejadian
            $table->foreignId('homeroom_teacher_id')->constrained('users')->onDelete('set null')->nullable(); // walas saat itu
            $table->date('violation_date'); // tanggal kejadian
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Optional: Add indexes for better performance on foreign keys
        Schema::table('student_violations', function (Blueprint $table) {
            $table->index('violation_date');
            $table->index('classroom_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_violations');
    }
};