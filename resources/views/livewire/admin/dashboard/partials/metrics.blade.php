<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
    <!-- 1. Total Applications Processed -->
    <flux:card class="p-4 space-y-2 border-l-4 border-l-blue-600">
        <div class="flex items-center justify-between">
            <span class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Total Filings</span>
            <flux:icon name="document-duplicate" class="size-4 text-blue-600" />
        </div>
        <div class="flex items-baseline justify-between">
            <div class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 font-mono">
                {{ number_format($this->stats['total_applications']) }}
            </div>
            <span class="text-xs font-medium text-emerald-600 dark:text-emerald-400">
                {{ $this->stats['total_change'] }}
            </span>
        </div>
        <div class="text-[11px] text-zinc-400">
            Cumulative Locational Clearances
        </div>
    </flux:card>

    <!-- 2. Pending Verification & Urgency -->
    <flux:card class="p-4 space-y-2 border-l-4 border-l-amber-500">
        <div class="flex items-center justify-between">
            <span class="text-xs font-semibold uppercase tracking-wider text-amber-700 dark:text-amber-400">Pending
                Evaluation</span>
            <flux:icon name="clock" class="size-4 text-amber-500" />
        </div>
        <div class="flex items-baseline justify-between">
            <div class="text-2xl font-bold text-amber-600 dark:text-amber-400 font-mono">
                {{ $this->stats['pending_verification'] }}
            </div>
            @if ($this->stats['pending_urgent'] > 0)
            <flux:badge size="sm" color="red" class="text-[10px]">
                {{ $this->stats['pending_urgent'] }} overdue (>3d)
            </flux:badge>
            @else
            <span class="text-xs text-zinc-400 font-mono">Normal SLA</span>
            @endif
        </div>
        <div class="text-[11px] text-zinc-400">
            Awaiting zoning officer review
        </div>
    </flux:card>

    <!-- 3. Treasury Revenue Assessed (This Month) -->
    <flux:card class="p-4 space-y-2 border-l-4 border-l-emerald-600">
        <div class="flex items-center justify-between">
            <span class="text-xs font-semibold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">Verified
                Revenue</span>
            <flux:icon name="banknotes" class="size-4 text-emerald-600" />
        </div>
        <div class="flex items-baseline justify-between">
            <div class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 font-mono">
                ₱{{ number_format($this->stats['monthly_revenue'], 2) }}
            </div>
        </div>
        <div class="text-[11px] text-zinc-400">
            Remitted via MTO Official Receipts
        </div>
    </flux:card>

    <!-- 4. Approved Rate & Field Queue -->
    <flux:card class="p-4 space-y-2 border-l-4 border-l-purple-600">
        <div class="flex items-center justify-between">
            <span class="text-xs font-semibold uppercase tracking-wider text-purple-700 dark:text-purple-400">Issued
                Clearances</span>
            <flux:icon name="check-badge" class="size-4 text-purple-600" />
        </div>
        <div class="flex items-baseline justify-between">
            <div class="text-2xl font-bold text-purple-600 dark:text-purple-400 font-mono">
                {{ $this->stats['approved_this_month'] }}
            </div>
            <span class="text-xs font-semibold text-zinc-600 dark:text-zinc-300">
                {{ $this->stats['approval_rate'] }} rate
            </span>
        </div>
        <div class="text-[11px] text-zinc-400 flex justify-between">
            <span>Approved this calendar month</span>
            <span class="font-medium text-purple-500 font-mono">{{ $this->stats['pending_inspections'] }} in
                inspection</span>
        </div>
    </flux:card>
</div>