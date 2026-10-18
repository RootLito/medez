<?php

use App\Models\LocClearance;
use Barryvdh\DomPDF\Facade\Pdf;
use function Livewire\Volt\{layout, title, state, mount};

layout('components.layouts.admin');
title('Application Form - Admin Portal');

state([
    'clearance' => null,
    'pdfDataUri' => '',
]);

mount(function (LocClearance $clearance) {
    $this->clearance = $clearance;

    $pdf = Pdf::loadView('pdf.locational-clearance', [
        'clearance' => $clearance,
    ])->setPaper('a4', 'portrait');

    $this->pdfDataUri = 'data:application/pdf;base64,' . base64_encode($pdf->output());
});

?>

<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <flux:heading size="xl" level="1">Encode Walk-In Application</flux:heading>
            <flux:subheading>Manually encode an in-person Locational Clearance application into the system.
            </flux:subheading>
        </div>
        <flux:button href="{{ route('admin.applications.walkin-index') }}" wire:navigate variant="subtle"
            icon="arrow-left">
            Back to Walk-Ins
        </flux:button>
    </div>
    <iframe src="{{ $pdfDataUri }}" class="w-full h-[850px] border-0 rounded" type="application/pdf">
    </iframe>
</div>

