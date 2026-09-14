<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Proveedor extends Model
{
    protected $table = 'proveedores';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'razon_social',
        'cuit',
        'tipo_iva',
        'domicilio',
        'cod_postal_id',
        'telefono',
        'email',
        'activo',
        'observacion',
        'fecha_creacion',
        'fecha_actualizacion',
    ];

    public function localidad(): BelongsTo
    {
        return $this->belongsTo(Localidad::class, 'cod_postal_id');
    }

}
