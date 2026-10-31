<?php

namespace App\Enums;

enum StatutRendezVous: string
{
    case EnAttente = 'en_attente';
    case Confirme = 'confirme';
    case ClientArrive = 'client_arrive';
    case EnCours = 'en_cours';
    case Termine = 'termine';
    case Annule = 'annule';
    case Absent = 'absent';

    public function label(): string
    {
        return match ($this) {
            self::EnAttente => 'En attente',
            self::Confirme => 'Confirmé',
            self::ClientArrive => 'Client arrivé',
            self::EnCours => 'En cours',
            self::Termine => 'Terminé',
            self::Annule => 'Annulé',
            self::Absent => 'Client absent',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::EnAttente => 'amber',
            self::Confirme, self::ClientArrive => 'sky',
            self::EnCours => 'violet',
            self::Termine => 'emerald',
            self::Annule, self::Absent => 'stone',
        };
    }

    /** Statuts qui libèrent le créneau du professionnel. */
    public function libereLeCreneau(): bool
    {
        return in_array($this, [self::Annule, self::Absent], true);
    }

    /** @return list<self> */
    public static function bloquants(): array
    {
        return array_values(array_filter(self::cases(), fn (self $s) => ! $s->libereLeCreneau()));
    }
}
