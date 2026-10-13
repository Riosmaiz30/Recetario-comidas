<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Miga') · Miga</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="app-shell">
        <aside class="sidebar">
            <a class="brand" href="{{ route('dashboard') }}" aria-label="Miga, inicio">
                <span class="brand-mark">m</span>
                <span>Miga<small>costos de cocina</small></span>
            </a>
            <p class="nav-label">Gestión</p>
            <nav class="main-nav" aria-label="Navegación principal">
                <a href="{{ route('dashboard') }}" @class(['active' => request()->routeIs('dashboard')])><span>⌂</span>Resumen</a>
                <a href="{{ route('recipes.index') }}" @class(['active' => request()->routeIs('recipes.*')])><span>▤</span>Recetas</a>
                <a href="{{ route('ingredients.index') }}" @class(['active' => request()->routeIs('ingredients.*')])><span>◉</span>Ingredientes</a>
                <a href="{{ route('rates.index') }}" @class(['active' => request()->routeIs('rates.*')])><span>↗</span>Monedas y tasas</a>
            </nav>
            <div class="sidebar-foot">
                <span class="status-dot"></span>
                <span>Moneda base <strong>{{ $baseCurrency ?? 'VES' }}</strong></span>
            </div>
        </aside>

        <main class="main-area">
            <header class="topbar">
                <span class="topbar-kicker">RECETARIO · CONTROL DE COSTOS</span>
                <span class="topbar-date">{{ now()->locale('es')->translatedFormat('l, d F Y') }}</span>
            </header>
            <section class="page-content">
                @if (session('status'))
                    <div class="notice notice-success" role="status">{{ session('status') }}</div>
                @endif
                @if (session('error'))
                    <div class="notice notice-error" role="alert">{{ session('error') }}</div>
                @endif
                @if ($errors->any())
                    <div class="notice notice-error" role="alert">
                        <strong>Revisa los datos:</strong>
                        <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif
                @yield('content')
            </section>
            <footer class="page-footer"><span>Miga</span><span>Hecho para costear mejor.</span></footer>
        </main>
    </div>
</body>
</html>
