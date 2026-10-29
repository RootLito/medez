<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-2.5 sm:gap-3">
    <!-- 1. Searchbar -->
    <div class="sm:col-span-2 lg:col-span-3">
        <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Search..." clearable />
    </div>

    <!-- 2. Dataset -->
    <div class="sm:col-span-1 lg:col-span-2">
        <flux:select wire:model.live="module" placeholder="Dataset">
            <flux:select.option value="applications">Clearances</flux:select.option>
            <flux:select.option value="payments">Payment Orders</flux:select.option>
            <flux:select.option value="inspections">Site Inspections</flux:select.option>
        </flux:select>
    </div>

    <!-- 3. Status -->
    <div class="sm:col-span-1 lg:col-span-2">
        <flux:select wire:model.live="status" placeholder="Status">
            <flux:select.option value="all">All Statuses</flux:select.option>
            @if($module === 'applications')
            <flux:select.option value="pending">Pending</flux:select.option>
            <flux:select.option value="approve">Approved</flux:select.option>
            <flux:select.option value="resubmit">Resubmit</flux:select.option>
            <flux:select.option value="rejected">Rejected</flux:select.option>
            @elseif($module === 'payments')
            <flux:select.option value="pending">Pending</flux:select.option>
            <flux:select.option value="paid">Paid</flux:select.option>
            <flux:select.option value="cancelled">Cancelled</flux:select.option>
            @else
            <flux:select.option value="compliant">Compliant</flux:select.option>
            <flux:select.option value="conditional">Conditional</flux:select.option>
            <flux:select.option value="non_compliant">Non-Compliant</flux:select.option>
            @endif
        </flux:select>
    </div>

    <!-- 4. Period -->
    <div class="sm:col-span-1 lg:col-span-2">
        <flux:select wire:model.live="period" placeholder="Period">
            <flux:select.option value="this_month">This Month</flux:select.option>
            <flux:select.option value="last_month">Last Month</flux:select.option>
            <flux:select.option value="this_quarter">This Quarter</flux:select.option>
            <flux:select.option value="this_year">This Year</flux:select.option>
            <flux:select.option value="custom">Custom Range</flux:select.option>
        </flux:select>
    </div>

    <!-- 5. Date Range -->
    <div class="grid grid-cols-2 gap-2 sm:col-span-2 lg:col-span-3">
        <flux:input type="date" wire:model.live="dateFrom" :disabled="$period !== 'custom'" />
        <flux:input type="date" wire:model.live="dateTo" :disabled="$period !== 'custom'" />
    </div>
</div>