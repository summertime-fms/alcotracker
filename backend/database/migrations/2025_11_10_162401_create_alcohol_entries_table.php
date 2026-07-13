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
        Schema::create('alcohol_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('alcohol_type');
            $table->integer('amount_ml');
            $table->date('drink_date');
            $table->text('comment')->nullable();
            $table->timestamps();

            // Индексы для быстрых запросов
            $table->index(['user_id', 'drink_date']);
            $table->index('drink_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alcohol_entries');
    }
};
