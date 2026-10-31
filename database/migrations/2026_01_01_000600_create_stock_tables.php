<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produits', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('categorie')->nullable();
            $table->decimal('quantite', 10, 2)->default(0);
            $table->string('unite', 20);
            $table->unsignedInteger('prix_achat');
            $table->unsignedInteger('prix_vente')->nullable();
            $table->decimal('seuil_alerte', 10, 2)->default(0);
            $table->string('fournisseur')->nullable();
            $table->date('date_entree')->nullable();
            $table->timestamps();
        });

        Schema::create('mouvements_stock', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produit_id')->constrained()->restrictOnDelete();
            $table->string('type', 20);
            $table->decimal('quantite', 10, 2);
            $table->decimal('stock_apres', 10, 2);
            $table->string('motif');
            $table->foreignId('rendez_vous_id')->nullable()->constrained('rendez_vous')->nullOnDelete();
            $table->foreignId('enregistre_par')->constrained('users')->restrictOnDelete();
            $table->timestamp('created_at')->useCurrent()->index();
        });

        Schema::create('service_produit', function (Blueprint $table) {
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->foreignId('produit_id')->constrained()->cascadeOnDelete();
            $table->decimal('quantite', 10, 2);
            $table->primary(['service_id', 'produit_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_produit');
        Schema::dropIfExists('mouvements_stock');
        Schema::dropIfExists('produits');
    }
};
