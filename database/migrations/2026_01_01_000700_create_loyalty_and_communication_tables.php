<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mouvements_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->restrictOnDelete();
            $table->foreignId('paiement_id')->nullable()->unique()->constrained()->restrictOnDelete();
            $table->string('type', 20);
            $table->integer('points');
            $table->string('motif');
            $table->foreignId('enregistre_par')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('recompenses', function (Blueprint $table) {
            $table->id();
            $table->string('libelle');
            $table->string('type', 30);
            $table->unsignedInteger('seuil_points');
            $table->unsignedInteger('valeur')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('utilisations_recompenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->restrictOnDelete();
            $table->foreignId('recompense_id')->constrained('recompenses')->restrictOnDelete();
            $table->foreignId('mouvement_points_id')->constrained('mouvements_points')->restrictOnDelete();
            $table->foreignId('enregistre_par')->constrained('users')->restrictOnDelete();
            $table->timestamp('utilisee_le')->useCurrent();
        });

        Schema::create('modeles_messages', function (Blueprint $table) {
            $table->id();
            $table->string('type', 30)->unique();
            $table->string('titre');
            $table->text('contenu');
            $table->timestamps();
        });

        Schema::create('campagnes_messages', function (Blueprint $table) {
            $table->id();
            $table->string('titre');
            $table->foreignId('modele_message_id')->nullable()->constrained('modeles_messages')->nullOnDelete();
            $table->text('message');
            $table->string('statut_suivi')->default('preparee');
            $table->foreignId('cree_par')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });

        Schema::create('destinataires_campagnes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campagne_message_id')->constrained('campagnes_messages')->cascadeOnDelete();
            $table->foreignId('client_id')->constrained()->restrictOnDelete();
            $table->text('message_personnalise');
            $table->timestamp('ouvert_le')->nullable();
            $table->string('suivi_manuel')->nullable();

            $table->unique(['campagne_message_id', 'client_id']);
        });

        Schema::create('anniversaires_traites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('annee');
            $table->string('offre')->nullable();
            $table->foreignId('traite_par')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->unique(['client_id', 'annee']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anniversaires_traites');
        Schema::dropIfExists('destinataires_campagnes');
        Schema::dropIfExists('campagnes_messages');
        Schema::dropIfExists('modeles_messages');
        Schema::dropIfExists('utilisations_recompenses');
        Schema::dropIfExists('recompenses');
        Schema::dropIfExists('mouvements_points');
    }
};
