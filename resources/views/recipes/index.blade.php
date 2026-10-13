@extends('layouts.app')

@section('title', 'Recetas')

@section('content')
    <div class="page-heading">
        <div><p class="eyebrow">FÓRMULAS Y RENDIMIENTO</p><h1>Recetas</h1><p class="lede">Usa tus pesos reales. Miga calcula el costo del lote y de cada unidad producida.</p></div>
        <a class="button button-primary" href="#new-recipe"><span>＋</span> Crear receta</a>
    </div>

    <section class="form-panel recipe-panel" id="new-recipe">
        <div class="panel-heading"><div><span class="step-number">02</span><div><h2>Nueva receta</h2><p>Define el rendimiento vendible y agrega cada ingrediente con su peso.</p></div></div></div>
        @if ($ingredients->isEmpty())
            <div class="empty-inline">Primero <a class="text-link" href="{{ route('ingredients.index') }}">registra al menos un ingrediente</a> con precio de compra.</div>
        @else
            <form method="POST" action="{{ route('recipes.store') }}" class="recipe-form" data-recipe-form>
                @csrf
                <div class="form-grid">
                    <label class="field field-wide">Nombre de la receta<input name="name" value="{{ old('name') }}" placeholder="Ej. Pan campesino" required></label>
                    <label class="field">Rendimiento del lote<input type="number" name="yield_quantity" min="0.0001" step="0.0001" value="{{ old('yield_quantity', '1') }}" required></label>
                    <label class="field">Se obtiene<select name="yield_unit"><option value="unidades">Unidades</option><option value="porciones">Porciones</option><option value="kg">Kilogramos</option><option value="tortas">Tortas</option><option value="docenas">Docenas</option></select></label>
                    <label class="field field-wide">Descripción <span class="optional">opcional</span><input name="description" value="{{ old('description') }}" placeholder="Tipo, tamaño o presentación"></label>
                </div>
                <div class="ingredient-lines-heading"><div><h3>Ingredientes y cantidades</h3><span>Usa gramos, mililitros o unidades según cada ingrediente.</span></div><button class="button button-secondary button-small" type="button" data-add-line><span>＋</span> Añadir línea</button></div>
                <div class="recipe-lines" data-recipe-lines>
                    <div class="recipe-line" data-recipe-line>
                        <label class="field">Ingrediente<select name="items[0][ingredient_id]" required><option value="">Seleccionar ingrediente</option>@foreach ($ingredients as $ingredient)<option value="{{ $ingredient->id }}">{{ $ingredient->name }} · {{ $ingredient->base_unit }}</option>@endforeach</select></label>
                        <label class="field">Cantidad (<span data-line-unit>unidad base</span>)<input type="number" name="items[0][quantity]" min="0.0001" step="0.0001" placeholder="0" required></label>
                        <button class="icon-button line-remove" type="button" data-remove-line title="Quitar línea" aria-label="Quitar ingrediente">×</button>
                    </div>
                </div>
                <label class="field notes-field">Preparación <span class="optional">opcional</span><textarea name="preparation_notes" rows="3" placeholder="Pasos, temperatura de horno o tiempos"></textarea></label>
                <div class="form-actions"><span class="form-hint">El costo se calculará con los precios y tasas vigentes.</span><button class="button button-primary" type="submit">Guardar receta y calcular</button></div>
            </form>
            <template id="ingredient-options">@foreach ($ingredients as $ingredient)<option value="{{ $ingredient->id }}" data-unit="{{ $ingredient->base_unit }}">{{ $ingredient->name }} · {{ $ingredient->base_unit }}</option>@endforeach</template>
        @endif
    </section>

    <section class="content-section">
        <div class="section-heading"><div><p class="eyebrow">COSTEO ACTUAL</p><h2>{{ $recipes->count() }} recetas</h2></div></div>
        @if ($recipes->isEmpty())
            <div class="empty-inline">Tu lista de recetas está vacía.</div>
        @else
            <div class="recipe-grid">
                @foreach ($recipes as $recipe)
                    <article class="recipe-card">
                        <div class="recipe-card-top"><span class="recipe-tag">RECETA</span><form method="POST" action="{{ route('recipes.destroy', $recipe) }}" onsubmit="return confirm('¿Eliminar esta receta?')">@csrf @method('DELETE')<button class="icon-button" type="submit" title="Eliminar receta" aria-label="Eliminar {{ $recipe->name }}">×</button></form></div>
                        <h3>{{ $recipe->name }}</h3>
                        @if ($recipe->description)<p class="recipe-description">{{ $recipe->description }}</p>@endif
                        <div class="recipe-yield">Rinde <strong>{{ number_format((float) $recipe->yield_quantity, 2, ',', '.') }} {{ $recipe->yield_unit }}</strong> · {{ $recipe->items->count() }} ingredientes</div>
                        <div class="recipe-cost-row">
                            @if ($costs[$recipe->id] !== null)
                                <div><span>COSTO DEL LOTE</span><strong>{{ $baseCurrency }} {{ number_format($costs[$recipe->id], 2, ',', '.') }}</strong></div>
                                <div><span>POR {{ ['unidades' => 'UNIDAD', 'porciones' => 'PORCIÓN', 'tortas' => 'TORTA', 'docenas' => 'DOCENA', 'kg' => 'KG'][$recipe->yield_unit] ?? strtoupper($recipe->yield_unit) }}</span><strong>{{ $baseCurrency }} {{ number_format($costs[$recipe->id] / $recipe->yield_quantity, 2, ',', '.') }}</strong></div>
                            @else
                                <div><span>COSTO</span><strong class="muted">Falta configurar una tasa</strong></div>
                            @endif
                        </div>
                        <details class="recipe-details"><summary>Ver ingredientes</summary><ul>@foreach ($recipe->items as $item)<li><span>{{ $item->ingredient->name }}</span><strong>{{ number_format((float) $item->quantity, 2, ',', '.') }} {{ $item->ingredient->base_unit }}</strong></li>@endforeach</ul></details>
                        <details class="recipe-details edit-recipe-details">
                            <summary>Editar receta</summary>
                            <form method="POST" action="{{ route('recipes.update', $recipe) }}" class="inline-recipe-form" data-recipe-form>
                                @csrf @method('PUT')
                                <label class="field">Nombre<input name="name" value="{{ $recipe->name }}" required></label>
                                <div class="edit-yield-grid">
                                    <label class="field">Rendimiento<input type="number" name="yield_quantity" min="0.0001" step="0.0001" value="{{ $recipe->yield_quantity }}" required></label>
                                    <label class="field">Unidad<select name="yield_unit">@foreach (['unidades', 'porciones', 'tortas', 'docenas', 'kg'] as $unit)<option value="{{ $unit }}" @selected($recipe->yield_unit === $unit)>{{ ucfirst($unit) }}</option>@endforeach</select></label>
                                </div>
                                <label class="field">Descripción<input name="description" value="{{ $recipe->description }}"></label>
                                <div class="ingredient-lines-heading"><div><h3>Ingredientes</h3></div><button class="button button-secondary button-small" type="button" data-add-line><span>＋</span> Añadir</button></div>
                                <div class="recipe-lines" data-recipe-lines>
                                    @foreach ($recipe->items as $item)
                                        <div class="recipe-line" data-recipe-line>
                                            <label class="field">Ingrediente<select name="items[{{ $loop->index }}][ingredient_id]" required><option value="">Seleccionar ingrediente</option>@foreach ($ingredients as $ingredient)<option value="{{ $ingredient->id }}" data-unit="{{ $ingredient->base_unit }}" @selected($ingredient->id === $item->ingredient_id)>{{ $ingredient->name }} · {{ $ingredient->base_unit }}</option>@endforeach</select></label>
                                            <label class="field">Cantidad (<span data-line-unit>{{ $item->ingredient->base_unit }}</span>)<input type="number" name="items[{{ $loop->index }}][quantity]" min="0.0001" step="0.0001" value="{{ $item->quantity }}" required></label>
                                            <button class="icon-button line-remove" type="button" data-remove-line title="Quitar línea" aria-label="Quitar ingrediente">×</button>
                                        </div>
                                    @endforeach
                                </div>
                                <label class="field">Preparación<textarea name="preparation_notes" rows="2">{{ $recipe->preparation_notes }}</textarea></label>
                                <button class="button button-primary button-small" type="submit">Guardar cambios</button>
                            </form>
                        </details>
                    </article>
                @endforeach
            </div>
        @endif
    </section>
@endsection
