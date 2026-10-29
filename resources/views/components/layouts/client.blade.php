<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Client Portal' }}</title>
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fluxAppearance
</head>

<body class="min-h-screen bg-white dark:bg-zinc-800 antialiased">
    @php
    $user = auth('client')->user();
    $clientId = auth('client')->id();
    $fullName = trim(($user?->first_name ?? '') . ' ' . ($user?->last_name ?? '')) ?: 'Client';
    $firstInitial = mb_substr($user?->first_name ?? 'C', 0, 1);
    $lastInitial = mb_substr($user?->last_name ?? '', 0, 1);
    $initials = strtoupper($firstInitial . $lastInitial);
    $avatarUrl = $user?->profile_picture ? asset('storage/' . $user->profile_picture) : null;

    // Query unread count from custom AppStatus model
    $unreadCount = $clientId
    ? \App\Models\AppStatus::whereHas('locClearance', function ($q) use ($clientId) {
    $q->where('client_id', $clientId);
    })->where('is_read', false)->count()
    : 0;
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

            <!-- Notification Menu Item with Badge -->
            <flux:navlist.item icon="bell" href="{{ route('client.notification') }}"
                :current="request()->routeIs('client.notification')" wire:navigate class="relative">
                <span>Notification</span>

                @if ($unreadCount > 0)
                <flux:badge size="sm" color="amber" class="ml-auto font-bold text-[10px] px-1.5 py-0.5">
                    {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                </flux:badge>
                @endif
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

    <!-- Mobile Header with Upper-Right Notification Bell -->
    <flux:header class="lg:hidden bg-white dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-700">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-3" inset="left" />
        <flux:spacer />

        <!-- Upper Right: Notification Bell & Profile -->
        <div class="flex items-center gap-2">
            <a href="{{ route('client.notification') }}" wire:navigate
                class="relative p-2 rounded-lg text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition"
                title="Notifications">
                <flux:icon name="bell" class="size-5" />

                @if ($unreadCount > 0)
                <span
                    class="absolute top-1 right-1 flex size-4 items-center justify-center rounded-full bg-amber-500 text-[9px] font-bold text-white shadow-xs ring-2 ring-white dark:ring-zinc-900">
                    {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                </span>
                @endif
            </a>

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
        </div>
    </flux:header>

    <flux:main>
        {{ $slot }}
    </flux:main>

    <flux:toast />
    @fluxScripts
</body>

</html>