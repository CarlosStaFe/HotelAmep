<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Articulo extends Model
{
    protected $table = 'articulos';

    public $timestamps = false;

    public function subrubro(): BelongsTo
    {
        return $this->belongsTo(Subrubro::class, 'subrubro_id');
    }

    public function primerStock(): HasOne
    {
        return $this->hasOne(Stock::class)->orderBy('id');
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }
}
