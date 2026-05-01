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
        Schema::table('users', function (Blueprint $table) {
            // Change programme_code to text so it can store JSON arrays for supervisors
            $table->text('programme_code')->nullable()->change();

            // Supervisor-specific fields
            $table->string('employee_id')->nullable()->after('matrix_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('programme_code', 100)->nullable()->change();
            $table->dropColumn('employee_id');
        });
    }
};
