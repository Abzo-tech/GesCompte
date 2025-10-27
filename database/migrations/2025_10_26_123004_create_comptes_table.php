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
            $table->string('numero', 20)->unique();
            $table->enum('type', ['epargne', 'cheque', 'courant'])->default('courant');
            $table->enum('statut', ['actif', 'bloque', 'ferme'])->default('actif');
            $table->uuid('client_id'); // Clé étrangère UUID vers clients
            $table->timestamps();

            // Clé étrangère avec UUID
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');

            // Index optimisés
            $table->index(['numero']);
            $table->index(['type']);
            $table->index(['statut']);
            $table->index(['client_id']);
            $table->index(['type', 'statut']);
            $table->index(['created_at']);
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
