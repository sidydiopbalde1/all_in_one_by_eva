<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modes_paiement', function (Blueprint $table) {
            $table->id();
            $table->string('libelle')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('paiements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->restrictOnDelete();
            $table->foreignId('rendez_vous_id')->nullable()->constrained('rendez_vous')->restrictOnDelete();
            $table->foreignId('mode_paiement_id')->constrained('modes_paiement')->restrictOnDelete();
            $table->unsignedInteger('montant_total');
            $table->unsignedInteger('montant_paye');
            $table->unsignedInteger('reste_a_payer');
            $table->dateTime('paye_le')->index();
            $table->string('reference')->nullable();
            $table->foreignId('enregistre_par')->constrained('users')->restrictOnDelete();
            $table->timestamp('annule_le')->nullable()->index();
            $table->foreignId('annule_par')->nullable()->constrained('users')->restrictOnDelete();
            $table->string('motif_annulation')->nullable();
            $table->timestamps();
        });

        Schema::create('categories_depenses', function (Blueprint $table) {
            $table->id();
            $table->string('nom')->unique();
            $table->timestamps();
        });

        Schema::create('depenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('categorie_depense_id')->constrained('categories_depenses')->restrictOnDelete();
            $table->unsignedInteger('montant');
            $table->date('date_depense')->index();
            $table->string('description');
            $table->string('justificatif_path')->nullable();
            $table->foreignId('enregistre_par')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('depenses');
        Schema::dropIfExists('categories_depenses');
        Schema::dropIfExists('paiements');
        Schema::dropIfExists('modes_paiement');
    }
};
