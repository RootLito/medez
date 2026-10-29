<?php

use function Livewire\Volt\{layout, title, state, usesPagination, computed, updated};
use App\Models\PaymentOrder;
use App\Models\LocClearance;
use Flux\Flux;

layout('components.layouts.admin');
title('Payment Orders');

usesPagination();

state([
    // Filters & Search
    'search' => '',
    'status' => 'all',
    'dateFrom' => '',
    'dateTo' => '',

    // Create Payment Order Form
    'application_id' => '',
    'approved_by' => '',
    'category_type' => 'Commercial',
    'project_cost' => 0.00,
    'base_clearance_fee' => 288.00,
    'excess_fee' => 0.00,
    'processing_fee' => 100.00,
    'inspection_fee' => 150.00,
    'surcharge_fee' => 0.00,
    'remarks' => '',

    // Record Payment State
    'selected_order_id' => null,
    'or_no' => '',
    'payment_date' => '',

    // Print State
    'print_order_id' => null,
]);

// Auto-fill project cost when selecting an application & reset pagination
updated([
    'application_id' => function ($value) {
        if ($value) {
            $clearance = LocClearance::find($value);
            if ($clearance) {
                $this->project_cost = (float) ($clearance->project_cost ?? 0);
            }
        }
    },
    'search' => fn () => $this->resetPage(),
    'status' => fn () => $this->resetPage(),
]);

// Computed: Eligible clearances without a payment order yet
$availableClearances = computed(function () {
    return LocClearance::whereDoesntHave('paymentOrder')
        ->where('is_archived', false)
        ->latest()
        ->get();
});

// Computed: Fetch list of staff / administrators who can approve
$approvers = computed(function () {
    $model = config('auth.providers.admins.model')
        ?? (class_exists(\App\Models\Admin::class) ? \App\Models\Admin::class : \App\Models\User::class);

    return $model::all();
});

// Computed: Paginated Orders Query
$orders = computed(function () {
    return PaymentOrder::query()
        ->with(['locClearance.client', 'assessor', 'approver'])
        ->when($this->search, function ($query) {
            $query->where(function ($q) {
                $q->where('assessment_no', 'like', '%' . $this->search . '%')
                    ->orWhere('or_no', 'like', '%' . $this->search . '%')
                    ->orWhereHas('locClearance', function ($cq) {
                        $cq->where('application_no', 'like', '%' . $this->search . '%')
                           ->orWhere('applicant_name', 'like', '%' . $this->search . '%');
                    });
            });
        })
        ->when($this->status !== 'all', fn ($q) => $q->where('status', $this->status))
        ->when($this->dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
        ->when($this->dateTo, fn ($q) => $q->whereDate('created_at', '<=', $this->dateTo))
        ->latest()
        ->paginate(15);
});

// Computed: Currently previewed order for print
$printOrder = computed(function () {
    return $this->print_order_id
        ? PaymentOrder::with(['locClearance.client', 'assessor', 'approver'])->find($this->print_order_id)
        : null;
});

// Action: Open Create Modal
$openCreateModal = function () {
    $this->reset([
        'application_id', 'project_cost', 'excess_fee', 'surcharge_fee', 'remarks'
    ]);
    $this->base_clearance_fee = 288.00;
    $this->processing_fee = 100.00;
    $this->inspection_fee = 150.00;
    $this->category_type = 'Commercial';

    // Pre-select current logged-in user as approver default
    $this->approved_by = (string) (auth('admin')->id() ?? auth()->id() ?? '');

    Flux::modal('create-order-modal')->show();
};

// Action: Store New Payment Order
$createPaymentOrder = function () {
    $this->validate([
        'application_id'     => ['required', 'exists:loc_clearances,id', 'unique:payment_orders,application_id'],
        'approved_by'        => ['required'],
        'category_type'      => ['required', 'string'],
        'project_cost'       => ['required', 'numeric', 'min:0'],
        'base_clearance_fee' => ['required', 'numeric', 'min:0'],
        'excess_fee'         => ['nullable', 'numeric', 'min:0'],
        'processing_fee'     => ['required', 'numeric', 'min:0'],
        'inspection_fee'     => ['required', 'numeric', 'min:0'],
        'surcharge_fee'      => ['nullable', 'numeric', 'min:0'],
    ], [
        'application_id.required' => 'Please select a locational clearance application.',
        'application_id.unique'   => 'A payment order already exists for this application.',
        'approved_by.required'    => 'Please select the approving officer.',
    ]);

    $totalClearanceFee = (float) $this->base_clearance_fee + (float) ($this->excess_fee ?? 0);
    $grandTotal = $totalClearanceFee + (float) $this->processing_fee + (float) $this->inspection_fee + (float) ($this->surcharge_fee ?? 0);

    $nextId = (PaymentOrder::max('id') ?? 0) + 1;
    $assessmentNo = 'PO-' . date('Y') . '-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);

    PaymentOrder::create([
        'application_id'      => $this->application_id,
        'assessment_no'       => $assessmentNo,
        'project_cost'        => $this->project_cost,
        'category_type'       => $this->category_type,
        'base_clearance_fee'  => $this->base_clearance_fee,
        'excess_cost'         => 0.00,
        'excess_fee'          => $this->excess_fee ?? 0.00,
        'total_clearance_fee' => $totalClearanceFee,
        'processing_fee'      => $this->processing_fee,
        'inspection_fee'      => $this->inspection_fee,
        'surcharge_fee'       => $this->surcharge_fee ?? 0.00,
        'grand_total'         => $grandTotal,
        'remarks'             => $this->remarks,
        'assessed_by'         => auth('admin')->id() ?? auth()->id(),
        'approved_by'         => $this->approved_by,
        'status'              => 'pending',
    ]);

    Flux::modal('create-order-modal')->close();
    Flux::toast(text: 'Payment Order assessment created successfully.', variant: 'success');
};

// Action: Open Record Payment Modal
$openPaymentModal = function ($orderId) {
    $this->selected_order_id = $orderId;
    $order = PaymentOrder::findOrFail($orderId);
    $this->or_no = $order->or_no ?? '';
    $this->payment_date = $order->payment_date?->format('Y-m-d') ?? now()->format('Y-m-d');
    Flux::modal('payment-modal')->show();
};

// Action: Save Official Receipt Details
$savePayment = function () {
    $this->validate([
        'or_no'        => ['required', 'string', 'max:50'],
        'payment_date' => ['required', 'date'],
    ], [
        'or_no.required'        => 'Please enter the Treasury Official Receipt (OR) number.',
        'payment_date.required' => 'Please select the payment date.',
    ]);

    $order = PaymentOrder::findOrFail($this->selected_order_id);
    $order->update([
        'or_no'        => $this->or_no,
        'payment_date' => $this->payment_date,
        'status'       => 'paid',
    ]);

    // Update clearance amount paid
    $order->locClearance?->update([
        'amount_paid' => $order->grand_total,
    ]);

    $this->selected_order_id = null;
    Flux::modal('payment-modal')->close();
    Flux::toast(text: 'Treasury payment recorded successfully!', variant: 'success');
};

// Action: Open Print Modal
$openPrintModal = function ($orderId) {
    $this->print_order_id = $orderId;
    Flux::modal('print-modal')->show();
};

?>

<div class="space-y-5 sm:space-y-6">
    <!-- Header with "New Payment Order" Button -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <flux:heading size="xl" level="1" class="text-xl sm:text-2xl font-bold">Payment Orders</flux:heading>
            <flux:subheading class="text-xs sm:text-sm">Manage locational clearance assessments, print orders of
                payment, and record Treasury ORs.</flux:subheading>
        </div>

        <flux:button wire:click="openCreateModal" variant="primary" size="sm" icon="plus">
            New Payment Order
        </flux:button>
    </div>

    <!-- Filters matching Digital Logbook -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-2.5 sm:gap-3">
        <div class="sm:col-span-2 lg:col-span-5">
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass"
                placeholder="Search assessment no, applicant, OR no..." clearable />
        </div>

        <div class="lg:col-span-3">
            <flux:select wire:model.live="status" placeholder="Status">
                <flux:select.option value="all">All Statuses</flux:select.option>
                <flux:select.option value="pending">Pending</flux:select.option>
                <flux:select.option value="paid">Paid</flux:select.option>
                <flux:select.option value="cancelled">Cancelled</flux:select.option>
            </flux:select>
        </div>

        <div class="grid grid-cols-2 gap-2 sm:col-span-2 lg:col-span-4">
            <flux:input type="date" wire:model.live="dateFrom" />
            <flux:input type="date" wire:model.live="dateTo" />
        </div>
    </div>

    <!-- Partials -->
    @include('livewire.admin.payment.partials.table')
    @include('livewire.admin.payment.partials.create-modal')
    @include('livewire.admin.payment.partials.payment-modal')
    @include('livewire.admin.payment.partials.print-modal')
</div>