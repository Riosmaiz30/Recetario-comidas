<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ingredient extends Model
{
    protected $fillable = [
        'name',
        'base_unit',
        'package_quantity',
        'package_unit',
        'purchase_price',
        'purchase_currency',
        'notes',
        'active',
    ];

    public function recipeItems(): HasMany
    {
        return $this->hasMany(RecipeItem::class);
    }
}
