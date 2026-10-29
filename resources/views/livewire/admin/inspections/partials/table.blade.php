<!-- Mobile Card View (< md) -->
<div class="space-y-3 md:hidden">
    @forelse($this->inspections as $inspection)
    @php
    $rawStatus = strtolower($inspection->status ?? 'scheduled');
    $badgeColor = match($rawStatus) {
    'completed' => 'green',
    'scheduled' => 'blue',
    'in_progress' => 'yellow',
    'rescheduled' => 'purple',
    'cancelled' => 'red',
    default => 'zinc',
    };
    $displayStatus = ucfirst(str_replace('_', ' ', $rawStatus));
    @endphp
    <flux:card class="p-4 space-y-3">
        <div class="flex items-start justify-between gap-2">
            <div>
                <div class="font-mono text-xs font-semibold text-zinc-900 dark:text-zinc-100">
                    {{ $inspection->inspection_no }}
                </div>
                <span class="text-xs text-zinc-500">{{ $inspection->inspection_type }}</span>
            </div>
            <flux:badge size="sm" :color="$badgeColor" class="text-[11px]">
                {{ $displayStatus }}
            </flux:badge>
        </div>

        <div class="pt-2 border-t border-zinc-100 dark:border-zinc-800 space-y-1">
            <div class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                {{ $inspection->locClearance?->applicant_name }}
            </div>
            <div class="text-xs text-zinc-500">
                <span class="font-medium text-zinc-700 dark:text-zinc-300">App No:</span>
                <span class="font-mono">{{ $inspection->locClearance?->application_no ?? 'Unassigned' }}</span>
            </div>
            <div class="text-xs text-zinc-400 truncate">
                {{ $inspection->locClearance?->project_location ?? 'No location specified' }}
            </div>
            <div class="text-xs font-medium text-zinc-700 dark:text-zinc-300 pt-1">
                Inspector: <span class="font-normal">{{ $inspection->inspector?->full_name ??
                    $inspection->inspector?->name ?? 'Unassigned' }}</span>
            </div>
        </div>

        <div
            class="flex items-center justify-between pt-2 border-t border-zinc-100 dark:border-zinc-800 text-xs text-zinc-400">
            <span>
                📅 {{ $inspection->scheduled_date?->format('M d, Y') }}
                @if($inspection->scheduled_time)
                • {{ date('g:i A', strtotime($inspection->scheduled_time)) }}
                @endif
            </span>
            <div class="flex items-center gap-1.5">
                @if($inspection->status !== 'completed' && $inspection->status !== 'cancelled')
                <flux:button wire:click="openCompleteModal({{ $inspection->id }})" variant="primary" size="xs"
                    icon="check">
                    Log Findings
                </flux:button>
                <flux:button wire:click="cancelInspection({{ $inspection->id }})" variant="ghost" size="xs"
                    icon="x-mark" class="text-red-600">
                    Cancel
                </flux:button>
                @else
                <flux:button wire:click="openCompleteModal({{ $inspection->id }})" variant="subtle" size="xs"
                    icon="eye">
                    View Findings
                </flux:button>
                @endif
            </div>
        </div>
    </flux:card>
    @empty
    <flux:card class="text-center py-10 px-4 space-y-2">
        <flux:icon name="calendar-days" class="size-8 mx-auto text-zinc-400" />
        <p class="text-sm font-medium text-zinc-700 dark:text-zinc-300">No scheduled site inspections found</p>
    </flux:card>
    @endforelse

    <div>{{ $this->inspections->links() }}</div>
</div>

<!-- Desktop Table View (>= md) -->
<div class="hidden md:block">
    <flux:table :paginate="$this->inspections">
        <flux:table.columns>
            <flux:table.column>Inspection / Type</flux:table.column>
            <flux:table.column>Applicant & Application</flux:table.column>
            <flux:table.column>Site Location</flux:table.column>
            <flux:table.column>Scheduled Date & Time</flux:table.column>
            <flux:table.column>Assigned Inspector</flux:table.column>
            <flux:table.column>Status</flux:table.column>
            <flux:table.column align="end">Actions</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse($this->inspections as $inspection)
            @php
            $rawStatus = strtolower($inspection->status ?? 'scheduled');
            $badgeColor = match($rawStatus) {
            'completed' => 'green',
            'scheduled' => 'blue',
            'in_progress' => 'yellow',
            'rescheduled' => 'purple',
            'cancelled' => 'red',
            default => 'zinc',
            };
            $displayStatus = ucfirst(str_replace('_', ' ', $rawStatus));
            @endphp
            <flux:table.row :key="$inspection->id">
                <flux:table.cell>
                    <div class="font-mono text-xs font-semibold text-zinc-900 dark:text-zinc-100">
                        {{ $inspection->inspection_no }}
                    </div>
                    <div class="text-xs text-zinc-400">{{ $inspection->inspection_type }}</div>
                </flux:table.cell>

                <flux:table.cell>
                    <div class="font-medium text-sm text-zinc-900 dark:text-zinc-100">
                        {{ $inspection->locClearance?->applicant_name }}
                    </div>
                    <div class="text-xs text-zinc-400 font-mono">
                        App: {{ $inspection->locClearance?->application_no ?? 'Unassigned' }}
                    </div>
                </flux:table.cell>

                <flux:table.cell>
                    <div class="text-sm text-zinc-700 dark:text-zinc-300 truncate max-w-xs">
                        {{ $inspection->locClearance?->project_location ?? 'N/A' }}
                    </div>
                </flux:table.cell>

                <flux:table.cell class="whitespace-nowrap text-sm text-zinc-500">
                    <div>{{ $inspection->scheduled_date?->format('M d, Y') }}</div>
                    @if($inspection->scheduled_time)
                    <div class="text-xs text-zinc-400">{{ date('g:i A', strtotime($inspection->scheduled_time)) }}</div>
                    @endif
                </flux:table.cell>

                <flux:table.cell>
                    <span class="text-sm font-medium text-zinc-800 dark:text-zinc-200">
                        {{ $inspection->inspector?->full_name ?? $inspection->inspector?->name ?? 'Unassigned' }}
                    </span>
                </flux:table.cell>

                <flux:table.cell>
                    <flux:badge size="sm" :color="$badgeColor">
                        {{ $displayStatus }}
                    </flux:badge>
                </flux:table.cell>

                <flux:table.cell align="end">
                    <div class="flex items-center justify-end gap-1">
                        @if($inspection->status !== 'completed' && $inspection->status !== 'cancelled')
                        <flux:button wire:click="openCompleteModal({{ $inspection->id }})" variant="subtle" size="sm"
                            icon="clipboard-document-check" tooltip="Log Findings & Complete" />
                        <flux:button wire:click="cancelInspection({{ $inspection->id }})" variant="subtle" size="sm"
                            icon="x-mark" class="text-red-500" tooltip="Cancel Inspection" />
                        @else
                        <flux:button wire:click="openCompleteModal({{ $inspection->id }})" variant="subtle" size="sm"
                            icon="eye" tooltip="View Findings" />
                        @endif
                    </div>
                </flux:table.cell>
            </flux:table.row>
            @empty
            <flux:table.row>
                <flux:table.cell colspan="7" class="text-center py-12 text-zinc-500">
                    No inspection schedules found.
                </flux:table.cell>
            </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
</div>