<!-- Mobile Card View (< md) -->
<div class="space-y-3 md:hidden">
    @forelse($this->logs as $log)
    @php
    $rawStatus = strtolower($log->compliance_status ?? 'pending');
    $badgeColor = match($rawStatus) {
    'compliant' => 'green',
    'conditional' => 'yellow',
    'non_compliant' => 'red',
    default => 'zinc',
    };
    $displayStatus = ucfirst(str_replace('_', ' ', $rawStatus));
    @endphp
    <flux:card class="p-4 space-y-3">
        <div class="flex items-start justify-between gap-2">
            <div>
                <div class="font-mono text-xs font-semibold text-zinc-900 dark:text-zinc-100">
                    {{ $log->inspection_no }}
                </div>
                <span class="text-xs text-zinc-500">{{ $log->inspection_type }}</span>
            </div>
            <flux:badge size="sm" :color="$badgeColor" class="text-[11px]">
                {{ $displayStatus }}
            </flux:badge>
        </div>

        <div class="pt-2 border-t border-zinc-100 dark:border-zinc-800 space-y-1">
            <div class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                {{ $log->locClearance?->applicant_name }}
            </div>
            <div class="text-xs text-zinc-500">
                <span class="font-medium text-zinc-700 dark:text-zinc-300">App No:</span>
                <span class="font-mono">{{ $log->locClearance?->application_no ?? 'Unassigned' }}</span>
            </div>
            <div class="text-xs text-zinc-400 truncate">
                📍 {{ $log->locClearance?->project_location ?? 'N/A' }}
            </div>
            <div class="text-xs text-zinc-600 dark:text-zinc-400 pt-1 line-clamp-2 italic">
                "{{ $log->findings ?? 'No notes recorded.' }}"
            </div>
        </div>

        <div
            class="flex items-center justify-between pt-2 border-t border-zinc-100 dark:border-zinc-800 text-xs text-zinc-400">
            <span>Inspected: {{ $log->inspected_at?->format('M d, Y') ?? $log->scheduled_date?->format('M d, Y')
                }}</span>
            <flux:button wire:click="openLogDetail({{ $log->id }})" variant="subtle" size="xs" icon="eye">
                View Report
            </flux:button>
        </div>
    </flux:card>
    @empty
    <flux:card class="text-center py-10 px-4 space-y-2">
        <flux:icon name="document-text" class="size-8 mx-auto text-zinc-400" />
        <p class="text-sm font-medium text-zinc-700 dark:text-zinc-300">No inspection log records found</p>
    </flux:card>
    @endforelse

    <div>{{ $this->logs->links() }}</div>
</div>

<!-- Desktop Table View (>= md) -->
<div class="hidden md:block">
    <flux:table :paginate="$this->logs">
        <flux:table.columns>
            <flux:table.column>Inspection No. & Type</flux:table.column>
            <flux:table.column>Applicant & Application</flux:table.column>
            <flux:table.column>Location</flux:table.column>
            <flux:table.column>Inspector</flux:table.column>
            <flux:table.column>Date Inspected</flux:table.column>
            <flux:table.column>Compliance</flux:table.column>
            <flux:table.column align="end">Actions</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse($this->logs as $log)
            @php
            $rawStatus = strtolower($log->compliance_status ?? 'pending');
            $badgeColor = match($rawStatus) {
            'compliant' => 'green',
            'conditional' => 'yellow',
            'non_compliant' => 'red',
            default => 'zinc',
            };
            $displayStatus = ucfirst(str_replace('_', ' ', $rawStatus));
            @endphp
            <flux:table.row :key="$log->id">
                <flux:table.cell>
                    <div class="font-mono text-xs font-semibold text-zinc-900 dark:text-zinc-100">
                        {{ $log->inspection_no }}
                    </div>
                    <div class="text-xs text-zinc-400">{{ $log->inspection_type }}</div>
                </flux:table.cell>

                <flux:table.cell>
                    <div class="font-medium text-sm text-zinc-900 dark:text-zinc-100">
                        {{ $log->locClearance?->applicant_name }}
                    </div>
                    <div class="text-xs text-zinc-400 font-mono">
                        App: {{ $log->locClearance?->application_no ?? 'Unassigned' }}
                    </div>
                </flux:table.cell>

                <flux:table.cell>
                    <div class="text-sm text-zinc-700 dark:text-zinc-300 truncate max-w-xs">
                        {{ $log->locClearance?->project_location ?? 'N/A' }}
                    </div>
                </flux:table.cell>

                <flux:table.cell>
                    <span class="text-sm font-medium text-zinc-800 dark:text-zinc-200">
                        {{ $log->inspector?->full_name ?? $log->inspector?->name ?? 'Unassigned' }}
                    </span>
                </flux:table.cell>

                <flux:table.cell class="whitespace-nowrap text-sm text-zinc-500">
                    {{ $log->inspected_at?->format('M d, Y') ?? $log->scheduled_date?->format('M d, Y') }}
                </flux:table.cell>

                <flux:table.cell>
                    <flux:badge size="sm" :color="$badgeColor">
                        {{ $displayStatus }}
                    </flux:badge>
                </flux:table.cell>

                <flux:table.cell align="end">
                    <flux:button wire:click="openLogDetail({{ $log->id }})" variant="filled" icon-variant="outline" size="sm"
                        icon="document-magnifying-glass" tooltip="View Full Report" />
                </flux:table.cell>
            </flux:table.row>
            @empty
            <flux:table.row>
                <flux:table.cell colspan="7" class="text-center py-12 text-zinc-500">
                    No completed inspection logs found.
                </flux:table.cell>
            </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
</div>