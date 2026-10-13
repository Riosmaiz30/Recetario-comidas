<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Recipe extends Model
{
    protected $fillable = [
        'name',
        'description',
        'yield_quantity',
        'yield_unit',
        'preparation_notes',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(RecipeItem::class)->orderBy('position');
    }
}
