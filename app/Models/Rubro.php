<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rubro extends Model
{
    protected $table = 'rubros';

    public $timestamps = false;

    public function subrubros(): HasMany
    {
        return $this->hasMany(Subrubro::class, 'rubro_id');
    }
}
