<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Client Portal' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-50 text-gray-900">
    <header class="bg-white border-b p-4">
        <nav class="flex gap-4">
            {{-- <a href="{{ route('client.dashboard') }}" wire:navigate class="font-bold">Dashboard</a>
            <a href="{{ route('client.profile') }}" wire:navigate>Profile</a> --}}
        </nav>
    </header>

    <main class="p-6">
        {{ $slot }}
    </main>
</body>

</html>