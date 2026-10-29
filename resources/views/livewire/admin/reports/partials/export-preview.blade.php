<div class="space-y-3">
    <div class="flex items-center justify-between text-xs text-zinc-500">
        <span class="font-semibold uppercase tracking-wider">
            Live Preview (First 8 Rows)
        </span>
        <span>
            Total matching records: <strong class="text-zinc-900 dark:text-zinc-100 font-mono">{{
                number_format($this->totalMatching) }}</strong>
        </span>
    </div>

    <!-- 1. Applications Preview -->
    @if($module === 'applications')
    <div class="hidden md:block">
        <flux:table :paginate="$this->previewData">
            <flux:table.columns>
                <flux:table.column>App No.</flux:table.column>
                <flux:table.column>Applicant</flux:table.column>
                <flux:table.column>Project Details</flux:table.column>
                <flux:table.column>Type</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column align="end">Date Filed</flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @forelse($this->previewData as $row)
                <flux:table.row :key="$row->id">
                    <flux:table.cell class="font-mono text-xs font-semibold text-zinc-900 dark:text-zinc-100">{{
                        $row->application_no ?? 'Unassigned' }}</flux:table.cell>
                    <flux:table.cell class="font-medium text-sm text-zinc-900 dark:text-zinc-100">{{
                        $row->applicant_name }}</flux:table.cell>
                    <flux:table.cell class="text-xs text-zinc-500">
                        <span class="text-zinc-700 dark:text-zinc-300">{{ $row->project_type ?? 'N/A' }}</span>
                        <span class="block truncate max-w-xs text-zinc-400">{{ $row->project_location ?? 'N/A' }}</span>
                    </flux:table.cell>
                    <flux:table.cell class="text-xs">{{ $row->is_walk_in ? 'Walk-in' : 'Online' }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:badge size="sm"
                            :color="in_array($row->status, ['approve','approved']) ? 'green' : ($row->status === 'rejected' ? 'red' : 'blue')">
                            {{ ucfirst($row->status) }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell align="end" class="text-xs text-zinc-500">{{ $row->created_at?->format('M d, Y') }}
                    </flux:table.cell>
                </flux:table.row>
                @empty
                <flux:table.row>
                    <flux:table.cell colspan="6" class="text-center py-10 text-zinc-400">No applications found in this
                        period.</flux:table.cell>
                </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

    <!-- 2. Payments Preview -->
    @elseif($module === 'payments')
    <div class="hidden md:block">
        <flux:table :paginate="$this->previewData">
            <flux:table.columns>
                <flux:table.column>Assessment No.</flux:table.column>
                <flux:table.column>Applicant</flux:table.column>
                <flux:table.column>Category</flux:table.column>
                <flux:table.column>Total Assessed</flux:table.column>
                <flux:table.column>OR Number</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column align="end">Payment Date</flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @forelse($this->previewData as $row)
                <flux:table.row :key="$row->id">
                    <flux:table.cell class="font-mono text-xs font-semibold text-zinc-900 dark:text-zinc-100">{{
                        $row->assessment_no }}</flux:table.cell>
                    <flux:table.cell class="font-medium text-sm text-zinc-900 dark:text-zinc-100">{{
                        $row->locClearance?->applicant_name }}</flux:table.cell>
                    <flux:table.cell class="capitalize text-xs text-zinc-600 dark:text-zinc-300">{{ $row->category_type
                        ?? 'N/A' }}</flux:table.cell>
                    <flux:table.cell class="font-bold text-sm text-zinc-900 dark:text-zinc-100 font-mono">₱{{
                        number_format($row->grand_total, 2) }}</flux:table.cell>
                    <flux:table.cell class="font-mono text-xs">{{ $row->or_no ?? '—' }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:badge size="sm" :color="$row->status === 'paid' ? 'green' : 'blue'">
                            {{ ucfirst($row->status) }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell align="end" class="text-xs text-zinc-500">{{ $row->payment_date?->format('M d, Y')
                        ?? 'Pending' }}</flux:table.cell>
                </flux:table.row>
                @empty
                <flux:table.row>
                    <flux:table.cell colspan="7" class="text-center py-10 text-zinc-400">No payment orders found in this
                        period.</flux:table.cell>
                </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

    <!-- 3. Inspections Preview -->
    @else
    <div class="hidden md:block">
        <flux:table :paginate="$this->previewData">
            <flux:table.columns>
                <flux:table.column>Inspection No.</flux:table.column>
                <flux:table.column>Applicant</flux:table.column>
                <flux:table.column>Inspector</flux:table.column>
                <flux:table.column>Date Scheduled</flux:table.column>
                <flux:table.column>Compliance</flux:table.column>
                <flux:table.column align="end">Findings Preview</flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @forelse($this->previewData as $row)
                <flux:table.row :key="$row->id">
                    <flux:table.cell class="font-mono text-xs font-semibold text-zinc-900 dark:text-zinc-100">{{
                        $row->inspection_no }}</flux:table.cell>
                    <flux:table.cell class="font-medium text-sm text-zinc-900 dark:text-zinc-100">{{
                        $row->locClearance?->applicant_name }}</flux:table.cell>
                    <flux:table.cell class="text-xs text-zinc-600 dark:text-zinc-300">{{ $row->inspector?->full_name ??
                        $row->inspector?->name ?? 'Unassigned' }}</flux:table.cell>
                    <flux:table.cell class="text-xs text-zinc-500">{{ $row->scheduled_date?->format('M d, Y') }}
                    </flux:table.cell>
                    <flux:table.cell>
                        @php
                        $compColor = match($row->compliance_status) {
                        'compliant' => 'green',
                        'conditional' => 'yellow',
                        'non_compliant' => 'red',
                        default => 'zinc',
                        };
                        @endphp
                        <flux:badge size="sm" :color="$compColor">
                            {{ ucfirst(str_replace('_', ' ', $row->compliance_status)) }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell align="end" class="text-xs text-zinc-500 truncate max-w-xs">{{ $row->findings ?:
                        'No notes' }}</flux:table.cell>
                </flux:table.row>
                @empty
                <flux:table.row>
                    <flux:table.cell colspan="6" class="text-center py-10 text-zinc-400">No inspections found in this
                        period.</flux:table.cell>
                </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>
    @endif

    <!-- Mobile Cards Preview (< md) -->
    <div class="space-y-3 md:hidden">
        @forelse($this->previewData as $item)
        <flux:card class="p-3.5 space-y-2">
            <div class="flex justify-between items-start text-xs font-mono">
                <span class="font-semibold text-zinc-900 dark:text-zinc-100">
                    {{ $item->application_no ?? $item->assessment_no ?? $item->inspection_no }}
                </span>
                <span class="text-zinc-500 text-[11px]">{{ $item->created_at?->format('M d, Y') ??
                    $item->scheduled_date?->format('M d, Y') }}</span>
            </div>
            <div class="text-sm font-medium text-zinc-800 dark:text-zinc-200">
                {{ $item->applicant_name ?? $item->locClearance?->applicant_name }}
            </div>
        </flux:card>
        @empty
        <flux:card class="p-6 text-center text-zinc-400 text-sm">
            No matching records to preview.
        </flux:card>
        @endforelse

        <div>{{ $this->previewData->links() }}</div>
    </div>
</div>