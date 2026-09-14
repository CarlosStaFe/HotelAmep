<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Habitacion extends Model
{
    protected $table = 'habitaciones';

    public $timestamps = false;

    protected $fillable = [
        'numero',
        'tipo',
        'estado',
        'observacion',
        'fecha_creacion',
        'fecha_actualizacion',
    ];

    public function pasajeros(): HasMany
    {
        return $this->hasMany(Pasajero::class, 'habitacion_id');
    }
}
