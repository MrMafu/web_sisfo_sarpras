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
        Schema::table('item_units', function (Blueprint $table) {
            $table->enum('status', ['available', 'borrowed'])->default('available')->change();
        });

        Schema::table('borrowings', function (Blueprint $table) {
            $table->enum('status', ['pending', 'approved', 'rejected', 'returned'])->default('pending')->change();
        });

        Schema::table('returnings', function (Blueprint $table) {
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
