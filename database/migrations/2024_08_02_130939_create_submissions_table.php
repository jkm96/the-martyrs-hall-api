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
        Schema::create('submissions', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->string('name');
            $table->date('birth_date');
            $table->date('death_date');
            $table->string('location');
            $table->text('contributions'); // Contributions and Impact
            $table->text('death_reason'); // Circumstances of Death
            $table->string('profile_picture')->nullable(); // Store file path as a string
            $table->boolean('is_approved')->default(0);
            $table->date('approved_at')->nullable();
            $table->string('approved_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submissions');
    }
};
