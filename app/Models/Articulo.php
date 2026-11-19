<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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

    public function scopeConStocksPorPunto(Builder $query): void
    {
        $query
            ->withSum(['stocks as stock_deposito' => fn ($stock) => $stock->where('punto', 'Deposito')], 'cantidad')
            ->withSum(['stocks as stock_comedor' => fn ($stock) => $stock->where('punto', 'Comedor')], 'cantidad')
            ->withSum(['stocks as stock_bar' => fn ($stock) => $stock->where('punto', 'Bar')], 'cantidad')
            ->withSum(['stocks as stock_recepcion' => fn ($stock) => $stock->where('punto', 'Recepción')], 'cantidad');
    }
}
