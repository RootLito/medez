<!-- Mobile Card View (< md) -->
<div class="space-y-3 md:hidden">
    @forelse($this->orders as $order)
    @php
    $rawStatus = strtolower($order->status ?? 'pending');
    $badgeColor = match($rawStatus) {
    'paid' => 'green',
    'pending' => 'blue',
    'cancelled' => 'red',
    default => 'zinc',
    };
    $displayStatus = match($rawStatus) {
    'paid' => 'Paid',
    'pending' => 'Pending',
    'cancelled' => 'Cancelled',
    default => ucfirst($rawStatus),
    };
    @endphp
    <flux:card class="p-4 space-y-3">
        <div class="flex items-start justify-between gap-2">
            <div>
                <div class="font-mono text-xs font-semibold text-zinc-900 dark:text-zinc-100">
                    {{ $order->assessment_no }}
                </div>
                @if ($order->or_no)
                <span class="text-[11px] font-mono text-zinc-500">OR: {{ $order->or_no }}</span>
                @endif
            </div>
            <flux:badge size="sm" :color="$badgeColor" class="text-[11px]">
                {{ $displayStatus }}
            </flux:badge>
        </div>

        <div class="pt-2 border-t border-zinc-100 dark:border-zinc-800 space-y-1">
            <div class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                {{ $order->locClearance?->applicant_name ?? ($order->locClearance?->client?->first_name . ' ' .
                $order->locClearance?->client?->last_name) }}
            </div>
            <div class="text-xs text-zinc-500">
                <span class="font-medium text-zinc-700 dark:text-zinc-300">App No:</span>
                <span class="font-mono">{{ $order->locClearance?->application_no ?? 'N/A' }}</span>
            </div>
            <div class="text-xs text-zinc-500">
                <span>Category: {{ ucfirst($order->category_type ?? 'N/A') }}</span>
            </div>
            <div class="text-sm font-bold text-zinc-900 dark:text-zinc-100 pt-1">
                ₱{{ number_format($order->grand_total, 2) }}
            </div>
        </div>

        <div
            class="flex items-center justify-between pt-2 border-t border-zinc-100 dark:border-zinc-800 text-xs text-zinc-400">
            <span>{{ $order->created_at?->format('M d, Y') ?? 'N/A' }}</span>
            <div class="flex items-center gap-1.5">
                <flux:button wire:click="openPrintModal({{ $order->id }})" variant="subtle" size="xs" icon="printer">
                    Print Slip
                </flux:button>
                <flux:button wire:click="openPaymentModal({{ $order->id }})" variant="ghost" size="xs" icon="banknotes"
                    class="text-blue-600 dark:text-blue-400">
                    {{ $order->status === 'paid' ? 'Edit OR' : 'Record OR' }}
                </flux:button>
            </div>
        </div>
    </flux:card>
    @empty
    <flux:card class="text-center py-10 px-4 space-y-2">
        <flux:icon name="banknotes" class="size-8 mx-auto text-zinc-400" />
        <p class="text-sm font-medium text-zinc-700 dark:text-zinc-300">No payment orders found</p>
    </flux:card>
    @endforelse

    <div>{{ $this->orders->links() }}</div>
</div>

<!-- Desktop Table View (>= md) -->
<div class="hidden md:block">
    <flux:table :paginate="$this->orders">
        <flux:table.columns>
            <flux:table.column>Assessment / OR No.</flux:table.column>
            <flux:table.column>Applicant & App No.</flux:table.column>
            <flux:table.column>Category</flux:table.column>
            <flux:table.column>Total Amount</flux:table.column>
            <flux:table.column>Date Assessed</flux:table.column>
            <flux:table.column>Status</flux:table.column>
            <flux:table.column align="end">Actions</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse($this->orders as $order)
            @php
            $rawStatus = strtolower($order->status ?? 'pending');
            $badgeColor = match($rawStatus) {
            'paid' => 'green',
            'pending' => 'blue',
            'cancelled' => 'red',
            default => 'zinc',
            };
            $displayStatus = match($rawStatus) {
            'paid' => 'Paid',
            'pending' => 'Pending',
            'cancelled' => 'Cancelled',
            default => ucfirst($rawStatus),
            };
            @endphp
            <flux:table.row :key="$order->id">
                <flux:table.cell>
                    <div class="font-mono text-xs font-semibold text-zinc-900 dark:text-zinc-100">
                        {{ $order->assessment_no }}
                    </div>
                    @if ($order->or_no)
                    <div class="text-xs text-zinc-400 font-mono">OR: {{ $order->or_no }}</div>
                    @endif
                </flux:table.cell>

                <flux:table.cell>
                    <div class="font-medium text-sm text-zinc-900 dark:text-zinc-100">
                        {{ $order->locClearance?->applicant_name ?? ($order->locClearance?->client?->first_name . ' ' .
                        $order->locClearance?->client?->last_name) }}
                    </div>
                    <div class="text-xs text-zinc-400 font-mono">
                        App: {{ $order->locClearance?->application_no ?? 'Unassigned' }}
                    </div>
                </flux:table.cell>

                <flux:table.cell>
                    <span class="capitalize text-sm text-zinc-700 dark:text-zinc-300">{{ $order->category_type ?? 'N/A'
                        }}</span>
                </flux:table.cell>

                <flux:table.cell class="whitespace-nowrap font-semibold text-sm text-zinc-900 dark:text-zinc-100">
                    ₱{{ number_format($order->grand_total, 2) }}
                </flux:table.cell>

                <flux:table.cell class="whitespace-nowrap text-sm text-zinc-500">
                    {{ $order->created_at?->format('M d, Y') ?? 'N/A' }}
                </flux:table.cell>

                <flux:table.cell>
                    <flux:badge size="sm" :color="$badgeColor">
                        {{ $displayStatus }}
                    </flux:badge>
                </flux:table.cell>

                <flux:table.cell align="end">
                    <div class="flex items-center justify-end gap-1">
                        <flux:button wire:click="openPrintModal({{ $order->id }})" variant="subtle" size="sm"
                            icon="printer" tooltip="Print Order Slip" />
                        <flux:button wire:click="openPaymentModal({{ $order->id }})" variant="subtle" size="sm"
                            icon="banknotes" tooltip="Record Treasury OR" />
                    </div>
                </flux:table.cell>
            </flux:table.row>
            @empty
            <flux:table.row>
                <flux:table.cell colspan="7" class="text-center py-12 text-zinc-500">
                    No payment order records found.
                </flux:table.cell>
            </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
</div>