<?php

use function Livewire\Volt\{layout, title, state, usesPagination, computed, updated};
use App\Models\LocClearance;
use App\Models\PaymentOrder;
use App\Models\Inspection;
use Carbon\Carbon;
use Flux\Flux;

layout('components.layouts.admin');
title('Export Transaction Logs');

usesPagination();

state([
    'module'   => 'applications', // 'applications', 'payments', 'inspections'
    'status'   => 'all',
    'search'   => '',
    'period'   => 'this_month',
    'dateFrom' => now()->startOfMonth()->format('Y-m-d'),
    'dateTo'   => now()->endOfMonth()->format('Y-m-d'),
]);

updated([
    'period' => function ($value) {
        match ($value) {
            'this_month' => [
                $this->dateFrom = now()->startOfMonth()->format('Y-m-d'),
                $this->dateTo   = now()->endOfMonth()->format('Y-m-d'),
            ],
            'last_month' => [
                $this->dateFrom = now()->subMonth()->startOfMonth()->format('Y-m-d'),
                $this->dateTo   = now()->subMonth()->endOfMonth()->format('Y-m-d'),
            ],
            'this_quarter' => [
                $this->dateFrom = now()->firstOfQuarter()->format('Y-m-d'),
                $this->dateTo   = now()->lastOfQuarter()->format('Y-m-d'),
            ],
            'this_year' => [
                $this->dateFrom = now()->startOfYear()->format('Y-m-d'),
                $this->dateTo   = now()->endOfYear()->format('Y-m-d'),
            ],
            'custom' => null,
        };
        $this->resetPage();
    },
    'module' => function () {
        $this->status = 'all';
        $this->resetPage();
    },
    'search'   => fn () => $this->resetPage(),
    'status'   => fn () => $this->resetPage(),
    'dateFrom' => fn () => $this->resetPage(),
    'dateTo'   => fn () => $this->resetPage(),
]);

$setPeriod = function (string $period) {
    $this->period = $period;
    match ($period) {
        'this_month' => [
            $this->dateFrom = now()->startOfMonth()->format('Y-m-d'),
            $this->dateTo = now()->endOfMonth()->format('Y-m-d'),
        ],
        'last_month' => [
            $this->dateFrom = now()->subMonth()->startOfMonth()->format('Y-m-d'),
            $this->dateTo = now()->subMonth()->endOfMonth()->format('Y-m-d'),
        ],
        'this_quarter' => [
            $this->dateFrom = now()->firstOfQuarter()->format('Y-m-d'),
            $this->dateTo = now()->lastOfQuarter()->format('Y-m-d'),
        ],
        'this_year' => [
            $this->dateFrom = now()->startOfYear()->format('Y-m-d'),
            $this->dateTo = now()->endOfYear()->format('Y-m-d'),
        ],
        'custom' => null,
    };
    $this->resetPage();
};

// Computed Master Query
$query = computed(function () {
    $from = Carbon::parse($this->dateFrom)->startOfDay();
    $to = Carbon::parse($this->dateTo)->endOfDay();

    if ($this->module === 'applications') {
        return LocClearance::query()
            ->with(['client'])
            ->whereBetween('created_at', [$from, $to])
            ->when($this->status !== 'all', fn ($q) => $q->where('status', $this->status))
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('application_no', 'like', '%' . $this->search . '%')
                        ->orWhere('applicant_name', 'like', '%' . $this->search . '%')
                        ->orWhere('project_type', 'like', '%' . $this->search . '%')
                        ->orWhere('or_number', 'like', '%' . $this->search . '%');
                });
            })
            ->latest();
    }

    if ($this->module === 'payments') {
        return PaymentOrder::query()
            ->with(['locClearance.client', 'assessor', 'approver'])
            ->whereBetween('created_at', [$from, $to])
            ->when($this->status !== 'all', fn ($q) => $q->where('status', $this->status))
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('assessment_no', 'like', '%' . $this->search . '%')
                        ->orWhere('or_no', 'like', '%' . $this->search . '%')
                        ->orWhereHas('locClearance', function ($cq) {
                            $cq->where('application_no', 'like', '%' . $this->search . '%')
                               ->orWhere('applicant_name', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->latest();
    }

    // Inspections
    return Inspection::query()
        ->with(['locClearance.client', 'inspector'])
        ->whereBetween('scheduled_date', [$this->dateFrom, $this->dateTo])
        ->when($this->status !== 'all', fn ($q) => $q->where('compliance_status', $this->status))
        ->when($this->search, function ($q) {
            $q->where(function ($sub) {
                $sub->where('inspection_no', 'like', '%' . $this->search . '%')
                    ->orWhere('findings', 'like', '%' . $this->search . '%')
                    ->orWhereHas('locClearance', function ($cq) {
                        $cq->where('application_no', 'like', '%' . $this->search . '%')
                           ->orWhere('applicant_name', 'like', '%' . $this->search . '%');
                    });
            });
        })
        ->latest('scheduled_date');
});

$previewData = computed(fn () => (clone $this->query)->paginate(10));
$totalMatching = computed(fn () => (clone $this->query)->count());

// Action: Export CSV
$exportCsv = function () {
    $records = (clone $this->query)->get();

    if ($records->isEmpty()) {
        Flux::toast(text: 'No records to export in this range.', variant: 'warning');
        return;
    }

    $filename = sprintf('mpdc_%s_%s_to_%s.csv', $this->module, $this->dateFrom, $this->dateTo);

    return response()->streamDownload(function () use ($records) {
        $handle = fopen('php://output', 'w');
        fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM

        if ($this->module === 'applications') {
            fputcsv($handle, ['App No', 'Applicant', 'Corporation', 'Project Type', 'Location', 'Land Use', 'Amount Paid', 'OR No', 'Status', 'Channel', 'Date Filed']);
            foreach ($records as $r) {
                fputcsv($handle, [
                    $r->application_no ?? 'Unassigned', $r->applicant_name, $r->corporation_name ?? 'N/A',
                    $r->project_type ?? 'N/A', $r->project_location ?? 'N/A', $r->existing_land_use ?? 'N/A',
                    $r->amount_paid ? number_format($r->amount_paid, 2) : '0.00', $r->or_number ?? 'N/A',
                    strtoupper($r->status ?? 'pending'), $r->is_walk_in ? 'Walk-in' : 'Online', $r->created_at?->format('Y-m-d H:i:s'),
                ]);
            }
        } elseif ($this->module === 'payments') {
            fputcsv($handle, ['Assessment No', 'App No', 'Applicant', 'Category', 'Project Cost', 'Base Fee', 'Excess Fee', 'Processing Fee', 'Inspection Fee', 'Surcharge', 'Grand Total', 'OR No', 'Paid Date', 'Status']);
            foreach ($records as $r) {
                fputcsv($handle, [
                    $r->assessment_no, $r->locClearance?->application_no ?? 'N/A', $r->locClearance?->applicant_name,
                    $r->category_type ?? 'N/A', number_format($r->project_cost, 2), number_format($r->base_clearance_fee, 2),
                    number_format($r->excess_fee, 2), number_format($r->processing_fee, 2), number_format($r->inspection_fee, 2),
                    number_format($r->surcharge_fee, 2), number_format($r->grand_total, 2), $r->or_no ?? 'UNPAID',
                    $r->payment_date?->format('Y-m-d') ?? 'N/A', strtoupper($r->status),
                ]);
            }
        } else {
            fputcsv($handle, ['Inspection No', 'App No', 'Applicant', 'Location', 'Type', 'Scheduled Date', 'Time', 'Inspector', 'Compliance', 'Status', 'Findings']);
            foreach ($records as $r) {
                fputcsv($handle, [
                    $r->inspection_no, $r->locClearance?->application_no ?? 'N/A', $r->locClearance?->applicant_name,
                    $r->locClearance?->project_location ?? 'N/A', $r->inspection_type, $r->scheduled_date?->format('Y-m-d'),
                    $r->scheduled_time ?? 'N/A', $r->inspector?->full_name ?? $r->inspector?->name ?? 'Unassigned',
                    strtoupper(str_replace('_', ' ', $r->compliance_status)), strtoupper($r->status), $r->findings ?? 'None',
                ]);
            }
        }
        fclose($handle);
    }, $filename, [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => "attachment; filename=\"{$filename}\"",
    ]);
};

?>

<div class="space-y-5 sm:space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <flux:heading size="xl" level="1" class="text-xl sm:text-2xl font-bold">Export Transaction Logs
            </flux:heading>
            <flux:subheading class="text-xs sm:text-sm">Filter, preview, and download official CSV logs for clearances,
                payments, and site inspections.</flux:subheading>
        </div>

        <flux:button wire:click="exportCsv" variant="primary" size="sm" icon="arrow-down-tray">
            Download ({{ number_format($this->totalMatching) }})
        </flux:button>
    </div>

    <!-- Partials -->
    @include('livewire.admin.reports.partials.export-toolbar')
    @include('livewire.admin.reports.partials.export-preview')
</div>