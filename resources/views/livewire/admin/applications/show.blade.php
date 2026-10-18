<?php

use function Livewire\Volt\{layout, title, state, mount};
use App\Models\LocClearance;

layout('components.layouts.admin');
title('Application Details');

state([
    'clearance' => null,
]);

mount(function (LocClearance $clearance) {
    $this->clearance = $clearance->load('client');
});

?>

<div class="space-y-6" x-data="{ modalOpen: false, activeImage: '' }">
    <!-- Flux Header & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 print:hidden">
        <div>
            <flux:heading size="xl" level="1">Application Details</flux:heading>
            <flux:subheading>Viewing official locational clearance application record.</flux:subheading>
        </div>

        <div class="flex items-center gap-2">
            <flux:button icon="arrow-left" variant="subtle" href="{{ route('admin.applications.pending') }}" wire:navigate>
                Back to Pending List
            </flux:button>
        </div>
    </div>

    <!-- Main Side-by-Side Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">

        <!-- LEFT 2 COLUMNS: Details wrapped in Card Containers -->
        <div class="lg:col-span-2 space-y-6 text-zinc-900 dark:text-zinc-100 text-sm font-sans print:col-span-3">

            <!-- Card 1: Applicant & Corporation Information -->
            <div
                class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-6 shadow-sm space-y-4">
                <h3
                    class="text-sm font-bold uppercase tracking-wider text-zinc-500 border-b border-zinc-200 dark:border-zinc-800 pb-2">
                    1 & 2. Applicant & Corporation</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <div class="font-bold text-xs uppercase text-zinc-500 mb-1">Name of Applicant <span
                                class="font-normal text-[10px] lowercase">(Last, First, Middle)</span></div>
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
                    3 to 6. Addresses & Representatives</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <div class="font-bold text-xs uppercase text-zinc-500 mb-1">3. Address/Telephone Representative
                        </div>
                        <div class="font-medium">
                            {{ $clearance->applicant_address ?? ($clearance->applicant_phone ?? 'N/A') }}</div>
                    </div>
                    <div>
                        <div class="font-bold text-xs uppercase text-zinc-500 mb-1">4. Address/Tel. of Corporation</div>
                        <div class="font-medium">{{ $clearance->corporation_address ?? 'N/A' }}</div>
                    </div>
                    <div>
                        <div class="font-bold text-xs uppercase text-zinc-500 mb-1">5. Name of Authorized Representative
                        </div>
                        <div class="font-medium">{{ $clearance->authorized_rep_name ?? 'N/A' }}</div>
                    </div>
                    <div>
                        <div class="font-bold text-xs uppercase text-zinc-500 mb-1">6. Address/Tel. of Authorized
                            Representative</div>
                        <div class="font-medium">{{ $clearance->authorized_rep_address ?? 'N/A' }}</div>
                    </div>
                </div>
            </div>

            <!-- Card 3: Project Type, Nature & Location -->
            <div
                class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-6 shadow-sm space-y-4">
                <h3
                    class="text-sm font-bold uppercase tracking-wider text-zinc-500 border-b border-zinc-200 dark:border-zinc-800 pb-2">
                    7 to 10. Project Specs & Location</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <div class="font-bold text-xs uppercase text-zinc-500 mb-1">7. Project Type</div>
                        <div class="font-medium">{{ $clearance->project_type ?? 'N/A' }}</div>
                    </div>
                    <div>
                        <div class="font-bold text-xs uppercase text-zinc-500 mb-1">8. Project Nature</div>
                        <div class="font-medium">{{ $clearance->project_nature ?? 'N/A' }}</div>
                    </div>
                    <div>
                        <div class="font-bold text-xs uppercase text-zinc-500 mb-1">9. Project Location</div>
                        <div class="font-medium">{{ $clearance->project_location ?? 'N/A' }}</div>
                    </div>
                    <div>
                        <div class="font-bold text-xs uppercase text-zinc-500 mb-1">10. Project Area <span
                                class="font-normal lowercase">(sq.m.)</span></div>
                        <div class="space-y-1 text-xs">
                            <div class="flex justify-between"><span>Lot Area:</span> <span
                                    class="font-mono font-medium">{{ $clearance->lot_area ? $clearance->lot_area . ' Sq.m.' : 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between"><span>Building Area:</span> <span
                                    class="font-mono font-medium">{{ $clearance->building_area ? $clearance->building_area . ' Sq.m.' : 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between"><span>Perimeter:</span> <span
                                    class="font-mono font-medium">{{ $clearance->perimeter ? $clearance->perimeter . ' m.' : 'N/A' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 4: Land Rights, Tenure & Land Use -->
            <div
                class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-6 shadow-sm space-y-4 text-xs">
                <h3
                    class="text-sm font-bold uppercase tracking-wider text-zinc-500 border-b border-zinc-200 dark:border-zinc-800 pb-2">
                    11 to 13. Land Rights, Tenure & Land Use</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <div class="font-bold uppercase text-zinc-500 mb-1">11. Right Over Land</div>
                        <div class="grid grid-cols-2 gap-1">
                            <label class="flex items-center gap-1.5"><input type="checkbox" disabled
                                    @checked($clearance->right_over_land === 'owner') /> Owner</label>
                            <label class="flex items-center gap-1.5"><input type="checkbox" disabled
                                    @checked($clearance->right_over_land === 'heirs') /> Heirs</label>
                            <label class="flex items-center gap-1.5"><input type="checkbox" disabled
                                    @checked($clearance->right_over_land === 'lease') /> Lease</label>
                            <label class="flex items-center gap-1.5"><input type="checkbox" disabled
                                    @checked(!in_array($clearance->right_over_land, ['owner', 'heirs', 'lease']) && $clearance->right_over_land) /> Others</label>
                        </div>
                    </div>
                    <div>
                        <div class="font-bold uppercase text-zinc-500 mb-1">12. Project Tenure</div>
                        <div class="space-y-1">
                            <label class="flex items-center gap-1.5"><input type="checkbox" disabled
                                    @checked($clearance->project_tenure === 'permanent') /> Permanent</label>
                            <label class="flex items-center gap-1.5"><input type="checkbox" disabled
                                    @checked($clearance->project_tenure === 'temporary') /> Temporary</label>
                        </div>
                    </div>
                </div>
                <div class="pt-2 border-t border-zinc-100 dark:border-zinc-800">
                    <div class="font-bold uppercase text-zinc-500 mb-1">13. Existing Land Use</div>
                    <div class="grid grid-cols-3 gap-2">
                        <label class="flex items-center gap-1.5"><input type="checkbox" disabled
                                @checked($clearance->land_use === 'residential') /> Residential</label>
                        <label class="flex items-center gap-1.5"><input type="checkbox" disabled
                                @checked($clearance->land_use === 'commercial') /> Commercial</label>
                        <label class="flex items-center gap-1.5"><input type="checkbox" disabled
                                @checked($clearance->land_use === 'vacant') /> Vacant/Idle</label>
                        <label class="flex items-center gap-1.5"><input type="checkbox" disabled
                                @checked($clearance->land_use === 'institutional') /> Institutional</label>
                        <label class="flex items-center gap-1.5"><input type="checkbox" disabled
                                @checked($clearance->land_use === 'industrial') /> Industrial</label>
                        <label class="flex items-center gap-1.5"><input type="checkbox" disabled
                                @checked($clearance->land_use === 'tenanted') /> Tenanted</label>
                        <label class="flex items-center gap-1.5"><input type="checkbox" disabled
                                @checked($clearance->land_use === 'not_tenanted') /> Not Tenanted</label>
                        <label class="flex items-center gap-1.5 col-span-2"><input type="checkbox" disabled
                                @checked($clearance->land_use === 'agriculture') /> Agriculture (Crops:
                            {{ $clearance->crops_specified ?? 'N/A' }})</label>
                    </div>
                </div>
            </div>

            <!-- Card 5: Project Cost, Legal Questions & Mode of Release -->
            <div
                class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-6 shadow-sm space-y-4 text-xs">
                <h3
                    class="text-sm font-bold uppercase tracking-wider text-zinc-500 border-b border-zinc-200 dark:border-zinc-800 pb-2">
                    14 to 17. Cost, Legal & Release</h3>
                <div>
                    <div class="font-bold uppercase text-zinc-500 mb-1">14. Project Cost</div>
                    <div class="font-mono text-base font-semibold text-zinc-900 dark:text-zinc-100">
                        {{ $clearance->project_cost ? '₱' . number_format($clearance->project_cost, 2) : 'N/A' }}
                    </div>
                </div>
                <div class="space-y-2 pt-2 border-t border-zinc-100 dark:border-zinc-800">
                    <div class="font-bold text-zinc-700 dark:text-zinc-300">15. Written Notice from Board/LGU?</div>
                    <div class="flex gap-4">
                        <label class="flex items-center gap-1"><input type="checkbox" disabled
                                @checked($clearance->has_written_notice) /> Yes</label>
                        <label class="flex items-center gap-1"><input type="checkbox" disabled
                                @checked(!$clearance->has_written_notice) /> No</label>
                    </div>
                </div>
                <div class="space-y-2 pt-2 border-t border-zinc-100 dark:border-zinc-800">
                    <div class="font-bold text-zinc-700 dark:text-zinc-300">16. Related Action with Other Offices?
                    </div>
                    <div class="flex gap-4">
                        <label class="flex items-center gap-1"><input type="checkbox" disabled
                                @checked($clearance->has_related_action) /> Yes</label>
                        <label class="flex items-center gap-1"><input type="checkbox" disabled
                                @checked(!$clearance->has_related_action) /> No</label>
                    </div>
                </div>
                <div class="pt-2 border-t border-zinc-100 dark:border-zinc-800">
                    <div class="font-bold uppercase text-zinc-500 mb-1">17. Preferred Mode of Release</div>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                        <label class="flex items-center gap-1"><input type="checkbox" disabled
                                @checked($clearance->release_mode === 'pick_up') /> Pick-up</label>
                        <label class="flex items-center gap-1"><input type="checkbox" disabled
                                @checked($clearance->release_mode === 'mail') /> By mail</label>
                        <label class="flex items-center gap-1"><input type="checkbox" disabled
                                @checked($clearance->release_recipient === 'applicant') /> Applicant</label>
                        <label class="flex items-center gap-1"><input type="checkbox" disabled
                                @checked($clearance->release_recipient === 'authorized_representative') /> Rep.</label>
                    </div>
                </div>
            </div>

        </div>

        <!-- RIGHT 1 COLUMN: Sticky Action Panel with Requirements & Decision -->
        <div class="lg:col-span-1 print:hidden">
            <div
                class="sticky top-6 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5 shadow-sm space-y-5">
                <div>
                    <h3 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">Action & Review</h3>
                    <p class="text-xs text-zinc-500 mt-0.5">Inspect uploaded requirements and submit decision.</p>
                </div>

                <hr class="border-zinc-200 dark:border-zinc-800" />

                <!-- Requirements Section -->
                <div class="space-y-3">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-zinc-900 dark:text-zinc-100">
                        Requirements</h4>

                    @php
                        $reqs = $clearance->requirements ?? [];
                    @endphp

                    <!-- 1. Basic Docs -->
                    <div class="space-y-1.5">
                        <div class="text-xs font-medium text-zinc-700 dark:text-zinc-300">1. Basic Docs</div>
                        <div class="grid grid-cols-4 gap-2">
                            @forelse ($reqs['basic_docs_files'] ?? [] as $file)
                                <img src="{{ asset('storage/' . $file) }}"
                                    @click="modalOpen = true; activeImage = '{{ asset('storage/' . $file) }}'"
                                    class="h-12 w-full object-cover rounded border border-zinc-200 dark:border-zinc-700 cursor-pointer hover:opacity-80 transition"
                                    alt="Basic Doc">
                            @empty
                                <span class="text-[11px] text-zinc-400 italic col-span-4">No files uploaded.</span>
                            @endforelse
                        </div>
                    </div>

                    <!-- 2. Proof of Land -->
                    <div class="space-y-1.5">
                        <div class="text-xs font-medium text-zinc-700 dark:text-zinc-300">2. Proof of Land</div>
                        <div class="grid grid-cols-4 gap-2">
                            @forelse ($reqs['land_right_files'] ?? [] as $file)
                                <img src="{{ asset('storage/' . $file) }}"
                                    @click="modalOpen = true; activeImage = '{{ asset('storage/' . $file) }}'"
                                    class="h-12 w-full object-cover rounded border border-zinc-200 dark:border-zinc-700 cursor-pointer hover:opacity-80 transition"
                                    alt="Proof of Land">
                            @empty
                                <span class="text-[11px] text-zinc-400 italic col-span-4">No files uploaded.</span>
                            @endforelse
                        </div>
                    </div>

                    <!-- 3. Plans & Specs -->
                    <div class="space-y-1.5">
                        <div class="text-xs font-medium text-zinc-700 dark:text-zinc-300">3. Plans & Specs</div>
                        <div class="grid grid-cols-4 gap-2">
                            @forelse ($reqs['plans_specs_files'] ?? [] as $file)
                                <img src="{{ asset('storage/' . $file) }}"
                                    @click="modalOpen = true; activeImage = '{{ asset('storage/' . $file) }}'"
                                    class="h-12 w-full object-cover rounded border border-zinc-200 dark:border-zinc-700 cursor-pointer hover:opacity-80 transition"
                                    alt="Plans & Specs">
                            @empty
                                <span class="text-[11px] text-zinc-400 italic col-span-4">No files uploaded.</span>
                            @endforelse
                        </div>
                    </div>

                    <!-- 4. Local Clearances -->
                    <div class="space-y-1.5">
                        <div class="text-xs font-medium text-zinc-700 dark:text-zinc-300">4. Local Clearances</div>
                        <div class="grid grid-cols-4 gap-2">
                            @forelse ($reqs['local_clearances_files'] ?? [] as $file)
                                <img src="{{ asset('storage/' . $file) }}"
                                    @click="modalOpen = true; activeImage = '{{ asset('storage/' . $file) }}'"
                                    class="h-12 w-full object-cover rounded border border-zinc-200 dark:border-zinc-700 cursor-pointer hover:opacity-80 transition"
                                    alt="Local Clearance">
                            @empty
                                <span class="text-[11px] text-zinc-400 italic col-span-4">No files uploaded.</span>
                            @endforelse
                        </div>
                    </div>

                    <!-- 5. Special Reqs -->
                    <div class="space-y-1.5">
                        <div class="text-xs font-medium text-zinc-700 dark:text-zinc-300">5. Special Reqs</div>
                        <div class="grid grid-cols-4 gap-2">
                            @forelse ($reqs['special_req_files'] ?? [] as $file)
                                <img src="{{ asset('storage/' . $file) }}"
                                    @click="modalOpen = true; activeImage = '{{ asset('storage/' . $file) }}'"
                                    class="h-12 w-full object-cover rounded border border-zinc-200 dark:border-zinc-700 cursor-pointer hover:opacity-80 transition"
                                    alt="Special Req">
                            @empty
                                <span class="text-[11px] text-zinc-400 italic col-span-4">No files uploaded.</span>
                            @endforelse
                        </div>
                    </div>
                </div>

                <hr class="border-zinc-200 dark:border-zinc-800" />

                <!-- Decision Section -->
                <div class="space-y-4">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-zinc-900 dark:text-zinc-100">Decision
                    </h4>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-medium text-zinc-700 dark:text-zinc-300">Status</label>
                        <flux:select wire:model="status" placeholder="Select status...">
                            <flux:select.option value="under_review">Under Review</flux:select.option>
                            <flux:select.option value="inspection_scheduled">Inspection Scheduled</flux:select.option>
                            <flux:select.option value="approved">Approved</flux:select.option>
                            <flux:select.option value="rejected">Rejected</flux:select.option>
                        </flux:select>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-medium text-zinc-700 dark:text-zinc-300">Remarks</label>
                        <flux:textarea wire:model="remarks" rows="3"
                            placeholder="Enter officer remarks or feedback..." />
                    </div>

                    <flux:button variant="primary" class="w-full">
                        Confirm
                    </flux:button>
                </div>
            </div>
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

            <!-- Zoomable & Draggable Image -->
            <img :src="activeImage"
                :style="`transform: translate(${posX}px, ${posY}px) scale(${zoom}); cursor: ${zoom > 1 ? (dragging ? 'grabbing' : 'grab') : 'default'}; transition: transform 0.05s ease-out;`"
                class="max-h-[85vh] max-w-full object-contain rounded-lg shadow-2xl select-none" @click.stop
                alt="Enlarged Requirement Preview">
        </div>
    </div>
</div>
