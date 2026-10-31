<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CategorieService extends Model
{
    protected $table = 'categories_services';

    protected $fillable = ['nom', 'ordre'];

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }
}
