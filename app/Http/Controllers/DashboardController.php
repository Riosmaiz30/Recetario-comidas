<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use App\Models\Recipe;
use App\Services\RecipeCostCalculator;
use Illuminate\View\View;
use RuntimeException;

class DashboardController extends Controller
{
    public function __invoke(RecipeCostCalculator $calculator): View
    {
        $recipes = Recipe::with('items.ingredient')->latest()->take(5)->get();
        $costs = [];

        foreach ($recipes as $recipe) {
            try {
                $costs[$recipe->id] = $calculator->recipeCost($recipe);
            } catch (RuntimeException) {
                $costs[$recipe->id] = null;
            }
        }

        return view('dashboard', [
            'ingredientCount' => Ingredient::where('active', true)->count(),
            'recipeCount' => Recipe::count(),
            'recipes' => $recipes,
            'costs' => $costs,
            'baseCurrency' => $calculator->baseCurrency(),
        ]);
    }
}
