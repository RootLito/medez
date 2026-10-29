<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
    <!-- Total Received -->
    <flux:card class="p-4 space-y-1">
        <div class="flex items-center justify-between">
            <span class="text-xs font-semibold text-zinc-500 uppercase tracking-wider">Total Received</span>
            <flux:icon name="document-duplicate" class="size-4 text-zinc-400" />
        </div>
        <div class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">
            {{ number_format($this->kpi['total_apps']) }}
        </div>
        <div class="text-[11px] text-zinc-500 flex gap-2 pt-0.5">
            <span>Online: <strong>{{ $this->kpi['online'] }}</strong></span>
            <span>•</span>
            <span>Walk-in: <strong>{{ $this->kpi['walk_in'] }}</strong></span>
        </div>
    </flux:card>

    <!-- Revenue Collected -->
    <flux:card class="p-4 space-y-1">
        <div class="flex items-center justify-between">
            <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">Revenue
                Assessed</span>
            <flux:icon name="banknotes" class="size-4 text-emerald-500" />
        </div>
        <div class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">
            ₱{{ number_format($this->kpi['revenue'], 2) }}
        </div>
        <div class="text-[11px] text-zinc-500 pt-0.5">
            Verified MTO receipts
        </div>
    </flux:card>

    <!-- Approvals & Rate -->
    <flux:card class="p-4 space-y-1">
        <div class="flex items-center justify-between">
            <span class="text-xs font-semibold text-blue-600 dark:text-blue-400 uppercase tracking-wider">Approved
                Clearances</span>
            <flux:icon name="check-badge" class="size-4 text-blue-500" />
        </div>
        <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
            {{ number_format($this->kpi['approved_apps']) }}
        </div>
        <div class="text-[11px] text-zinc-500 pt-0.5">
            Approval Rate: <strong>{{ $this->kpi['approval_rate'] }}%</strong>
        </div>
    </flux:card>

    <!-- Inspections Completed -->
    <flux:card class="p-4 space-y-1">
        <div class="flex items-center justify-between">
            <span class="text-xs font-semibold text-purple-600 dark:text-purple-400 uppercase tracking-wider">Ocular
                Inspections</span>
            <flux:icon name="clipboard-document-check" class="size-4 text-purple-500" />
        </div>
        <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">
            {{ number_format($this->kpi['inspections_done']) }}
        </div>
        <div class="text-[11px] text-zinc-500 pt-0.5">
            Completed site visits
        </div>
    </flux:card>
</div>