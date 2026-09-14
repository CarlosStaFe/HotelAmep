<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Localidad extends Model
{
    use HasFactory;

    protected $table = 'localidades';

    protected $fillable = ['id_prov', 'provincia', 'id_local', 'localidad', 'cod_postal'];

    public function proveedores(): HasMany
    {
        return $this->hasMany(Proveedor::class, 'cod_postal_id');
    }

}
