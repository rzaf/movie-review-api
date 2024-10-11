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
        Schema::create('review_replies', function (Blueprint $table) {
            $table->id();
            $table->string('text',250);
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('review_id')->nullable()->constrained('movie_reviews');
            $table->foreignId('reply_id')->nullable()->constrained('review_replies');//if replied to a reply
            // $table->unique(['user_id','review_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('review_replies');
    }
};
