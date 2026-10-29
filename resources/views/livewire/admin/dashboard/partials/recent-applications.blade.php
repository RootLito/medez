<flux:card class="lg:col-span-2 p-5 space-y-4">
    <div class="flex items-center justify-between border-b border-zinc-100 dark:border-zinc-800 pb-3">
        <div>
            <flux:heading size="base" class="font-bold">Recent Clearance Applications</flux:heading>
            <flux:subheading class="text-xs">Latest filings registered into the digital zoning logbook.
            </flux:subheading>
        </div>
        <flux:button variant="ghost" size="xs" icon-trailing="chevron-right" wire:navigate
            href="{{ route('admin.applications.index') }}">
            Full Logbook
        </flux:button>
    </div>

    @if ($this->recentApplications->isEmpty())
    <div class="text-center py-10 text-zinc-400 text-xs">
        No clearance applications recorded yet.
    </div>
    @else
    <!-- Mobile Card View (< md) -->
    <div class="space-y-3 md:hidden">
        @foreach ($this->recentApplications as $app)
        @php
        $rawStatus = strtolower($app->status ?? 'pending');
        $badgeColor = match($rawStatus) {
        'approve', 'approved' => 'green',
        'resubmit' => 'yellow',
        'pending' => 'blue',
        'rejected' => 'red',
        default => 'zinc',
        };
        @endphp
        <div
            class="p-3 bg-zinc-50 dark:bg-zinc-800/40 rounded-lg space-y-2 border border-zinc-100 dark:border-zinc-800">
            <div class="flex justify-between items-center">
                <span class="font-mono text-xs font-semibold text-zinc-900 dark:text-zinc-100">
                    {{ $app->application_no ?? '#' . $app->id }}
                </span>
                <flux:badge size="sm" :color="$badgeColor" class="text-[10px]">
                    {{ ucfirst($app->status ?? 'pending') }}
                </flux:badge>
            </div>
            <div class="text-sm font-medium text-zinc-800 dark:text-zinc-200">
                {{ $app->applicant_name }}
            </div>
            <div
                class="flex justify-between text-xs text-zinc-400 pt-1 border-t border-zinc-100 dark:border-zinc-800/50">
                <span class="truncate max-w-[180px]">{{ $app->project_type ?? 'N/A' }}</span>
                <span>{{ $app->created_at?->diffForHumans() }}</span>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Desktop Formal Table (>= md) -->
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-left text-xs text-zinc-600 dark:text-zinc-400">
            <thead
                class="border-b border-zinc-100 dark:border-zinc-800 text-zinc-400 uppercase font-semibold bg-zinc-50/50 dark:bg-zinc-800/30">
                <tr>
                    <th class="p-2.5">App No.</th>
                    <th class="p-2.5">Applicant / Corporation</th>
                    <th class="p-2.5">Classification & Site</th>
                    <th class="p-2.5">Filed</th>
                    <th class="p-2.5 text-right">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800/60">
                @foreach ($this->recentApplications as $app)
                @php
                $rawStatus = strtolower($app->status ?? 'pending');
                $badgeColor = match($rawStatus) {
                'approve', 'approved' => 'green',
                'resubmit' => 'yellow',
                'pending' => 'blue',
                'rejected' => 'red',
                default => 'zinc',
                };
                @endphp
                <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/20 transition">
                    <td class="p-2.5 font-mono font-semibold text-zinc-900 dark:text-zinc-100">
                        <a href="{{ route('admin.applications.show', $app->id) }}" wire:navigate
                            class="hover:underline">
                            {{ $app->application_no ?? '#' . $app->id }}
                        </a>
                    </td>
                    <td class="p-2.5">
                        <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $app->applicant_name }}</div>
                        @if($app->corporation_name)
                        <div class="text-[11px] text-zinc-400 truncate max-w-[180px]">{{ $app->corporation_name }}</div>
                        @endif
                    </td>
                    <td class="p-2.5">
                        <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $app->project_type ?? 'Clearance'
                            }}</span>
                        <span class="block truncate max-w-[200px] text-[11px] text-zinc-400">{{ $app->project_location
                            ?? 'N/A' }}</span>
                    </td>
                    <td class="p-2.5 whitespace-nowrap text-zinc-500">
                        {{ $app->created_at?->diffForHumans() }}
                    </td>
                    <td class="p-2.5 text-right">
                        <flux:badge size="sm" :color="$badgeColor">
                            {{ ucfirst($app->status ?? 'pending') }}
                        </flux:badge>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</flux:card>