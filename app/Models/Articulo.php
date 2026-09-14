<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class articulo extends Model
{
    protected $table = 'articulos';

    public $timestamps = false;

    public function rubro(): BelongsTo
    {
        return $this->belongsTo(rubro::class, 'rubro_id');
    }
}
