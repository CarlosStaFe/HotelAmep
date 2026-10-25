<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Calendario extends Model
{
    use HasFactory;
    protected $table = 'calendarios';

    protected $fillable = [
        'title',
        'description',
        'start',
        'end',
        'color',
        'textColor',
        'estado',
        'habitacion_id',
        'pasajero_id',
    ];

    public function habitacion()
    {
        return $this->belongsTo(Habitacion::class);
    }

    public function pasajero()
    {
        return $this->belongsTo(Pasajero::class);
    }
}
