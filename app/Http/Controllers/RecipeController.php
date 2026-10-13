<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use App\Models\Recipe;
use App\Services\RecipeCostCalculator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use RuntimeException;

class RecipeController extends Controller
{
    public function index(RecipeCostCalculator $calculator): View
    {
        $recipes = Recipe::with('items.ingredient')->orderBy('name')->get();
        $costs = [];

        foreach ($recipes as $recipe) {
            try {
                $costs[$recipe->id] = $calculator->recipeCost($recipe);
            } catch (RuntimeException) {
                $costs[$recipe->id] = null;
            }
        }

        return view('recipes.index', [
            'recipes' => $recipes,
            'costs' => $costs,
            'ingredients' => Ingredient::where('active', true)->orderBy('name')->get(),
            'baseCurrency' => $calculator->baseCurrency(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedRecipe($request);

        DB::transaction(function () use ($validated): void {
            $recipe = Recipe::create(collect($validated)->except('items')->all());

            foreach ($validated['items'] as $position => $item) {
                $recipe->items()->create([
                    'ingredient_id' => $item['ingredient_id'],
                    'quantity' => $item['quantity'],
                    'position' => $position,
                ]);
            }
        });

        return to_route('recipes.index')->with('status', 'Receta guardada.');
    }

    public function update(Request $request, Recipe $recipe): RedirectResponse
    {
        $validated = $this->validatedRecipe($request);

        DB::transaction(function () use ($validated, $recipe): void {
            $recipe->update(collect($validated)->except('items')->all());
            $recipe->items()->delete();

            foreach ($validated['items'] as $position => $item) {
                $recipe->items()->create([
                    'ingredient_id' => $item['ingredient_id'],
                    'quantity' => $item['quantity'],
                    'position' => $position,
                ]);
            }
        });

        return to_route('recipes.index')->with('status', 'Receta actualizada.');
    }

    private function validatedRecipe(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'yield_quantity' => ['required', 'numeric', 'gt:0'],
            'yield_unit' => ['required', 'string', 'max:24'],
            'preparation_notes' => ['nullable', 'string', 'max:5000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.ingredient_id' => ['required', 'integer', 'distinct', 'exists:ingredients,id'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
        ]);
    }

    public function destroy(Recipe $recipe): RedirectResponse
    {
        $recipe->delete();

        return to_route('recipes.index')->with('status', 'Receta eliminada.');
    }
}
