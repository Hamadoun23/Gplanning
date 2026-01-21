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
        Schema::create('client_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->enum('report_type', ['monthly', 'annual'])->comment('Type de rapport: mensuel ou annuel');
            $table->date('report_date')->nullable()->comment('Date du rapport (période couverte)');
            $table->string('file_path');
            $table->string('original_filename');
            $table->string('file_size')->nullable();
            $table->timestamp('uploaded_at')->useCurrent();
            $table->timestamps();
            
            $table->index(['client_id', 'report_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_reports');
    }
};
