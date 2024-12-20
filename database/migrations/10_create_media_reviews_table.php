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
        Schema::create('media_reviews', function (Blueprint $table) {
            $table->id();
            $table->string('review',500)->nullable();
            $table->tinyInteger('score')->comment('score 0.0 to 10.0 stored 0 to 100');
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('media_id')->constrained('medias');
            $table->unique(['user_id','media_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media_reviews');
    }
};
