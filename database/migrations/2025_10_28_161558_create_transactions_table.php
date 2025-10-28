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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 50);
            $table->string('compte_id');
            $table->enum('type', ['depot', 'retrait', 'virement', 'paiement'])->default('depot');
            $table->decimal('montant', 15, 2);
            $table->text('description')->nullable();
            $table->string('beneficiaire')->nullable();
            $table->timestamp('date_transaction');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
