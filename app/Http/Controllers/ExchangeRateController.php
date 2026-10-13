<?php

namespace App\Http\Controllers;

use App\Models\ExchangeRate;
use App\Services\RecipeCostCalculator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ExchangeRateController extends Controller
{
    public function index(RecipeCostCalculator $calculator): View
    {
        return view('rates.index', [
            'rates' => ExchangeRate::orderByDesc('effective_at')->get(),
            'currencies' => RecipeCostCalculator::CURRENCIES,
            'baseCurrency' => $calculator->baseCurrency(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'from_currency' => ['required', 'in:VES,USD,EUR'],
            'to_currency' => ['required', 'in:VES,USD,EUR', 'different:from_currency'],
            'rate' => ['required', 'numeric', 'gt:0'],
            'source' => ['required', 'string', 'max:80'],
            'effective_at' => ['required', 'date'],
        ]);

        ExchangeRate::create($validated);

        return to_route('rates.index')->with('status', 'Tasa registrada.');
    }

    public function updateBaseCurrency(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'base_currency' => ['required', 'in:VES,USD,EUR'],
        ]);

        DB::table('app_settings')->updateOrInsert(
            ['key' => 'base_currency'],
            ['value' => $validated['base_currency']],
        );

        return to_route('rates.index')->with('status', 'Moneda base actualizada.');
    }
}
