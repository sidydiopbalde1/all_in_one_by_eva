<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\RendezVous;
use App\Models\User;

class RendezVousPolicy
{
    public function view(User $user, RendezVous $rendezVous): bool
    {
        return $user->hasPermission(Permission::RendezVousGerer) || $this->estAttribueA($user, $rendezVous);
    }

    public function changerStatut(User $user, RendezVous $rendezVous): bool
    {
        return $this->view($user, $rendezVous);
    }

    private function estAttribueA(User $user, RendezVous $rendezVous): bool
    {
        return $user->employe !== null && $rendezVous->employe_id === $user->employe->id;
    }
}
