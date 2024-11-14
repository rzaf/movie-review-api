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
        Schema::create('media_languages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('language_id')->constrained('languages');
            $table->foreignId('media_id')->constrained('medias');
            $table->unique(['language_id', 'media_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media_languages');
    }
};
