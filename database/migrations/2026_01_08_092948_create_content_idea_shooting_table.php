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
        Schema::create('content_idea_shooting', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_idea_id')->constrained()->onDelete('cascade');
            $table->foreignId('shooting_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            // Une idée de contenu ne peut être associée qu'une fois à un tournage
            $table->unique(['content_idea_id', 'shooting_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_idea_shooting');
    }
};
