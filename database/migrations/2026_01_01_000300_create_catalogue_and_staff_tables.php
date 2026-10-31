<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories_services', function (Blueprint $table) {
            $table->id();
            $table->string('nom')->unique();
            $table->unsignedSmallInteger('ordre')->default(0);
            $table->timestamps();
        });

        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('categorie_service_id')->constrained('categories_services')->restrictOnDelete();
            $table->string('nom');
            $table->text('description')->nullable();
            $table->unsignedInteger('prix');
            $table->unsignedSmallInteger('duree_minutes');
            $table->string('zone', 20)->default('mixte');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('forfaits', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->text('description')->nullable();
            $table->unsignedInteger('prix');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('forfait_service', function (Blueprint $table) {
            $table->foreignId('forfait_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->restrictOnDelete();
            $table->primary(['forfait_id', 'service_id']);
        });

        Schema::create('employes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->string('prenom');
            $table->string('nom');
            $table->string('telephone', 20);
            $table->string('fonction');
            $table->string('specialites')->nullable();
            $table->decimal('taux_commission', 5, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('employe_service', function (Blueprint $table) {
            $table->foreignId('employe_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->primary(['employe_id', 'service_id']);
        });

        Schema::create('disponibilites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employe_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('jour_semaine'); // 1 = lundi … 7 = dimanche (ISO-8601)
            $table->time('heure_debut');
            $table->time('heure_fin');
            $table->timestamps();

            $table->unique(['employe_id', 'jour_semaine']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disponibilites');
        Schema::dropIfExists('employe_service');
        Schema::dropIfExists('employes');
        Schema::dropIfExists('forfait_service');
        Schema::dropIfExists('forfaits');
        Schema::dropIfExists('services');
        Schema::dropIfExists('categories_services');
    }
};
