<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pasajero extends Model
{
    protected $table = 'pasajeros';

    public $timestamps = false;

    protected $fillable = [
        'tipoDocumento',
        'documento',
        'apelyNombre',
        'fechaNacimiento',
        'telefono',
        'email',
        'usaComanda',
        'direccion',
        'cod_postal_id',
        'habitacion_id',
        'fecha_creacion',
        'fecha_actualizacion',
    ];

    public function habitacion(): BelongsTo
    {
        return $this->belongsTo(Habitacion::class, 'habitacion_id');
    }

    public function localidad()
    {
        return $this->belongsTo(Localidad::class, 'cod_postal_id');
    }
}
