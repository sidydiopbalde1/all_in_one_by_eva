<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ParametreSalon extends Model
{
    protected $table = 'parametres_salon';

    protected $fillable = ['cle', 'valeur'];

    private const CACHE_KEY = 'parametres_salon';

    public static function get(string $cle, mixed $defaut = null): mixed
    {
        $valeurs = Cache::rememberForever(self::CACHE_KEY, fn () => self::query()->pluck('valeur', 'cle')->all());

        return $valeurs[$cle] ?? $defaut;
    }

    public static function set(string $cle, mixed $valeur): void
    {
        self::query()->updateOrCreate(['cle' => $cle], ['valeur' => $valeur]);
        Cache::forget(self::CACHE_KEY);
    }
}
