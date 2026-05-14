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
        // If 'milestones' table exists, rename it to 'tasks' and update type values
        if (Schema::hasTable('milestones') && ! Schema::hasTable('tasks')) {
            Schema::rename('milestones', 'tasks');
            // Update type enum values
            DB::table('tasks')->where('type', 'sv_milestone')->update(['type' => 'sv_task']);
        }

        // If neither table exists, create 'tasks' fresh
        if (! Schema::hasTable('tasks')) {
            Schema::create('tasks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
                $table->string('title');
                $table->dateTime('due_date');
                $table->string('type')->default('sv_task');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
