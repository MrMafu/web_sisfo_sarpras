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
        Schema::create('returnings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('borrow_id')->constrained('borrowings')->cascadeOnDelete();
            $table->integer('returned_quantity');
            $table->enum('status', ['pending', 'approved', 'rejected']);
            $table->foreignId('handled_by')->nullable()->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('returnings');
    }
};
