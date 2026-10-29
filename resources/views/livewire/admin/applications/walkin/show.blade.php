<?php

use function Livewire\Volt\{layout, title, state, mount};
use App\Models\LocClearance;
use App\Models\AppStatus;
use Flux\Flux;

layout('components.layouts.admin');
title('Walk-In Application Details');

state([
    'clearance' => null,
    'status' => '',
    'remarks' => '',
]);

mount(function (LocClearance $clearance) {
    if (! $clearance->is_walk_in) {
        abort(404);
    }
    $this->clearance = $clearance;
    $this->status = $clearance->status ?? 'resubmit';
});

$submitDecision = function () {
    $this->validate([
        'status' => ['required', 'in:resubmit,approve,approved,rejected'],
        'remarks' => ['nullable', 'string', 'max:1000'],
    ]);

    $dbStatus = $this->status === 'approved' ? 'approve' : $this->status;

    // 1. Create status log entry
    AppStatus::create([
        'application_id' => $this->clearance->id,
        'admin_id' => auth('admin')->id() ?? auth()->id(),
        'status' => $dbStatus,
        'remarks' => $this->remarks,
        'is_read' => true, // Admin decisions are already acknowledged by admin
    ]);

    // 2. Update clearance status
    $this->clearance->update([
        'status' => $dbStatus,
    ]);

    $this->remarks = '';
    $this->clearance->refresh();

    Flux::toast(text: 'Walk-in application decision updated successfully!', variant: 'success');
};

?>

<div class="space-y-6" x-data="{ modalOpen: false, activeImage: '' }">
    <!-- Header & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 print:hidden">
        <div>
            <div class="flex items-center gap-3">
                <flux:heading size="xl" level="1">
                    Walk-In App #{{ $clearance->application_no ?? str_pad($clearance->id, 5, '0', STR_PAD_LEFT) }}
                </flux:heading>
                @php
                $rawStatus = strtolower($clearance->status ?? 'pending');
                $statusColor = match($rawStatus) {
                'approve', 'approved' => 'green',
                'resubmit' => 'yellow',
                'pending' => 'blue',
                'rejected' => 'red',
                default => 'zinc',
                };
                $displayStatus = match($rawStatus) {
                'approve', 'approved' => 'Approved',
                'resubmit' => 'Resubmit',
                'pending' => 'Pending',
                'rejected' => 'Rejected',
                default => ucfirst($rawStatus),
                };
                @endphp
                <flux:badge size="sm" :color="$statusColor">
                    {{ $displayStatus }}
                </flux:badge>
                <flux:badge size="sm" color="zinc">Walk-In Record</flux:badge>
            </div>
            <flux:subheading>Encoded on {{ $clearance->created_at->format('M d, Y \a\t h:i A') }}</flux:subheading>
        </div>

        <div class="flex items-center gap-2">
            <flux:button href="{{ route('admin.applications.walkin-index') }}" wire:navigate variant="subtle" size="sm"
                icon="arrow-left">
                Back to Walk-Ins
            </flux:button>
            <flux:button href="{{ route('admin.applications.walkin-edit', $clearance->id) }}" wire:navigate
                variant="primary" size="sm" icon="pencil-square">
                Edit Record
            </flux:button>
        </div>
    </div>

    <!-- Main Side-by-Side Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">

        <!-- LEFT 2 COLUMNS: Walk-In Form Details -->
        <div class="lg:col-span-2 space-y-6 text-zinc-900 dark:text-zinc-100 text-sm font-sans print:col-span-3">

            <!-- Card 0: Official Processing / O.R. Details -->
            <div
                class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-6 shadow-sm space-y-4">
                <h3
                    class="text-sm font-bold uppercase tracking-wider text-zinc-500 border-b border-zinc-200 dark:border-zinc-800 pb-2">
                    Official Processing & O.R. Information
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                    <div>
                        <span class="text-zinc-400 block font-bold uppercase mb-1">Application No.</span>
                        <span class="font-mono font-semibold text-sm">{{ $clearance->application_no ?? 'Unassigned'
                            }}</span>
                    </div>
                    <div>
                        <span class="text-zinc-400 block font-bold uppercase mb-1">O.R. Number</span>
                        <span class="font-mono font-semibold text-sm">{{ $clearance->or_number ?? 'N/A' }}</span>
                    </div>
                    <div>
                        <span class="text-zinc-400 block font-bold uppercase mb-1">Amount Paid</span>
                        <span class="font-mono font-semibold text-sm">{{ $clearance->amount_paid ? '₱' .
                            number_format($clearance->amount_paid, 2) : 'N/A' }}</span>
                    </div>
                </div>
            </div>

            <!-- Card 1: Applicant & Corporation Information -->
            <div
                class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-6 shadow-sm space-y-4">
                <h3
                    class="text-sm font-bold uppercase tracking-wider text-zinc-500 border-b border-zinc-200 dark:border-zinc-800 pb-2">
                    1 & 2. Applicant & Corporation
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <div class="font-bold text-xs uppercase text-zinc-500 mb-1">Name of Applicant</div>
                        <div class="font-medium text-base">{{ $clearance->applicant_name }}</div>
                    </div>
                    <div>
                        <div class="font-bold text-xs uppercase text-zinc-500 mb-1">Name of Corporation</div>
                        <div class="font-medium text-base">{{ $clearance->corporation_name ?? 'N/A' }}</div>
                    </div>
                </div>
            </div>

            <!-- Card 2: Addresses & Representatives -->
            <div
                class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-6 shadow-sm space-y-4">
                <h3
                    class="text-sm font-bold uppercase tracking-wider text-zinc-500 border-b border-zinc-200 dark:border-zinc-800 pb-2">
                    3 to 6. Addresses & Representatives
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <div class="font-bold text-xs uppercase text-zinc-500 mb-1">3. Address / Tel. No. of Applicant
                        </div>
                        <div class="font-medium">{{ $clearance->representative_address_tel ?? 'N/A' }}</div>
                    </div>
                    <div>
                        <div class="font-bold text-xs uppercase text-zinc-500 mb-1">4. Address / Tel. No. of Corporation
                        </div>
                        <div class="font-medium">{{ $clearance->corporation_address_tel ?? 'N/A' }}</div>
                    </div>
                    <div>
                        <div class="font-bold text-xs uppercase text-zinc-500 mb-1">5. Authorized Representative</div>
                        <div class="font-medium">{{ $clearance->authorized_representative_name ?? 'N/A' }}</div>
                    </div>
                    <div>
                        <div class="font-bold text-xs uppercase text-zinc-500 mb-1">6. Representative Address / Tel.
                        </div>
                        <div class="font-medium">{{ $clearance->authorized_representative_address_tel ?? 'N/A' }}</div>
                    </div>
                </div>
            </div>

            <!-- Card 3: Project Specs & Location -->
            <div
                class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-6 shadow-sm space-y-4">
                <h3
                    class="text-sm font-bold uppercase tracking-wider text-zinc-500 border-b border-zinc-200 dark:border-zinc-800 pb-2">
                    7 to 10. Project Specs & Location
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <div class="font-bold text-xs uppercase text-zinc-500 mb-1">7. Project Type</div>
                        <div class="font-medium">{{ $clearance->project_type ?? 'N/A' }}</div>
                    </div>
                    <div>
                        <div class="font-bold text-xs uppercase text-zinc-500 mb-1">8. Project Nature</div>
                        <div class="font-medium">{{ $clearance->project_nature ?? 'N/A' }}</div>
                    </div>
                    <div class="sm:col-span-2">
                        <div class="font-bold text-xs uppercase text-zinc-500 mb-1">9. Project Location</div>
                        <div class="font-medium">{{ $clearance->project_location ?? 'N/A' }}</div>
                    </div>
                    <div class="sm:col-span-2">
                        <div class="font-bold text-xs uppercase text-zinc-500 mb-1">10. Project Area</div>
                        <div class="grid grid-cols-3 gap-2 text-xs pt-1">
                            <div
                                class="p-2.5 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700">
                                <span class="text-zinc-500 block">Lot Area</span>
                                <span class="font-mono font-semibold text-sm">{{ $clearance->lot_area_sqm ?
                                    number_format($clearance->lot_area_sqm, 2) . ' sq.m' : 'N/A' }}</span>
                            </div>
                            <div
                                class="p-2.5 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700">
                                <span class="text-zinc-500 block">Building Area</span>
                                <span class="font-mono font-semibold text-sm">{{ $clearance->building_area_sqm ?
                                    number_format($clearance->building_area_sqm, 2) . ' sq.m' : 'N/A' }}</span>
                            </div>
                            <div
                                class="p-2.5 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700">
                                <span class="text-zinc-500 block">Perimeter</span>
                                <span class="font-mono font-semibold text-sm">{{ $clearance->perimeter_m ?
                                    number_format($clearance->perimeter_m, 2) . ' m' : 'N/A' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 4: Land Rights & Land Use -->
            <div
                class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-6 shadow-sm space-y-4 text-xs">
                <h3
                    class="text-sm font-bold uppercase tracking-wider text-zinc-500 border-b border-zinc-200 dark:border-zinc-800 pb-2">
                    11 to 13. Land Rights, Tenure & Land Use
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <div class="font-bold uppercase text-zinc-500 mb-1">11. Right Over Land</div>
                        <div class="capitalize text-sm font-medium">
                            {{ $clearance->right_over_land ?? 'N/A' }}
                            @if($clearance->right_over_land === 'others' && $clearance->right_over_land_specify)
                            <span class="text-zinc-500">({{ $clearance->right_over_land_specify }})</span>
                            @endif
                        </div>
                    </div>
                    <div>
                        <div class="font-bold uppercase text-zinc-500 mb-1">12. Project Tenure</div>
                        <div class="capitalize text-sm font-medium">{{ $clearance->project_tenure ?? 'N/A' }}</div>
                    </div>
                </div>

                <div class="pt-2 border-t border-zinc-100 dark:border-zinc-800 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <div class="font-bold uppercase text-zinc-500 mb-1">13. Existing Land Use</div>
                        <div class="capitalize text-sm font-medium">
                            {{ str_replace('_', ' ', $clearance->existing_land_use ?? 'N/A') }}
                        </div>
                    </div>
                    <div>
                        <div class="font-bold uppercase text-zinc-500 mb-1">Tenancy Status</div>
                        <div>
                            @if($clearance->is_tenanted)
                            <flux:badge size="sm" color="amber">Currently Tenanted</flux:badge>
                            @else
                            <flux:badge size="sm" color="zinc">Not Tenanted</flux:badge>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 5: Cost, Legal & Release -->
            <div
                class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-6 shadow-sm space-y-4 text-xs">
                <h3
                    class="text-sm font-bold uppercase tracking-wider text-zinc-500 border-b border-zinc-200 dark:border-zinc-800 pb-2">
                    14 to 17. Cost, Legal & Release
                </h3>
                <div>
                    <div class="font-bold uppercase text-zinc-500 mb-1">14. Project Cost</div>
                    <div class="font-mono text-base font-semibold text-zinc-900 dark:text-zinc-100">
                        {{ $clearance->project_cost ? '₱' . number_format($clearance->project_cost, 2) : 'N/A' }}
                    </div>
                </div>

                <div class="pt-2 border-t border-zinc-100 dark:border-zinc-800">
                    <div class="font-bold uppercase text-zinc-500 mb-1">17. Preferred Mode of Release</div>
                    <div class="text-sm font-medium capitalize">
                        {{ str_replace('_', ' ', $clearance->preferred_mode_of_release ?? 'Pick-up') }}
                    </div>
                </div>
            </div>

        </div>

        <!-- RIGHT 1 COLUMN: Requirements & Review Decision -->
        <div class="lg:col-span-1 space-y-6 print:hidden">

            <!-- Uploaded Requirements Inspection Card -->
            <div
                class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5 shadow-sm space-y-4">
                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-zinc-900 dark:text-zinc-100">Scanned
                        Requirements</h4>
                    <p class="text-[11px] text-zinc-500 mt-0.5">Click any image to inspect or zoom.</p>
                </div>

                <hr class="border-zinc-200 dark:border-zinc-800" />

                @php
                $reqs = is_array($clearance->requirements) ? $clearance->requirements :
                (json_decode($clearance->requirements, true) ?? []);
                $docCategories = [
                ['title' => '1. Basic Docs', 'key' => 'basic_docs_files'],
                ['title' => '2. Proof of Land', 'key' => 'land_right_files'],
                ['title' => '3. Plans & Specs', 'key' => 'plans_specs_files'],
                ['title' => '4. Local Clearances', 'key' => 'local_clearances_files'],
                ['title' => '5. Special Reqs', 'key' => 'special_req_files'],
                ];
                @endphp

                <div class="space-y-4">
                    @foreach ($docCategories as $cat)
                    <div class="space-y-1.5">
                        <div
                            class="flex justify-between items-center text-xs font-medium text-zinc-700 dark:text-zinc-300">
                            <span>{{ $cat['title'] }}</span>
                            <span class="text-[10px] text-zinc-400">({{ count($reqs[$cat['key']] ?? []) }})</span>
                        </div>

                        <div class="grid grid-cols-4 gap-2">
                            @forelse ($reqs[$cat['key']] ?? [] as $file)
                            @php
                            $fileUrl = asset('storage/' . $file);
                            $isPdf = \Illuminate\Support\Str::endsWith(strtolower($file), '.pdf');
                            @endphp

                            @if ($isPdf)
                            <a href="{{ $fileUrl }}" target="_blank"
                                class="h-12 flex flex-col items-center justify-center p-1 rounded border border-zinc-200 dark:border-zinc-700 text-red-500 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition text-[10px]">
                                <flux:icon.document-text class="w-5 h-5" />
                                <span>PDF</span>
                            </a>
                            @else
                            <img src="{{ $fileUrl }}" @click="modalOpen = true; activeImage = '{{ $fileUrl }}'"
                                class="h-12 w-full object-cover rounded border border-zinc-200 dark:border-zinc-700 cursor-pointer hover:opacity-80 transition"
                                alt="Requirement Preview">
                            @endif
                            @empty
                            <span class="text-[11px] text-zinc-400 italic col-span-4">No files attached.</span>
                            @endforelse
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Decision Card -->
            <div
                class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5 shadow-sm space-y-5">
                <div>
                    <h3 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">Review Decision</h3>
                    <p class="text-xs text-zinc-500 mt-0.5">Set the application status and update records.</p>
                </div>

                <hr class="border-zinc-200 dark:border-zinc-800" />

                <!-- Decision Form -->
                <form wire:submit="submitDecision" class="space-y-4">
                    <div class="space-y-1.5">
                        <label class="block text-xs font-medium text-zinc-700 dark:text-zinc-300">Status Decision <span
                                class="text-red-500">*</span></label>
                        <flux:select wire:model="status" required>
                            <flux:select.option value="approve">Approved</flux:select.option>
                            <flux:select.option value="resubmit">Resubmit (Needs Revision)</flux:select.option>
                            <flux:select.option value="rejected">Rejected</flux:select.option>
                        </flux:select>
                        @error('status') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-medium text-zinc-700 dark:text-zinc-300">Remarks /
                            Feedback</label>
                        <flux:textarea wire:model="remarks" rows="3" placeholder="Provide notes or instructions..." />
                        @error('remarks') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <flux:button type="submit" variant="primary" class="w-full" icon="check"
                        wire:loading.attr="disabled">
                        Update Decision
                    </flux:button>
                </form>
            </div>

            <!-- Decision History Timeline -->
            @php
            $statusLogs = \App\Models\AppStatus::where('application_id', $clearance->id)->latest()->get();
            @endphp

            @if ($statusLogs->count() > 0)
            <div
                class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5 shadow-sm space-y-3">
                <h4 class="text-xs font-bold uppercase tracking-wider text-zinc-900 dark:text-zinc-100">Status History
                </h4>
                <div class="space-y-3 max-h-60 overflow-y-auto pr-1">
                    @foreach ($statusLogs as $log)
                    @php
                    $rawLogStatus = strtolower($log->status);
                    $logColor = match($rawLogStatus) {
                    'approve', 'approved' => 'green',
                    'resubmit' => 'yellow',
                    'pending' => 'blue',
                    default => 'red',
                    };
                    $displayLogStatus = match($rawLogStatus) {
                    'approve', 'approved' => 'Approved',
                    'resubmit' => 'Resubmit',
                    'pending' => 'Pending',
                    'rejected' => 'Rejected',
                    default => ucfirst($rawLogStatus),
                    };
                    @endphp
                    <div
                        class="p-2.5 rounded-lg bg-zinc-50 dark:bg-zinc-800/60 border border-zinc-200 dark:border-zinc-700/60 space-y-1 text-xs">
                        <div class="flex justify-between items-center">
                            <flux:badge size="sm" :color="$logColor">
                                {{ $displayLogStatus }}
                            </flux:badge>
                            <span class="text-[10px] text-zinc-400">{{ $log->created_at->diffForHumans() }}</span>
                        </div>
                        @if($log->remarks)
                        <p class="text-zinc-600 dark:text-zinc-300 text-[11px] mt-1">{{ $log->remarks }}</p>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

        </div>

    </div>

    <!-- Full-Screen Image Modal Preview -->
    <div x-show="modalOpen" x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4" style="display: none;"
        x-data="{ zoom: 1, posX: 0, posY: 0, dragging: false, startX: 0, startY: 0 }">

        <div class="relative max-w-5xl max-h-[95vh] w-full h-full flex items-center justify-center overflow-hidden"
            @wheel.prevent="zoom = Math.max(1, Math.min(4, zoom + ($event.deltaY < 0 ? 0.2 : -0.2))); if(zoom === 1) { posX = 0; posY = 0; }"
            @mousedown="dragging = true; startX = $event.clientX - posX; startY =$event.clientY - posY;"
            @mouseup="dragging = false"
            @mousemove="if(dragging && zoom > 1) { posX = $event.clientX - startX; posY =$event.clientY - startY; }"
            @mouseleave="dragging = false">

            <!-- Controls Toolbar -->
            <div
                class="absolute top-4 right-4 z-50 flex items-center gap-2 bg-zinc-900/80 backdrop-blur-md border border-zinc-700 p-1.5 rounded-lg text-white">
                <button @click="zoom = Math.min(4, zoom + 0.5)"
                    class="px-2 py-1 hover:bg-zinc-700 rounded text-xs font-bold" title="Zoom In">+</button>
                <button @click="zoom = Math.max(1, zoom - 0.5); if(zoom === 1) { posX = 0; posY = 0; }"
                    class="px-2 py-1 hover:bg-zinc-700 rounded text-xs font-bold" title="Zoom Out">-</button>
                <button @click="zoom = 1; posX = 0; posY = 0;" class="px-2 py-1 hover:bg-zinc-700 rounded text-xs"
                    title="Reset">Reset</button>
                <span class="text-xs px-1 text-zinc-400" x-text="Math.round(zoom * 100) + '%'"></span>
                <div class="w-px h-4 bg-zinc-700 mx-1"></div>
                <button @click="modalOpen = false; zoom = 1; posX = 0; posY = 0;"
                    class="px-2.5 py-1 hover:bg-red-600 rounded text-xs font-bold bg-zinc-800">✕ Close</button>
            </div>

            <img :src="activeImage"
                :style="`transform: translate(${posX}px, ${posY}px) scale(${zoom}); cursor: ${zoom > 1 ? (dragging ? 'grabbing' : 'grab') : 'default'}; transition: transform 0.05s ease-out;`"
                class="max-h-[85vh] max-w-full object-contain rounded-lg shadow-2xl select-none" @click.stop
                alt="Enlarged Requirement Preview">
        </div>
    </div>
</div>