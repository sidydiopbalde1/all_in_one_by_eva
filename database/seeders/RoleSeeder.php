<?php

namespace Database\Seeders;

use App\Enums\Permission as P;
use App\Enums\Role as R;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /** Matrice rôles × permissions (l'administratrice reçoit tout). */
    private const MATRICE = [
        'receptionniste' => [P::ClientsGerer, P::RendezVousGerer, P::PaiementsEnregistrer, P::WhatsAppEnvoyer],
        'professionnel' => [],
    ];

    public function run(): void
    {
        foreach (P::cases() as $permission) {
            Permission::updateOrCreate(['name' => $permission->value], ['label' => $permission->label()]);
        }

        foreach (R::cases() as $role) {
            $modele = Role::updateOrCreate(['name' => $role->value], ['label' => $role->label()]);
            $permissions = $role === R::Admin ? P::cases() : self::MATRICE[$role->value];

            $modele->permissions()->sync(
                Permission::query()->whereIn('name', array_map(fn (P $p) => $p->value, $permissions))->pluck('id')
            );
        }
    }
}
