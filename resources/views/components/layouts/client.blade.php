<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Client Portal' }}</title>
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fluxAppearance
</head>

<body class="min-h-screen bg-white dark:bg-zinc-800 antialiased">
    @php
        $user = auth('client')->user();
        $fullName = trim(($user?->first_name ?? '') . ' ' . ($user?->last_name ?? '')) ?: 'Client';
        $firstInitial = mb_substr($user?->first_name ?? 'C', 0, 1);
        $lastInitial = mb_substr($user?->last_name ?? '', 0, 1);
        $initials = strtoupper($firstInitial . $lastInitial);
        $avatarUrl = $user?->profile_picture ? asset('storage/' . $user->profile_picture) : null;
    @endphp

    <flux:sidebar stashable sticky class="bg-zinc-50 dark:bg-zinc-900 border-r border-zinc-200 dark:border-zinc-700">
        <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

        <flux:brand href="{{ route('client.home') }}" name="Client Portal" class="px-2 font-bold text-lg">
            <img src="{{ asset('malalag.png') }}" alt="Malalag Logo" class="h-12 w-auto object-contain">
        </flux:brand>
        <flux:navlist variant="outline">
            <flux:navlist.item icon="home" href="{{ route('client.home') }}"
                :current="request()->routeIs('client.home')" wire:navigate>
                Home
            </flux:navlist.item>

            <flux:navlist.item icon="document-text" href="{{ route('client.applications.index') }}"
                :current="request()->routeIs('client.applications.*')" wire:navigate>
                Applications
            </flux:navlist.item>

            <flux:navlist.item icon="user" href="{{ route('client.profile') }}"
                :current="request()->routeIs('client.profile')" wire:navigate>
                Profile
            </flux:navlist.item>
        </flux:navlist>

        <flux:spacer />

        <flux:dropdown position="bottom" align="start">
            <flux:profile name="{{ $fullName }}" initials="{{ $initials }}" :avatar="$avatarUrl" />

            <flux:menu>
                <flux:menu.item icon="user" href="{{ route('client.profile') }}" wire:navigate>
                    My Profile
                </flux:menu.item>

                <flux:menu.separator />

                <form method="POST" action="{{ route('client.logout') }}" id="logout-form">
                    @csrf
                    <flux:menu.item icon="arrow-right-start-on-rectangle" variant="danger"
                        onclick="document.getElementById('logout-form').submit();">
                        Logout
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:sidebar>

    <!-- Mobile Header -->
    <flux:header class="lg:hidden bg-white dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-700">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-3" inset="left" />
        <flux:spacer />
        <flux:dropdown position="bottom" align="end">
            <flux:profile name="{{ $fullName }}" initials="{{ $initials }}" :avatar="$avatarUrl" />

            <flux:menu>
                <flux:menu.item icon="user" href="{{ route('client.profile') }}" wire:navigate>
                    Profile
                </flux:menu.item>

                <form method="POST" action="{{ route('client.logout') }}" id="logout-form-mobile">
                    @csrf
                    <flux:menu.item icon="arrow-right-start-on-rectangle" variant="danger"
                        onclick="document.getElementById('logout-form-mobile').submit();">
                        Logout
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:header>

    <flux:main>
        {{ $slot }}
    </flux:main>

    <flux:toast />
    @fluxScripts
</body>

</html>
