<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('prenom');
            $table->string('nom');
            $table->string('telephone', 20)->unique();
            $table->string('whatsapp', 20)->nullable();
            $table->string('email')->nullable();
            $table->date('date_naissance')->nullable();
            $table->date('date_inscription');
            $table->string('preference_communication', 20)->default('whatsapp');
            $table->boolean('consentement_promo')->default(false);
            $table->timestamp('consentement_modifie_le')->nullable();
            $table->string('preferences_prestations')->nullable();
            $table->text('notes')->nullable();
            $table->string('statut', 20)->default('actif')->index();
            $table->timestamps();

            $table->index(['nom', 'prenom']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
