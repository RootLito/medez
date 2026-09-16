<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Admin Dashboard' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-900 text-slate-100 flex min-h-screen">
    <aside class="w-64 bg-slate-800 p-4 border-r border-slate-700">
        <h2 class="text-xl font-bold mb-6 text-indigo-400">Admin Panel</h2>
        <nav class="flex flex-col gap-2">
            <a href="{{ route('admin.dashboard') }}" wire:navigate class="p-2 rounded hover:bg-slate-700">Dashboard</a>
            {{-- <a href="{{ route('admin.users.index') }}" wire:navigate class="p-2 rounded hover:bg-slate-700">Users</a> --}}
        </nav>
    </aside>

    <main class="flex-1 p-8">
        {{ $slot }}
    </main>
</body>

</html>