@extends('layouts.app')

@section('title', 'Resumen')

@section('content')
    <div class="page-heading">
        <div>
            <p class="eyebrow">TU PRODUCCIÓN, EN NÚMEROS</p>
            <h1>Resumen</h1>
            <p class="lede">Costos claros para decidir qué preparar y a qué precio.</p>
        </div>
        <a class="button button-primary" href="{{ route('recipes.index') }}#new-recipe"><span>＋</span> Nueva receta</a>
    </div>

    <div class="metric-grid">
        <article class="metric metric-green">
            <span class="metric-label">RECETAS REGISTRADAS</span>
            <strong>{{ $recipeCount }}</strong>
            <span class="metric-note">Preparaciones y productos</span>
            <span class="metric-icon">▤</span>
        </article>
        <article class="metric metric-cream">
            <span class="metric-label">INGREDIENTES ACTIVOS</span>
            <strong>{{ $ingredientCount }}</strong>
            <span class="metric-note">Con precio de compra guardado</span>
            <span class="metric-icon">◉</span>
        </article>
        <article class="metric metric-red">
            <span class="metric-label">MONEDA DE COSTEO</span>
            <strong class="metric-currency">{{ $baseCurrency }}</strong>
            <span class="metric-note">Puedes cambiarla en monedas y tasas</span>
            <span class="metric-icon">↗</span>
        </article>
    </div>

    <section class="content-section">
        <div class="section-heading">
            <div><p class="eyebrow">ÚLTIMAS PREPARACIONES</p><h2>Costos por receta</h2></div>
            <a class="text-link" href="{{ route('recipes.index') }}">Ver todas <span>→</span></a>
        </div>
        @if ($recipes->isEmpty())
            <div class="empty-state">
                <span class="empty-icon">✳</span>
                <h3>Tu recetario empieza aquí</h3>
                <p>Registra ingredientes y sus precios; luego arma recetas con los pesos que ya utilizas.</p>
                <a class="button button-primary" href="{{ route('ingredients.index') }}">Agregar primer ingrediente</a>
            </div>
        @else
            <div class="table-wrap">
                <table>
                    <thead><tr><th>RECETA</th><th>RENDIMIENTO</th><th>COSTO DEL LOTE</th><th>COSTO POR UNIDAD</th><th></th></tr></thead>
                    <tbody>
                    @foreach ($recipes as $recipe)
                        <tr>
                            <td><strong>{{ $recipe->name }}</strong><small>{{ $recipe->items->count() }} ingredientes</small></td>
                            <td>{{ number_format((float) $recipe->yield_quantity, 2, ',', '.') }} {{ $recipe->yield_unit }}</td>
                            @if ($costs[$recipe->id] !== null)
                                <td>{{ $baseCurrency }} {{ number_format($costs[$recipe->id], 2, ',', '.') }}</td>
                                <td class="cost-highlight">{{ $baseCurrency }} {{ number_format($costs[$recipe->id] / $recipe->yield_quantity, 2, ',', '.') }}</td>
                            @else
                                <td colspan="2"><span class="muted">Configura una tasa de cambio para ver el costo.</span></td>
                            @endif
                            <td><a class="row-link" href="{{ route('recipes.index') }}">Abrir →</a></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection
