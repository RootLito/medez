<flux:card class="p-4 sm:p-5 space-y-4">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3">
        <!-- Target Module -->
        <div class="sm:col-span-1 lg:col-span-4">
            <flux:select wire:model.live="module" label="Select Dataset to Export *">
                <flux:select.option value="applications">Locational Clearance Applications</flux:select.option>
                <flux:select.option value="payments">Orders of Payment & Treasury Records</flux:select.option>
                <flux:select.option value="inspections">Site Inspection Logs</flux:select.option>
            </flux:select>
        </div>

        <!-- Status Filter (Dynamic based on selected dataset) -->
        <div class="sm:col-span-1 lg:col-span-3">
            <flux:select wire:model.live="status" label="Filter Status">
                <flux:select.option value="all">All Records</flux:select.option>
                @if($module === 'applications')
                <flux:select.option value="pending">Pending</flux:select.option>
                <flux:select.option value="approved">Approved</flux:select.option>
                <flux:select.option value="resubmit">Resubmit</flux:select.option>
                <flux:select.option value="rejected">Rejected</flux:select.option>
                @elseif($module === 'payments')
                <flux:select.option value="pending">Pending (Unpaid)</flux:select.option>
                <flux:select.option value="paid">Paid (MTO Verified)</flux:select.option>
                <flux:select.option value="cancelled">Cancelled</flux:select.option>
                @else
                <flux:select.option value="compliant">Compliant</flux:select.option>
                <flux:select.option value="conditional">Conditional</flux:select.option>
                <flux:select.option value="non_compliant">Non-Compliant</flux:select.option>
                @endif
            </flux:select>
        </div>

        <!-- Date Range -->
        <div class="grid grid-cols-2 gap-2 sm:col-span-2 lg:col-span-5">
            <flux:input type="date" wire:model.live="dateFrom" label="From Date" />
            <flux:input type="date" wire:model.live="dateTo" label="To Date" />
        </div>
    </div>

    <!-- Search input -->
    <div
        class="pt-2 border-t border-zinc-100 dark:border-zinc-800 flex flex-col sm:flex-row items-center justify-between gap-3">
        <div class="w-full sm:max-w-md">
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass"
                placeholder="Search across records before downloading..." clearable />
        </div>

        <div class="text-xs text-zinc-500 flex items-center gap-2">
            <span>Matching: <strong class="text-zinc-900 dark:text-zinc-100 font-mono">{{
                    number_format($this->totalMatching) }}</strong> records</span>
        </div>
    </div>
</flux:card>