<?php

namespace Tests\Feature;

use App\Models\ExchangeRate;
use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\RecipeItem;
use App\Services\RecipeCostCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecipeCostingTest extends TestCase
{
    use RefreshDatabase;

    public function test_recipe_cost_normalizes_package_units_and_converts_to_base_currency(): void
    {
        ExchangeRate::create([
            'from_currency' => 'USD',
            'to_currency' => 'VES',
            'rate' => 36.5,
            'source' => 'Test',
            'effective_at' => now()->subMinute(),
        ]);

        $flour = Ingredient::create([
            'name' => 'Harina',
            'base_unit' => 'g',
            'package_quantity' => 2,
            'package_unit' => 'kg',
            'purchase_price' => 10,
            'purchase_currency' => 'USD',
        ]);

        $water = Ingredient::create([
            'name' => 'Agua',
            'base_unit' => 'g',
            'package_quantity' => 1,
            'package_unit' => 'kg',
            'purchase_price' => 100,
            'purchase_currency' => 'VES',
        ]);

        $recipe = Recipe::create([
            'name' => 'Pan de prueba',
            'yield_quantity' => 12,
            'yield_unit' => 'unidades',
        ]);

        RecipeItem::create(['recipe_id' => $recipe->id, 'ingredient_id' => $flour->id, 'quantity' => 500]);
        RecipeItem::create(['recipe_id' => $recipe->id, 'ingredient_id' => $water->id, 'quantity' => 300]);

        $this->assertEqualsWithDelta(121.25, app(RecipeCostCalculator::class)->recipeCost($recipe), 0.001);
    }

    public function test_recipe_form_saves_weighted_ingredients_and_yield(): void
    {
        $ingredient = Ingredient::create([
            'name' => 'Harina',
            'base_unit' => 'g',
            'package_quantity' => 1,
            'package_unit' => 'kg',
            'purchase_price' => 250,
            'purchase_currency' => 'VES',
        ]);

        $response = $this->post(route('recipes.store'), [
            'name' => 'Pan campesino',
            'yield_quantity' => 8,
            'yield_unit' => 'unidades',
            'items' => [
                ['ingredient_id' => $ingredient->id, 'quantity' => 750],
            ],
        ]);

        $response->assertRedirect(route('recipes.index'));
        $this->assertDatabaseHas('recipes', ['name' => 'Pan campesino', 'yield_quantity' => 8]);
        $this->assertDatabaseHas('recipe_items', ['ingredient_id' => $ingredient->id, 'quantity' => 750]);
    }

    public function test_ingredient_price_can_be_updated_without_changing_recipe_references(): void
    {
        $ingredient = Ingredient::create([
            'name' => 'Levadura',
            'base_unit' => 'g',
            'package_quantity' => 500,
            'package_unit' => 'g',
            'purchase_price' => 120,
            'purchase_currency' => 'VES',
        ]);

        $response = $this->put(route('ingredients.update', $ingredient), [
            'name' => 'Levadura seca',
            'base_unit' => 'g',
            'package_quantity' => 250,
            'package_unit' => 'g',
            'purchase_price' => 90,
            'purchase_currency' => 'VES',
        ]);

        $response->assertRedirect(route('ingredients.index'));
        $this->assertDatabaseHas('ingredients', [
            'id' => $ingredient->id,
            'name' => 'Levadura seca',
            'purchase_price' => 90,
        ]);
    }

    public function test_recipe_can_be_updated_with_new_weights_and_yield(): void
    {
        $ingredient = Ingredient::create([
            'name' => 'Mantequilla',
            'base_unit' => 'g',
            'package_quantity' => 1,
            'package_unit' => 'kg',
            'purchase_price' => 500,
            'purchase_currency' => 'VES',
        ]);
        $recipe = Recipe::create(['name' => 'Galletas', 'yield_quantity' => 10, 'yield_unit' => 'unidades']);
        RecipeItem::create(['recipe_id' => $recipe->id, 'ingredient_id' => $ingredient->id, 'quantity' => 200]);

        $response = $this->put(route('recipes.update', $recipe), [
            'name' => 'Galletas de mantequilla',
            'yield_quantity' => 15,
            'yield_unit' => 'unidades',
            'items' => [
                ['ingredient_id' => $ingredient->id, 'quantity' => 300],
            ],
        ]);

        $response->assertRedirect(route('recipes.index'));
        $this->assertDatabaseHas('recipes', ['id' => $recipe->id, 'yield_quantity' => 15]);
        $this->assertDatabaseHas('recipe_items', ['recipe_id' => $recipe->id, 'quantity' => 300]);
        $this->assertDatabaseMissing('recipe_items', ['recipe_id' => $recipe->id, 'quantity' => 200]);
    }
}