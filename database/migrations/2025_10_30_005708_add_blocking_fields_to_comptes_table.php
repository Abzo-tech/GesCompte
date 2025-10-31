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
        if (Schema::hasTable('comptes')) {
            Schema::table('comptes', function (Blueprint $table) {
                $table->timestamp('date_blocage')->nullable();
                $table->timestamp('date_deblocage_prevue')->nullable();
                $table->string('motif_deblocage')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comptes', function (Blueprint $table) {
            $table->dropColumn(['date_blocage', 'date_deblocage_prevue', 'motif_deblocage']);
        });
    }
};
