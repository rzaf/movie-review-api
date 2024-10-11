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
        Schema::create('movie_actors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('movie_id')->constrained('movies');
            $table->foreignId('person_id')->constrained('people');
            $table->enum('job',[
                'director',
                'producer',
                'writer',
                'actor',
                'music',
            ]);
            $table->unique(['movie_id', 'person_id','job']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movie_actors');
    }
};
