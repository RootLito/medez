<flux:card class="p-5 flex flex-col justify-between space-y-4">
    <div>
        <div class="flex items-center justify-between border-b border-zinc-100 dark:border-zinc-800 pb-3 mb-3">
            <div>
                <flux:heading size="base" class="font-bold">Field Site Inspections</flux:heading>
                <flux:subheading class="text-xs">Upcoming boundary & setback visits</flux:subheading>
            </div>
            <flux:button variant="ghost" size="xs" icon-trailing="chevron-right" wire:navigate
                href="{{ route('admin.inspections.index') }}">
                Schedule
            </flux:button>
        </div>

        @if ($this->upcomingInspections->isEmpty())
        <div
            class="text-center py-8 px-4 border border-dashed border-zinc-200 dark:border-zinc-800 rounded-xl space-y-2">
            <flux:icon name="calendar-days" class="size-6 mx-auto text-zinc-400" />
            <p class="text-xs text-zinc-500">No field visits scheduled for today.</p>
            <flux:button href="{{ route('admin.inspections.index') }}" wire:navigate variant="subtle" size="xs">
                Schedule Visit
            </flux:button>
        </div>
        @else
        <div class="space-y-2.5">
            @foreach ($this->upcomingInspections as $insp)
            <div
                class="p-3 bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-100 dark:border-zinc-800 rounded-lg space-y-1">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-mono font-semibold text-blue-600 dark:text-blue-400">
                        {{ $insp->inspection_no }}
                    </span>
                    <span class="text-[11px] font-medium text-zinc-500">
                        {{ $insp->scheduled_date?->format('M d') }}
                        @if($insp->scheduled_time)
                        • {{ date('g:i A', strtotime($insp->scheduled_time)) }}
                        @endif
                    </span>
                </div>
                <div class="text-xs font-medium text-zinc-900 dark:text-zinc-100">
                    {{ $insp->locClearance?->applicant_name }}
                </div>
                <div class="text-[11px] text-zinc-400 truncate flex justify-between">
                    <span class="truncate max-w-[150px]">📍 {{ $insp->locClearance?->project_location ?? 'Malalag'
                        }}</span>
                    <span class="italic">{{ $insp->inspector?->full_name ?? $insp->inspector?->name ?? 'Inspector'
                        }}</span>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    <!-- Quick Operations Shortcuts -->
    <div class="pt-3 border-t border-zinc-100 dark:border-zinc-800 space-y-2">
        <span class="text-[11px] font-bold uppercase tracking-wider text-zinc-400 block">Zoning Shortcuts</span>

        <div class="grid grid-cols-2 gap-2">
            <flux:button variant="subtle" size="sm" icon="banknotes" wire:navigate
                href="{{ route('admin.payments.index') }}" class="justify-center text-xs">
                Payments
            </flux:button>
            <flux:button variant="subtle" size="sm" icon="map-pin" wire:navigate
                href="{{ route('admin.geospatial.index') }}" class="justify-center text-xs">
                Zone Map
            </flux:button>
        </div>
    </div>
</flux:card>