<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        @php
            // Get appSettings - either from View::share or directly from model
            $appSettings = $appSettings ?? null;
            if (!$appSettings) {
                try {
                    $settings = \App\Models\Setting::getSettings();
                    $appSettings = [
                        'logo_path' => $settings->logo_path ?? null,
                        'primary_color' => $settings->primary_color ?? '#2563eb',
                        'secondary_color' => $settings->secondary_color ?? '#1e293b',
                    ];
                } catch (\Exception $e) {
                    $appSettings = [
                        'logo_path' => null,
                        'primary_color' => '#2563eb',
                        'secondary_color' => '#1e293b',
                    ];
                }
            }
        @endphp

        <!-- Favicon - Use logo from settings if available -->
        @if(!empty($appSettings['logo_path']))
            @php
                $logoUrl = \Illuminate\Support\Facades\Storage::url($appSettings['logo_path']);
            @endphp
            <link rel="icon" type="image/png" href="{{ $logoUrl }}">
            <link rel="shortcut icon" type="image/png" href="{{ $logoUrl }}">
            <link rel="apple-touch-icon" href="{{ $logoUrl }}">
        @else
            <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
        @endif

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Dynamic Theme Colors -->
        <style>
            :root {
                --primary-color: {{ $appSettings['primary_color'] ?? '#2563eb' }};
                --secondary-color: {{ $appSettings['secondary_color'] ?? '#1e293b' }};
            }
        </style>

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
