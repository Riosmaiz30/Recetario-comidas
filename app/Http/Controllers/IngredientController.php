<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use App\Services\RecipeCostCalculator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class IngredientController extends Controller
{
    public function index(RecipeCostCalculator $calculator): View
    {
        $ingredients = Ingredient::where('active', true)->orderBy('name')->get();
        $unitCosts = [];

        foreach ($ingredients as $ingredient) {
            try {
                $unitCosts[$ingredient->id] = $calculator->ingredientUnitCost($ingredient);
            } catch (\RuntimeException) {
                $unitCosts[$ingredient->id] = null;
            }
        }

        return view('ingredients.index', [
            'ingredients' => $ingredients,
            'unitCosts' => $unitCosts,
            'baseCurrency' => $calculator->baseCurrency(),
            'currencies' => RecipeCostCalculator::CURRENCIES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedIngredient($request);

        Ingredient::create($validated);

        return to_route('ingredients.index')->with('status', 'Ingrediente guardado.');
    }

    public function update(Request $request, Ingredient $ingredient): RedirectResponse
    {
        $validated = $this->validatedIngredient($request, $ingredient);

        $ingredient->update($validated);

        return to_route('ingredients.index')->with('status', 'Ingrediente actualizado.');
    }

    private function validatedIngredient(Request $request, ?Ingredient $ingredient = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('ingredients', 'name')->ignore($ingredient?->id)],
            'base_unit' => ['required', 'in:g,ml,ud'],
            'package_quantity' => ['required', 'numeric', 'gt:0'],
            'package_unit' => ['required', 'in:g,kg,ml,l,ud'],
            'purchase_price' => ['required', 'numeric', 'gt:0'],
            'purchase_currency' => ['required', 'in:VES,USD,EUR'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $compatibleUnits = [
            'g' => ['g', 'kg'],
            'ml' => ['ml', 'l'],
            'ud' => ['ud'],
        ];

        if (! in_array($validated['package_unit'], $compatibleUnits[$validated['base_unit']], true)) {
            return back()->withInput()->withErrors([
                'package_unit' => 'La unidad de compra debe corresponder a la unidad base.',
            ]);
        }

        return $validated;
    }

    public function destroy(Ingredient $ingredient): RedirectResponse
    {
        if ($ingredient->recipeItems()->exists()) {
            return to_route('ingredients.index')->with('error', 'Este ingrediente está en uso en una receta.');
        }

        $ingredient->delete();

        return to_route('ingredients.index')->with('status', 'Ingrediente eliminado.');
    }
}
