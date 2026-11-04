<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'KPLC Meter Lab'))</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">


    <link rel="stylesheet" href="{{ asset('css/layout.css') }}">


    @stack('styles')
</head>

<body>
<div id="app">
    @include('layouts.partials.navbar')

    <div class="mobile-sidebar-backdrop" id="mobileSidebarBackdrop"></div>

    <div class="container-fluid px-0">
        <div class="row g-0">
            @include('layouts.partials.sidebar')

            <div id="appMainContent"
                 class="col-md-10 main-content"
                 style="{{ auth()->check() ? '' : 'margin-left: auto; margin-right: auto; max-width: 1200px;' }}">

                @include('layouts.partials.alerts')

                @yield('content')
            </div>
        </div>
    </div>
</div>

@include('layouts.partials.scripts')

@stack('scripts')
</body>
</html>
