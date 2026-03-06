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
            $table->string('faculty')->nullable();
            $table->string('class')->nullable();
            $table->string('programme_code')->nullable();
            $table->string('location')->nullable();
            $table->text('about')->nullable();
            $table->string('avatar')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'faculty',
                'class',
                'programme_code',
                'location',
                'about',
                'avatar'
            ]);
        });
    }
};
