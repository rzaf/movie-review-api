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
        Schema::create('media_keywords', function (Blueprint $table) {
            $table->id();
            $table->foreignId('keyword_id')->constrained('keywords');
            $table->foreignId('media_id')->constrained('medias');
            $table->unique(['keyword_id', 'media_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media_keywords');
    }
};
