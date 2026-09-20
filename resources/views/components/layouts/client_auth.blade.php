<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Auth' }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fluxAppearance
</head>

<body class="min-h-screen bg-zinc-50 dark:bg-zinc-950 flex items-center justify-center p-4">
    <div class="w-full">
        {{ $slot }}
    </div>

    @fluxScripts
</body>

</html>