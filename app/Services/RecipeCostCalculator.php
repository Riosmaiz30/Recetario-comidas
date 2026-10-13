<?php

namespace App\Services;

use App\Models\Ingredient;
use App\Models\Recipe;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Date;
use RuntimeException;

class RecipeCostCalculator
{
    public const CURRENCIES = ['VES', 'USD', 'EUR'];

    private const UNIT_FACTORS = [
        'g' => ['g' => 1],
        'kg' => ['g' => 1000],
        'ml' => ['ml' => 1],
        'l' => ['ml' => 1000],
        'ud' => ['ud' => 1],
    ];

    public function baseCurrency(): string
    {
        return DB::table('app_settings')->where('key', 'base_currency')->value('value') ?? 'VES';
    }

    public function ingredientUnitCost(Ingredient $ingredient): float
    {
        $baseUnit = $ingredient->base_unit;
        $packageUnit = $ingredient->package_unit;
        $unitFactor = self::UNIT_FACTORS[$packageUnit][$baseUnit] ?? null;

        if ($unitFactor === null || (float) $ingredient->package_quantity <= 0) {
            throw new RuntimeException("Unidades incompatibles para {$ingredient->name}.");
        }

        $exchangeRate = $this->exchangeRate($ingredient->purchase_currency, $this->baseCurrency());

        return ((float) $ingredient->purchase_price * $exchangeRate)
            / ((float) $ingredient->package_quantity * $unitFactor);
    }

    public function recipeCost(Recipe $recipe): float
    {
        $recipe->loadMissing('items.ingredient');

        return $recipe->items->sum(fn ($item) =>
            (float) $item->quantity * $this->ingredientUnitCost($item->ingredient)
        );
    }

    public function exchangeRate(string $from, string $to): float
    {
        if ($from === $to) {
            return 1;
        }

        $effectiveAt = Date::now();
        $directRate = DB::table('exchange_rates')
            ->where('from_currency', $from)
            ->where('to_currency', $to)
            ->where('effective_at', '<=', $effectiveAt)
            ->orderByDesc('effective_at')
            ->value('rate');

        if ($directRate !== null) {
            return (float) $directRate;
        }

        $inverseRate = DB::table('exchange_rates')
            ->where('from_currency', $to)
            ->where('to_currency', $from)
            ->where('effective_at', '<=', $effectiveAt)
            ->orderByDesc('effective_at')
            ->value('rate');

        if ($inverseRate !== null && (float) $inverseRate > 0) {
            return 1 / (float) $inverseRate;
        }

        throw new RuntimeException("Falta una tasa de cambio vigente de {$from} a {$to}.");
    }
}
