<?php

namespace App\Enums;

/**
 * Permissions fines, attribuées aux rôles par le RoleSeeder.
 * L'administratrice les possède toutes (voir Gate::before dans AppServiceProvider).
 */
enum Permission: string
{
    case ClientsGerer = 'clients.gerer';
    case ClientsExporter = 'clients.exporter';
    case CatalogueGerer = 'catalogue.gerer';
    case EmployesGerer = 'employes.gerer';
    case RendezVousGerer = 'rdv.gerer';
    case RendezVousPasse = 'rdv.passe';
    case PaiementsEnregistrer = 'paiements.enregistrer';
    case PaiementsAnnuler = 'paiements.annuler';
    case FideliteCorriger = 'fidelite.corriger';
    case WhatsAppEnvoyer = 'whatsapp.envoyer';
    case FinancesVoir = 'finances.voir';
    case ParametresGerer = 'parametres.gerer';

    public function label(): string
    {
        return match ($this) {
            self::ClientsGerer => 'Gérer les clients',
            self::ClientsExporter => 'Exporter les clients',
            self::CatalogueGerer => 'Gérer le catalogue',
            self::EmployesGerer => 'Gérer les employés',
            self::RendezVousGerer => 'Gérer les rendez-vous',
            self::RendezVousPasse => 'Créer un rendez-vous dans le passé',
            self::PaiementsEnregistrer => 'Enregistrer des paiements',
            self::PaiementsAnnuler => 'Annuler / corriger des paiements',
            self::FideliteCorriger => 'Corriger les points de fidélité',
            self::WhatsAppEnvoyer => 'Envoyer des messages WhatsApp',
            self::FinancesVoir => 'Voir les finances et rapports',
            self::ParametresGerer => 'Configurer le salon',
        };
    }
}
