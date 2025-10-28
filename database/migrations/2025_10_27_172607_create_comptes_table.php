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
        Schema::create('comptes', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->unique();
            $table->string('type');
            $table->string('statut')->default('actif');
            $table->string('client_id')->nullable();
            $table->string('devise', 4)->default('FCFA');
            $table->dateTime('date_creation');
            $table->softDeletes();
            $table->timestamps();

            // Index pour les performances
            $table->index(['type']);
            $table->index(['statut']);
            $table->index(['client_id']);
            $table->index(['date_creation']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comptes');
    }
};
