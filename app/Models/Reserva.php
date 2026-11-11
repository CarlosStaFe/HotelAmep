<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reserva extends Model
{
    protected $fillable = [
        'habitacion_id',
        'pasajero_id',
        'fecha_inicio',
        'fecha_fin',
        'detalle',
        'estado',
    ];

    public function habitacion(): BelongsTo
    {
        return $this->belongsTo(Habitacion::class);
    }

    public function pasajero(): BelongsTo
    {
        return $this->belongsTo(Pasajero::class);
    }
}
