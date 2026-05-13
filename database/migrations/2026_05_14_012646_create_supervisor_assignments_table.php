<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supervisor_assignments', function (Blueprint $table) {
            $table->id();
            $table->string('student_matrix_id')->unique();
            $table->string('student_name');
            $table->string('supervisor_matrix_id');
            $table->string('faculty')->nullable();
            $table->string('programme_code')->nullable();
            $table->string('class')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supervisor_assignments');
    }
};
