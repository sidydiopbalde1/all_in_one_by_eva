<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rendez_vous', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->restrictOnDelete();
            $table->foreignId('employe_id')->constrained()->restrictOnDelete();
            $table->dateTime('debut');
            $table->dateTime('fin');
            $table->string('zone', 20)->default('mixte');
            $table->unsignedInteger('prix_prevu');
            $table->string('statut', 20)->default('en_attente')->index();
            $table->text('commentaire')->nullable();
            $table->foreignId('cree_par')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index(['employe_id', 'debut', 'fin']);
        });

        Schema::create('rendez_vous_service', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rendez_vous_id')->constrained('rendez_vous')->cascadeOnDelete();
            $table->foreignId('service_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('forfait_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('libelle');
            $table->unsignedInteger('prix');
            $table->unsignedSmallInteger('duree_minutes');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rendez_vous_service');
        Schema::dropIfExists('rendez_vous');
    }
};
