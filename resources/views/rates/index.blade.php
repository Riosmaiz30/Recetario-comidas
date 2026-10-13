@extends('layouts.app')

@section('title', 'Monedas y tasas')

@section('content')
    <div class="page-heading">
        <div><p class="eyebrow">VES · USD · EUR</p><h1>Monedas y tasas</h1><p class="lede">Registra la tasa que aplicas. Cada costo se convierte a la moneda base configurada.</p></div>
    </div>

    <div class="settings-grid">
        <section class="form-panel">
            <div class="panel-heading"><div><span class="step-number">03</span><div><h2>Moneda base</h2><p>Es la moneda en que se mostrarán todos los costos.</p></div></div></div>
            <form method="POST" action="{{ route('rates.base-currency') }}" class="stack-form">
                @csrf @method('PUT')
                <label class="field">Costear en<select name="base_currency" required>@foreach ($currencies as $currency)<option value="{{ $currency }}" @selected($baseCurrency === $currency)>{{ $currency }}</option>@endforeach</select></label>
                <button class="button button-primary" type="submit">Guardar moneda base</button>
            </form>
        </section>
        <section class="form-panel">
            <div class="panel-heading"><div><span class="step-number step-yellow">04</span><div><h2>Registrar tasa</h2><p>La tasa expresa cuánto vale 1 unidad origen en la moneda destino.</p></div></div></div>
            <form method="POST" action="{{ route('rates.store') }}" class="stack-form">
                @csrf
                <div class="rate-pair"><label class="field">De<select name="from_currency">@foreach ($currencies as $currency)<option value="{{ $currency }}" @selected($currency === 'USD')>{{ $currency }}</option>@endforeach</select></label><span class="rate-arrow">→</span><label class="field">A<select name="to_currency">@foreach ($currencies as $currency)<option value="{{ $currency }}" @selected($currency === 'VES')>{{ $currency }}</option>@endforeach</select></label></div>
                <label class="field">Tasa de cambio<input name="rate" type="number" min="0.00000001" step="0.00000001" placeholder="Ej. 36.50" required></label>
                <label class="field">Fuente<input name="source" value="Manual" maxlength="80" required></label>
                <label class="field">Vigente desde<input name="effective_at" type="datetime-local" value="{{ now()->format('Y-m-d\TH:i') }}" required></label>
                <button class="button button-primary" type="submit">Guardar tasa</button>
            </form>
        </section>
    </div>

    <section class="content-section">
        <div class="section-heading"><div><p class="eyebrow">HISTORIAL</p><h2>Tasas registradas</h2></div></div>
        @if ($rates->isEmpty())
            <div class="empty-inline">No hay tasas guardadas. Si tus ingredientes están en una moneda distinta a {{ $baseCurrency }}, agrega su tasa para costearlos.</div>
        @else
            <div class="table-wrap"><table><thead><tr><th>CONVERSIÓN</th><th>TASA</th><th>FUENTE</th><th>VIGENTE DESDE</th></tr></thead><tbody>@foreach ($rates as $rate)<tr><td><strong>{{ $rate->from_currency }} <span class="muted">→</span> {{ $rate->to_currency }}</strong></td><td>{{ number_format((float) $rate->rate, 6, ',', '.') }}</td><td>{{ $rate->source }}</td><td>{{ $rate->effective_at->format('d/m/Y H:i') }}</td></tr>@endforeach</tbody></table></div>
        @endif
    </section>
@endsection
