<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'MPDC Admin Portal' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fluxAppearance
</head>

<body class="min-h-screen bg-white dark:bg-zinc-800 antialiased">
    @php
    $admin = auth('admin')->user();
    $fullName = $admin?->full_name ?? 'MPDC Staff';
    $position = $admin?->position ?? 'Staff Member';

    $nameParts = explode(' ', trim($fullName));
    $firstInitial = mb_substr($nameParts[0] ?? 'A', 0, 1);
    $lastInitial = count($nameParts) > 1 ? mb_substr(end($nameParts), 0, 1) : '';
    $initials = strtoupper($firstInitial . $lastInitial);
    @endphp

    <flux:sidebar sticky collapsible="mobile" class="bg-zinc-50 dark:bg-zinc-900 border-r border-zinc-200 dark:border-zinc-700">
        <flux:sidebar.header>
            <flux:sidebar.brand
                href="{{ route('admin.dashboard') }}"
                logo="https://fluxui.dev/img/demo/logo.png"
                name="MPDC Portal"
                class="px-2 font-bold"
            />
            <flux:sidebar.collapse class="lg:hidden" />
        </flux:sidebar.header>

        <flux:sidebar.nav>
            {{-- Dashboard --}}
            <flux:sidebar.item icon="squares-2x2" href="{{ route('admin.dashboard') }}" :current="request()->routeIs('admin.dashboard')" wire:navigate>
                Dashboard
            </flux:sidebar.item>

            {{-- Applications --}}
            @if($admin?->hasPermission('applications.view'))
            <flux:sidebar.group expandable icon="document-duplicate" heading="Applications" class="grid" :expanded="request()->routeIs('admin.applications.*')">
                <flux:sidebar.item href="{{ route('admin.applications.index') }}" :current="request()->routeIs('admin.applications.index')" wire:navigate>
                    Digital Logbook
                </flux:sidebar.item>

                @if($admin?->hasPermission('applications.encode_walkin'))
                <flux:sidebar.item href="{{ route('admin.applications.create-walkin') }}" :current="request()->routeIs('admin.applications.create-walkin')" wire:navigate>
                    Encode Walk-In
                </flux:sidebar.item>
                @endif

                <flux:sidebar.item href="{{ route('admin.applications.pending') }}" :current="request()->routeIs('admin.applications.pending')" wire:navigate>
                    Pending Verification
                </flux:sidebar.item>

                <flux:sidebar.item href="{{ route('admin.applications.archived') }}" :current="request()->routeIs('admin.applications.archived')" wire:navigate>
                    Archived Records
                </flux:sidebar.item>
            </flux:sidebar.group>
            @endif

            {{-- Geospatial & Inspection --}}
            @if($admin?->hasPermission('geospatial.view_map') || $admin?->hasPermission('inspections.schedule'))
            <flux:sidebar.group expandable icon="map-pin" heading="Geospatial Mapping" class="grid" :expanded="request()->routeIs('admin.geospatial.*') || request()->routeIs('admin.inspections.*')">
                @if($admin?->hasPermission('geospatial.view_map'))
                <flux:sidebar.item href="{{ route('admin.geospatial.index') }}" :current="request()->routeIs('admin.geospatial.*')" wire:navigate>
                    Interactive Zone Map
                </flux:sidebar.item>
                @endif

                @if($admin?->hasPermission('inspections.schedule'))
                <flux:sidebar.item href="{{ route('admin.inspections.index') }}" :current="request()->routeIs('admin.inspections.index')" wire:navigate>
                    Inspection Schedule
                </flux:sidebar.item>

                <flux:sidebar.item href="{{ route('admin.inspections.logs') }}" :current="request()->routeIs('admin.inspections.logs')" wire:navigate>
                    Inspection Logs
                </flux:sidebar.item>
                @endif
            </flux:sidebar.group>
            @endif

            {{-- Reports --}}
            @if($admin?->hasPermission('reports.view'))
            <flux:sidebar.group expandable icon="chart-bar" heading="Reports" class="grid" :expanded="request()->routeIs('admin.reports.*')">
                <flux:sidebar.item href="{{ route('admin.reports.summaries') }}" :current="request()->routeIs('admin.reports.summaries')" wire:navigate>
                    Application Summaries
                </flux:sidebar.item>

                <flux:sidebar.item href="{{ route('admin.reports.export') }}" :current="request()->routeIs('admin.reports.export')" wire:navigate>
                    Export Transaction Logs
                </flux:sidebar.item>
            </flux:sidebar.group>
            @endif

            {{-- System Administration --}}
            @if($admin?->isAdmin())
            <flux:sidebar.group expandable icon="cog-6-tooth" heading="System Administration" class="grid" :expanded="request()->routeIs('admin.users.*') || request()->routeIs('admin.roles.*') || request()->routeIs('admin.logs.*')">
                <flux:sidebar.item href="{{ route('admin.users.index') }}" :current="request()->routeIs('admin.users.*')" wire:navigate>
                    User Management
                </flux:sidebar.item>

                <flux:sidebar.item href="{{ route('admin.roles.index') }}" :current="request()->routeIs('admin.roles.*')" wire:navigate>
                    Roles & Permissions
                </flux:sidebar.item>

                <flux:sidebar.item href="{{ route('admin.logs.index') }}" :current="request()->routeIs('admin.logs.*')" wire:navigate>
                    Activity & Audit Logs
                </flux:sidebar.item>
            </flux:sidebar.group>
            @endif
        </flux:sidebar.nav>

        <flux:sidebar.spacer />

        <flux:dropdown position="top" align="start" class="max-lg:hidden">
            <flux:sidebar.profile name="{{ $fullName }}" initials="{{ $initials }}" />

            <flux:menu>
                <div class="px-3 py-2 border-b border-zinc-100 dark:border-zinc-700/60">
                    <div class="text-xs font-semibold text-zinc-900 dark:text-zinc-100">{{ $fullName }}</div>
                    <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $position }}</div>
                </div>

                <form method="POST" action="{{ route('admin.logout') }}" id="admin-logout-form">
                    @csrf
                    <flux:menu.item icon="arrow-right-start-on-rectangle" variant="danger" onclick="document.getElementById('admin-logout-form').submit();">
                        Logout
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:sidebar>

    <flux:header class="lg:hidden">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

        <flux:spacer />

        <flux:dropdown position="top" align="start">
            <flux:profile initials="{{ $initials }}" />

            <flux:menu>
                <div class="px-3 py-2 border-b border-zinc-100 dark:border-zinc-700/60">
                    <div class="text-xs font-semibold text-zinc-900 dark:text-zinc-100">{{ $fullName }}</div>
                    <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $position }}</div>
                </div>

                <form method="POST" action="{{ route('admin.logout') }}" id="admin-logout-form-mobile">
                    @csrf
                    <flux:menu.item icon="arrow-right-start-on-rectangle" variant="danger" onclick="document.getElementById('admin-logout-form-mobile').submit();">
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