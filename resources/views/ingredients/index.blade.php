@extends('layouts.app')

@section('title', 'Ingredientes')

@section('content')
    <div class="page-heading">
        <div><p class="eyebrow">DESPENSA Y PRECIOS</p><h1>Ingredientes</h1><p class="lede">Los precios se convierten a {{ $baseCurrency }} y el empaque se normaliza a gramos, mililitros o unidades.</p></div>
        <a class="button button-primary" href="#new-ingredient"><span>＋</span> Agregar ingrediente</a>
    </div>

    <section class="form-panel" id="new-ingredient">
        <div class="panel-heading"><div><span class="step-number">01</span><div><h2>Nuevo ingrediente</h2><p>Indica cómo lo compras; el sistema lo convierte a su unidad de receta.</p></div></div></div>
        <form method="POST" action="{{ route('ingredients.store') }}" class="form-grid">
            @csrf
            <label class="field field-wide">Nombre del ingrediente<input name="name" value="{{ old('name') }}" placeholder="Ej. Harina de trigo todo uso" required></label>
            <label class="field">Unidad para recetas<select name="base_unit" required><option value="g" @selected(old('base_unit', 'g') === 'g')>Gramos (g)</option><option value="ml" @selected(old('base_unit') === 'ml')>Mililitros (ml)</option><option value="ud" @selected(old('base_unit') === 'ud')>Unidad (ud)</option></select></label>
            <label class="field">Cantidad del empaque<input type="number" name="package_quantity" min="0.0001" step="0.0001" value="{{ old('package_quantity', '1') }}" required></label>
            <label class="field">Se compra en<select name="package_unit" required><option value="kg" @selected(old('package_unit', 'kg') === 'kg')>Kilogramos (kg)</option><option value="g" @selected(old('package_unit') === 'g')>Gramos (g)</option><option value="l" @selected(old('package_unit') === 'l')>Litros (l)</option><option value="ml" @selected(old('package_unit') === 'ml')>Mililitros (ml)</option><option value="ud" @selected(old('package_unit') === 'ud')>Unidades (ud)</option></select></label>
            <label class="field">Precio pagado<input type="number" name="purchase_price" min="0.0001" step="0.0001" value="{{ old('purchase_price') }}" placeholder="0,00" required></label>
            <label class="field">Moneda<select name="purchase_currency" required>@foreach ($currencies as $currency)<option value="{{ $currency }}" @selected(old('purchase_currency', $baseCurrency) === $currency)>{{ $currency }}</option>@endforeach</select></label>
            <label class="field field-wide">Nota <span class="optional">opcional</span><input name="notes" value="{{ old('notes') }}" placeholder="Marca, proveedor o presentación"></label>
            <div class="form-actions field-wide"><span class="form-hint">Ejemplo: bolsa de 5 kg de harina, base en gramos.</span><button class="button button-primary" type="submit">Guardar ingrediente</button></div>
        </form>
    </section>

    <section class="content-section">
        <div class="section-heading"><div><p class="eyebrow">CATÁLOGO ACTUAL</p><h2>{{ $ingredients->count() }} ingredientes</h2></div></div>
        @if ($ingredients->isEmpty())
            <div class="empty-inline">Todavía no hay ingredientes. Agrega el primero arriba.</div>
        @else
            <div class="table-wrap"><table><thead><tr><th>INGREDIENTE</th><th>EMPAQUE</th><th>PRECIO DE COMPRA</th><th>COSTO POR UNIDAD BASE</th><th></th></tr></thead><tbody>
                @foreach ($ingredients as $ingredient)
                    <tr>
                        <td><strong>{{ $ingredient->name }}</strong>@if ($ingredient->notes)<small>{{ $ingredient->notes }}</small>@endif</td>
                        <td>{{ number_format((float) $ingredient->package_quantity, 2, ',', '.') }} {{ $ingredient->package_unit }}</td>
                        <td>{{ $ingredient->purchase_currency }} {{ number_format((float) $ingredient->purchase_price, 2, ',', '.') }}</td>
                        <td class="cost-highlight">@if ($unitCosts[$ingredient->id] !== null){{ $baseCurrency }} {{ number_format($unitCosts[$ingredient->id], 6, ',', '.') }} / {{ $ingredient->base_unit }}@else<span class="muted">Falta configurar tasa</span>@endif</td>
                        <td class="actions-cell">
                            <details class="edit-details"><summary class="row-link">Editar</summary>
                                <form method="POST" action="{{ route('ingredients.update', $ingredient) }}" class="inline-edit-form">
                                    @csrf @method('PUT')
                                    <label class="field">Nombre<input name="name" value="{{ $ingredient->name }}" required></label>
                                    <label class="field">Unidad base<select name="base_unit"><option value="g" @selected($ingredient->base_unit === 'g')>g</option><option value="ml" @selected($ingredient->base_unit === 'ml')>ml</option><option value="ud" @selected($ingredient->base_unit === 'ud')>ud</option></select></label>
                                    <label class="field">Cantidad del paquete<input type="number" name="package_quantity" min="0.0001" step="0.0001" value="{{ $ingredient->package_quantity }}" required></label>
                                    <label class="field">Unidad de compra<select name="package_unit"><option value="kg" @selected($ingredient->package_unit === 'kg')>kg</option><option value="g" @selected($ingredient->package_unit === 'g')>g</option><option value="l" @selected($ingredient->package_unit === 'l')>l</option><option value="ml" @selected($ingredient->package_unit === 'ml')>ml</option><option value="ud" @selected($ingredient->package_unit === 'ud')>ud</option></select></label>
                                    <label class="field">Precio<input type="number" name="purchase_price" min="0.0001" step="0.0001" value="{{ $ingredient->purchase_price }}" required></label>
                                    <label class="field">Moneda<select name="purchase_currency">@foreach ($currencies as $currency)<option value="{{ $currency }}" @selected($ingredient->purchase_currency === $currency)>{{ $currency }}</option>@endforeach</select></label>
                                    <label class="field field-wide">Nota<input name="notes" value="{{ $ingredient->notes }}"></label>
                                    <button class="button button-primary button-small" type="submit">Actualizar precio</button>
                                </form>
                            </details>
                            <form method="POST" action="{{ route('ingredients.destroy', $ingredient) }}" onsubmit="return confirm('¿Eliminar este ingrediente?')">@csrf @method('DELETE')<button class="icon-button" type="submit" title="Eliminar ingrediente" aria-label="Eliminar {{ $ingredient->name }}">×</button></form>
                        </td>
                    </tr>
                @endforeach
            </tbody></table></div>
        @endif
    </section>
@endsection
