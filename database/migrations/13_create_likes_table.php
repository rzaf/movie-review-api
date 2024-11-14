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
        Schema::create('likes', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_liked');
            $table->foreignId('user_id')->constrained('users');
            $table->morphs('likeable');
            // $table->foreignId('likeable_id')->constrained('movies');
            // $table->enum('likeable_type',['movies','review','review_replies']);
            $table->unique(['user_id', 'likeable_type','likeable_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('likes');
    }
};
