<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class rubro extends Model
{
    public $timestamps = false;

    public function ctrocosto(): BelongsTo
    {
        return $this->belongsTo(CtroCosto::class, 'ctrocosto_id');
    }
}
