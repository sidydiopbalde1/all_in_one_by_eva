# All in One by Eva — Application de gestion

Application web de gestion du salon de beauté **All in One by Eva** : clients, rendez-vous, prestations, caisse, fidélité, équipe et messages WhatsApp.

**Stack** : Laravel 13 · PHP 8.3+ · MySQL 8 · Blade · Tailwind CSS 4 · Chart.js · icônes Lucide.

---

## Installation

Prérequis : PHP 8.3+ (extensions `pdo_mysql`, `mbstring`, `intl`), Composer 2, Node.js 20+, MySQL 8.

```bash
# 1. Dépendances
composer install
npm install

# 2. Configuration
cp .env.example .env
php artisan key:generate
```

Créez la base, puis renseignez `DB_*` dans `.env` :

```sql
CREATE DATABASE all_in_one_eva CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

```bash
# 3. Tables + données de référence (+ démo si APP_ENV=local)
php artisan migrate --seed

# 4. Assets et serveur
npm run build          # ou « npm run dev » pendant le développement
php artisan serve      # http://localhost:8000
```

Raccourci : `composer run setup` enchaîne toutes ces étapes.

### Comptes de démonstration (`APP_ENV=local`)

| Rôle | E-mail | Mot de passe |
|---|---|---|
| Administratrice | `admin@allinone.test` | `password` |
| Réceptionniste | `accueil@allinone.test` | `password` |
| Professionnelle (Fatou) | `fatou@allinone.test` | `password` |

En production : `APP_ENV=production`, `APP_DEBUG=false`. Le seeder ne crée alors que les données de référence (rôles, catégories, modes de paiement, modèles de messages). Créez ensuite le compte de l'administratrice.

### Tests

```bash
php artisan test        # 40 tests, base SQLite en mémoire
vendor/bin/pint         # style de code Laravel
```

---

## Architecture

```
app/
├── Enums/                  StatutRendezVous, StatutClient, ZoneSalon, Role, Permission…
├── Exceptions/             RegleMetierException (règle de gestion violée → message clair)
├── Http/
│   ├── Controllers/        Contrôleurs minces : valident, autorisent, délèguent
│   ├── Middleware/         EnsureUserIsActive (déconnecte un compte désactivé)
│   └── Requests/           Form Requests (validation + normalisation des numéros)
├── Models/                 Modèles Eloquent (relations, scopes, casts)
├── Policies/               RendezVousPolicy (un·e professionnel·le ne voit que ses RDV)
├── Services/               Logique métier
│   ├── RendezVousService       création, déplacement, statuts (transaction + verrou)
│   ├── DisponibiliteService    horaires de travail + anti double réservation
│   ├── PaiementService         paiement complet/partiel, annulation tracée
│   ├── FideliteService         points idempotents, récompenses, corrections
│   ├── WhatsAppService         normalisation +221, liens wa.me
│   ├── StatistiquesService     indicateurs du tableau de bord
│   └── AuditLogger             journal des actions (historique_actions)
└── Support/                Money (FCFA), Telephone (affichage)
```

### Règles de gestion appliquées

- **Double réservation impossible** : verrou SQL sur le professionnel, puis contrôle de chevauchement (`debut < fin_demandee AND fin > debut_demande`). Les statuts « Annulé » et « Client absent » libèrent le créneau.
- **Heure de fin calculée** : somme des durées des prestations, plus la pause configurable.
- **Horaires de travail** vérifiés pour chaque professionnel·le.
- **Rendez-vous dans le passé** : réservé à la permission `rdv.passe`.
- **Paiements** : transaction atomique, le montant ne peut pas dépasser le reste à payer. Un paiement n'est jamais supprimé : il est **annulé avec un motif** et sort des recettes.
- **Fidélité** : points crédités seulement quand le rendez-vous est **terminé et soldé**, une seule fois (contrainte d'unicité). L'annulation d'un paiement retire les points par un mouvement compensatoire, sans réécrire l'historique.
- **Clients** : numéros stockés au format international (`221771234567`), ce qui permet de détecter un doublon quel que soit le format saisi. Les clients sont **archivés**, jamais supprimés.
- **WhatsApp** : lien `https://wa.me/…?text=…` encodé. L'envoi reste manuel et aucun statut « envoyé » n'est enregistré.
- **Montants** stockés en entiers (FCFA, sans décimales).

### Rôles et permissions

| Permission | Administratrice | Réceptionniste | Professionnel·le |
|---|:-:|:-:|:-:|
| Clients (créer, modifier, rechercher) | ✓ | ✓ | |
| Export CSV des clients | ✓ | | |
| Rendez-vous (créer, déplacer, statut) | ✓ | ✓ | ses RDV (statut) |
| Encaisser un paiement | ✓ | ✓ | |
| Annuler un paiement, corriger des points | ✓ | | |
| Catalogue, équipe, paramètres | ✓ | consultation catalogue | consultation catalogue |
| Chiffres financiers du tableau de bord | ✓ | | |

Chaque route est protégée par un middleware `can:` ou par une Policy. Saisir une URL interdite renvoie une erreur 403.

---

## Avancement

**Lot 1 — livré** : authentification et rôles, tableau de bord, clients (CRM), catalogue et forfaits, équipe et horaires, rendez-vous (jour / semaine / mois), caisse (paiements, reçus, impayés, annulation), fidélité (points, récompenses), WhatsApp sur la fiche client et rappel de RDV, paramètres et modèles de messages, journal d'audit, pages d'erreur en français.

La base de données couvre déjà tout le cahier des charges : les tables dépenses, stock, campagnes et anniversaires traités existent.

**Lot 2 — à venir** : écrans Dépenses et Stock, page Anniversaires (avec indicateur « traité »), Campagnes WhatsApp, rapports avec export CSV/PDF, gestion des comptes utilisateurs, sauvegardes planifiées.
