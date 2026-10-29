@php
$c1 = count($existing_basic_docs_files) + count($basic_docs_files);
$c2 = count($existing_land_right_files) + count($land_right_files);
$c3 = count($existing_plans_specs_files) + count($plans_specs_files);
$c4 = count($existing_local_clearances_files) + count($local_clearances_files);
$c5 = count($existing_special_req_files) + count($special_req_files);
@endphp

<div class="space-y-5">
    <!-- Header Badge Row -->
    <div
        class="flex flex-wrap items-center gap-2 p-3 bg-zinc-50 dark:bg-zinc-800/40 rounded-xl border border-zinc-200/80 dark:border-zinc-800 text-xs text-zinc-500">
        <span class="font-medium text-zinc-700 dark:text-zinc-300">Current files:</span>
        <flux:badge size="sm" :color="$c1 > 0 ? 'green' : 'zinc'">Docs: {{ $c1 }}</flux:badge>
        <flux:badge size="sm" :color="$c2 > 0 ? 'green' : 'zinc'">Land: {{ $c2 }}</flux:badge>
        <flux:badge size="sm" :color="$c3 > 0 ? 'green' : 'zinc'">Plans: {{ $c3 }}</flux:badge>
        <flux:badge size="sm" :color="$c4 > 0 ? 'green' : 'zinc'">Clearance: {{ $c4 }}</flux:badge>
        <flux:badge size="sm" :color="$c5 > 0 ? 'green' : 'zinc'">Special: {{ $c5 }}</flux:badge>
    </div>

    <flux:card class="p-4 sm:p-6 space-y-6">
        <div>
            <flux:heading size="lg" class="text-base sm:text-lg">Requirements Checklist & Attachments</flux:heading>
            <flux:subheading class="text-xs">
                Review existing documents, delete outdated ones, or attach new files (JPG, PNG, PDF up to 10MB each).
            </flux:subheading>
        </div>

        <!-- 1. Basic Docs -->
        <div class="space-y-3 pt-2">
            <div class="flex items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <span
                        class="size-6 rounded-full bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 text-xs font-bold flex items-center justify-center shrink-0">1</span>
                    <span class="font-semibold text-sm sm:text-base text-zinc-900 dark:text-zinc-100">Basic Application
                        Documents</span>
                </div>
                @if ($c1 > 0) <flux:badge size="sm" color="green" icon="check">{{ $c1 }} Attached</flux:badge> @endif
            </div>

            <ul class="text-xs sm:text-sm text-zinc-500 list-disc list-inside space-y-0.5 pl-2 sm:pl-8">
                <li>Notarized Application Form</li>
                <li>Valid Identification Card (ID)</li>
                <li>Special Power of Attorney (if representative)</li>
            </ul>

            <div class="pl-2 sm:pl-8 space-y-2">
                <label
                    class="flex flex-col items-center justify-center border-2 border-dashed border-zinc-200 dark:border-zinc-700 hover:border-zinc-400 dark:hover:border-zinc-500 rounded-xl p-3 sm:p-4 text-center cursor-pointer transition bg-zinc-50/50 dark:bg-zinc-800/20">
                    <flux:icon name="arrow-up-tray" class="size-5 text-zinc-400 mb-1" />
                    <span class="text-xs font-medium text-zinc-700 dark:text-zinc-300">Tap to upload additional basic
                        documents</span>
                    <span class="text-[10px] text-zinc-400">PNG, JPG, PDF up to 10MB</span>
                    <input type="file" wire:model="basic_docs_files" multiple accept="image/*,application/pdf"
                        class="hidden" />
                </label>

                @if ($c1 > 0)
                <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-2 pt-1">
                    @foreach ($existing_basic_docs_files as $index => $path)
                    <div
                        class="relative group rounded-lg overflow-hidden border-2 border-emerald-500/70 aspect-square bg-zinc-100 dark:bg-zinc-800">
                        <a href="{{ Storage::url($path) }}" target="_blank" rel="noopener noreferrer"
                            class="size-full block">
                            <img src="{{ Storage::url($path) }}" class="size-full object-cover" />
                        </a>
                        <span
                            class="absolute bottom-1 left-1 text-[9px] bg-emerald-600/90 text-white px-1 rounded font-medium">Saved</span>
                        <button type="button" wire:click="removeExistingFile('basic_docs_files', {{ $index }})"
                            class="absolute top-1 right-1 size-5 bg-red-600 text-white rounded-full flex items-center justify-center shadow-md hover:bg-red-700 transition">
                            <flux:icon name="x-mark" class="size-3" />
                        </button>
                    </div>
                    @endforeach

                    @foreach ($basic_docs_files as $index => $file)
                    <div
                        class="relative group rounded-lg overflow-hidden border-2 border-blue-500/70 aspect-square bg-zinc-100 dark:bg-zinc-800">
                        @if(str_contains($file->getMimeType(), 'image'))
                        <img src="{{ $file->temporaryUrl() }}" class="size-full object-cover" />
                        @else
                        <div class="size-full flex flex-col items-center justify-center p-1 text-center">
                            <flux:icon name="document-text" class="size-6 text-zinc-400" />
                            <span class="text-[10px] truncate max-w-full text-zinc-500">{{
                                $file->getClientOriginalName() }}</span>
                        </div>
                        @endif
                        <span
                            class="absolute bottom-1 left-1 text-[9px] bg-blue-600/90 text-white px-1 rounded font-medium">New</span>
                        <button type="button" wire:click="removeNewFile('basic_docs_files', {{ $index }})"
                            class="absolute top-1 right-1 size-5 bg-red-600 text-white rounded-full flex items-center justify-center shadow-md hover:bg-red-700 transition">
                            <flux:icon name="x-mark" class="size-3" />
                        </button>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        <flux:separator />

        <!-- 2. Land Rights -->
        <div class="space-y-3">
            <div class="flex items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <span
                        class="size-6 rounded-full bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 text-xs font-bold flex items-center justify-center shrink-0">2</span>
                    <span class="font-semibold text-sm sm:text-base text-zinc-900 dark:text-zinc-100">Proof of Right
                        Over Land</span>
                </div>
                @if ($c2 > 0) <flux:badge size="sm" color="green" icon="check">{{ $c2 }} Attached</flux:badge> @endif
            </div>

            <ul class="text-xs sm:text-sm text-zinc-500 list-disc list-inside space-y-0.5 pl-2 sm:pl-8">
                <li>TCT (For titled properties)</li>
                <li>Certified Tax Declaration & Real Property Tax Clearance</li>
                <li>Notarized Contract of Lease or Authorization</li>
            </ul>

            <div class="pl-2 sm:pl-8 space-y-2">
                <label
                    class="flex flex-col items-center justify-center border-2 border-dashed border-zinc-200 dark:border-zinc-700 hover:border-zinc-400 dark:hover:border-zinc-500 rounded-xl p-3 sm:p-4 text-center cursor-pointer transition bg-zinc-50/50 dark:bg-zinc-800/20">
                    <flux:icon name="arrow-up-tray" class="size-5 text-zinc-400 mb-1" />
                    <span class="text-xs font-medium text-zinc-700 dark:text-zinc-300">Tap to upload additional land
                        documents</span>
                    <span class="text-[10px] text-zinc-400">PNG, JPG, PDF up to 10MB</span>
                    <input type="file" wire:model="land_right_files" multiple accept="image/*,application/pdf"
                        class="hidden" />
                </label>

                @if ($c2 > 0)
                <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-2 pt-1">
                    @foreach ($existing_land_right_files as $index => $path)
                    <div
                        class="relative group rounded-lg overflow-hidden border-2 border-emerald-500/70 aspect-square bg-zinc-100 dark:bg-zinc-800">
                        <a href="{{ Storage::url($path) }}" target="_blank" rel="noopener noreferrer"
                            class="size-full block">
                            <img src="{{ Storage::url($path) }}" class="size-full object-cover" />
                        </a>
                        <span
                            class="absolute bottom-1 left-1 text-[9px] bg-emerald-600/90 text-white px-1 rounded font-medium">Saved</span>
                        <button type="button" wire:click="removeExistingFile('land_right_files', {{ $index }})"
                            class="absolute top-1 right-1 size-5 bg-red-600 text-white rounded-full flex items-center justify-center shadow-md hover:bg-red-700 transition">
                            <flux:icon name="x-mark" class="size-3" />
                        </button>
                    </div>
                    @endforeach

                    @foreach ($land_right_files as $index => $file)
                    <div
                        class="relative group rounded-lg overflow-hidden border-2 border-blue-500/70 aspect-square bg-zinc-100 dark:bg-zinc-800">
                        @if(str_contains($file->getMimeType(), 'image'))
                        <img src="{{ $file->temporaryUrl() }}" class="size-full object-cover" />
                        @else
                        <div class="size-full flex flex-col items-center justify-center p-1 text-center">
                            <flux:icon name="document-text" class="size-6 text-zinc-400" />
                            <span class="text-[10px] truncate max-w-full text-zinc-500">{{
                                $file->getClientOriginalName() }}</span>
                        </div>
                        @endif
                        <span
                            class="absolute bottom-1 left-1 text-[9px] bg-blue-600/90 text-white px-1 rounded font-medium">New</span>
                        <button type="button" wire:click="removeNewFile('land_right_files', {{ $index }})"
                            class="absolute top-1 right-1 size-5 bg-red-600 text-white rounded-full flex items-center justify-center shadow-md hover:bg-red-700 transition">
                            <flux:icon name="x-mark" class="size-3" />
                        </button>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        <flux:separator />

        <!-- 3. Plans & Specs -->
        <div class="space-y-3">
            <div class="flex items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <span
                        class="size-6 rounded-full bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 text-xs font-bold flex items-center justify-center shrink-0">3</span>
                    <span class="font-semibold text-sm sm:text-base text-zinc-900 dark:text-zinc-100">Plans and
                        Technical Specs</span>
                </div>
                @if ($c3 > 0) <flux:badge size="sm" color="green" icon="check">{{ $c3 }} Attached</flux:badge> @endif
            </div>

            <ul class="text-xs sm:text-sm text-zinc-500 list-disc list-inside space-y-0.5 pl-2 sm:pl-8">
                <li>Vicinity / Location Map</li>
                <li>Site Development Plan & Architectural Plans</li>
                <li>Bill of Materials & Cost Estimates</li>
            </ul>

            <div class="pl-2 sm:pl-8 space-y-2">
                <label
                    class="flex flex-col items-center justify-center border-2 border-dashed border-zinc-200 dark:border-zinc-700 hover:border-zinc-400 dark:hover:border-zinc-500 rounded-xl p-3 sm:p-4 text-center cursor-pointer transition bg-zinc-50/50 dark:bg-zinc-800/20">
                    <flux:icon name="arrow-up-tray" class="size-5 text-zinc-400 mb-1" />
                    <span class="text-xs font-medium text-zinc-700 dark:text-zinc-300">Tap to upload additional plans
                        and specs</span>
                    <span class="text-[10px] text-zinc-400">PNG, JPG, PDF up to 10MB</span>
                    <input type="file" wire:model="plans_specs_files" multiple accept="image/*,application/pdf"
                        class="hidden" />
                </label>

                @if ($c3 > 0)
                <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-2 pt-1">
                    @foreach ($existing_plans_specs_files as $index => $path)
                    <div
                        class="relative group rounded-lg overflow-hidden border-2 border-emerald-500/70 aspect-square bg-zinc-100 dark:bg-zinc-800">
                        <a href="{{ Storage::url($path) }}" target="_blank" rel="noopener noreferrer"
                            class="size-full block">
                            <img src="{{ Storage::url($path) }}" class="size-full object-cover" />
                        </a>
                        <span
                            class="absolute bottom-1 left-1 text-[9px] bg-emerald-600/90 text-white px-1 rounded font-medium">Saved</span>
                        <button type="button" wire:click="removeExistingFile('plans_specs_files', {{ $index }})"
                            class="absolute top-1 right-1 size-5 bg-red-600 text-white rounded-full flex items-center justify-center shadow-md hover:bg-red-700 transition">
                            <flux:icon name="x-mark" class="size-3" />
                        </button>
                    </div>
                    @endforeach

                    @foreach ($plans_specs_files as $index => $file)
                    <div
                        class="relative group rounded-lg overflow-hidden border-2 border-blue-500/70 aspect-square bg-zinc-100 dark:bg-zinc-800">
                        @if(str_contains($file->getMimeType(), 'image'))
                        <img src="{{ $file->temporaryUrl() }}" class="size-full object-cover" />
                        @else
                        <div class="size-full flex flex-col items-center justify-center p-1 text-center">
                            <flux:icon name="document-text" class="size-6 text-zinc-400" />
                            <span class="text-[10px] truncate max-w-full text-zinc-500">{{
                                $file->getClientOriginalName() }}</span>
                        </div>
                        @endif
                        <span
                            class="absolute bottom-1 left-1 text-[9px] bg-blue-600/90 text-white px-1 rounded font-medium">New</span>
                        <button type="button" wire:click="removeNewFile('plans_specs_files', {{ $index }})"
                            class="absolute top-1 right-1 size-5 bg-red-600 text-white rounded-full flex items-center justify-center shadow-md hover:bg-red-700 transition">
                            <flux:icon name="x-mark" class="size-3" />
                        </button>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        <flux:separator />

        <!-- 4. Local Clearances -->
        <div class="space-y-3">
            <div class="flex items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <span
                        class="size-6 rounded-full bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 text-xs font-bold flex items-center justify-center shrink-0">4</span>
                    <span class="font-semibold text-sm sm:text-base text-zinc-900 dark:text-zinc-100">Supporting Local
                        Clearances</span>
                </div>
                @if ($c4 > 0) <flux:badge size="sm" color="green" icon="check">{{ $c4 }} Attached</flux:badge> @endif
            </div>

            <ul class="text-xs sm:text-sm text-zinc-500 list-disc list-inside space-y-0.5 pl-2 sm:pl-8">
                <li>Barangay Clearance for Locational Clearance</li>
                <li>Real Property Tax Clearance / Latest Tax Receipt</li>
            </ul>

            <div class="pl-2 sm:pl-8 space-y-2">
                <label
                    class="flex flex-col items-center justify-center border-2 border-dashed border-zinc-200 dark:border-zinc-700 hover:border-zinc-400 dark:hover:border-zinc-500 rounded-xl p-3 sm:p-4 text-center cursor-pointer transition bg-zinc-50/50 dark:bg-zinc-800/20">
                    <flux:icon name="arrow-up-tray" class="size-5 text-zinc-400 mb-1" />
                    <span class="text-xs font-medium text-zinc-700 dark:text-zinc-300">Tap to upload additional local
                        clearances</span>
                    <span class="text-[10px] text-zinc-400">PNG, JPG, PDF up to 10MB</span>
                    <input type="file" wire:model="local_clearances_files" multiple accept="image/*,application/pdf"
                        class="hidden" />
                </label>

                @if ($c4 > 0)
                <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-2 pt-1">
                    @foreach ($existing_local_clearances_files as $index => $path)
                    <div
                        class="relative group rounded-lg overflow-hidden border-2 border-emerald-500/70 aspect-square bg-zinc-100 dark:bg-zinc-800">
                        <a href="{{ Storage::url($path) }}" target="_blank" rel="noopener noreferrer"
                            class="size-full block">
                            <img src="{{ Storage::url($path) }}" class="size-full object-cover" />
                        </a>
                        <span
                            class="absolute bottom-1 left-1 text-[9px] bg-emerald-600/90 text-white px-1 rounded font-medium">Saved</span>
                        <button type="button" wire:click="removeExistingFile('local_clearances_files', {{ $index }})"
                            class="absolute top-1 right-1 size-5 bg-red-600 text-white rounded-full flex items-center justify-center shadow-md hover:bg-red-700 transition">
                            <flux:icon name="x-mark" class="size-3" />
                        </button>
                    </div>
                    @endforeach

                    @foreach ($local_clearances_files as $index => $file)
                    <div
                        class="relative group rounded-lg overflow-hidden border-2 border-blue-500/70 aspect-square bg-zinc-100 dark:bg-zinc-800">
                        @if(str_contains($file->getMimeType(), 'image'))
                        <img src="{{ $file->temporaryUrl() }}" class="size-full object-cover" />
                        @else
                        <div class="size-full flex flex-col items-center justify-center p-1 text-center">
                            <flux:icon name="document-text" class="size-6 text-zinc-400" />
                            <span class="text-[10px] truncate max-w-full text-zinc-500">{{
                                $file->getClientOriginalName() }}</span>
                        </div>
                        @endif
                        <span
                            class="absolute bottom-1 left-1 text-[9px] bg-blue-600/90 text-white px-1 rounded font-medium">New</span>
                        <button type="button" wire:click="removeNewFile('local_clearances_files', {{ $index }})"
                            class="absolute top-1 right-1 size-5 bg-red-600 text-white rounded-full flex items-center justify-center shadow-md hover:bg-red-700 transition">
                            <flux:icon name="x-mark" class="size-3" />
                        </button>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        <flux:separator />

        <!-- 5. Special Reqs -->
        <div class="space-y-3">
            <div class="flex items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <span
                        class="size-6 rounded-full bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 text-xs font-bold flex items-center justify-center shrink-0">5</span>
                    <span class="font-semibold text-sm sm:text-base text-zinc-900 dark:text-zinc-100">Special
                        Requirements (If Applicable)</span>
                </div>
                @if ($c5 > 0) <flux:badge size="sm" color="green" icon="check">{{ $c5 }} Attached</flux:badge> @endif
            </div>

            <ul class="text-xs sm:text-sm text-zinc-500 list-disc list-inside space-y-0.5 pl-2 sm:pl-8">
                <li>DTI / SEC Registration, HOA Clearance, DPWH, ECC / CNC, DAR Conversion</li>
            </ul>

            <div class="pl-2 sm:pl-8 space-y-2">
                <label
                    class="flex flex-col items-center justify-center border-2 border-dashed border-zinc-200 dark:border-zinc-700 hover:border-zinc-400 dark:hover:border-zinc-500 rounded-xl p-3 sm:p-4 text-center cursor-pointer transition bg-zinc-50/50 dark:bg-zinc-800/20">
                    <flux:icon name="arrow-up-tray" class="size-5 text-zinc-400 mb-1" />
                    <span class="text-xs font-medium text-zinc-700 dark:text-zinc-300">Tap to upload additional special
                        requirements</span>
                    <span class="text-[10px] text-zinc-400">PNG, JPG, PDF up to 10MB</span>
                    <input type="file" wire:model="special_req_files" multiple accept="image/*,application/pdf"
                        class="hidden" />
                </label>

                @if ($c5 > 0)
                <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-2 pt-1">
                    @foreach ($existing_special_req_files as $index => $path)
                    <div
                        class="relative group rounded-lg overflow-hidden border-2 border-emerald-500/70 aspect-square bg-zinc-100 dark:bg-zinc-800">
                        <a href="{{ Storage::url($path) }}" target="_blank" rel="noopener noreferrer"
                            class="size-full block">
                            <img src="{{ Storage::url($path) }}" class="size-full object-cover" />
                        </a>
                        <span
                            class="absolute bottom-1 left-1 text-[9px] bg-emerald-600/90 text-white px-1 rounded font-medium">Saved</span>
                        <button type="button" wire:click="removeExistingFile('special_req_files', {{ $index }})"
                            class="absolute top-1 right-1 size-5 bg-red-600 text-white rounded-full flex items-center justify-center shadow-md hover:bg-red-700 transition">
                            <flux:icon name="x-mark" class="size-3" />
                        </button>
                    </div>
                    @endforeach

                    @foreach ($special_req_files as $index => $file)
                    <div
                        class="relative group rounded-lg overflow-hidden border-2 border-blue-500/70 aspect-square bg-zinc-100 dark:bg-zinc-800">
                        @if(str_contains($file->getMimeType(), 'image'))
                        <img src="{{ $file->temporaryUrl() }}" class="size-full object-cover" />
                        @else
                        <div class="size-full flex flex-col items-center justify-center p-1 text-center">
                            <flux:icon name="document-text" class="size-6 text-zinc-400" />
                            <span class="text-[10px] truncate max-w-full text-zinc-500">{{
                                $file->getClientOriginalName() }}</span>
                        </div>
                        @endif
                        <span
                            class="absolute bottom-1 left-1 text-[9px] bg-blue-600/90 text-white px-1 rounded font-medium">New</span>
                        <button type="button" wire:click="removeNewFile('special_req_files', {{ $index }})"
                            class="absolute top-1 right-1 size-5 bg-red-600 text-white rounded-full flex items-center justify-center shadow-md hover:bg-red-700 transition">
                            <flux:icon name="x-mark" class="size-3" />
                        </button>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </flux:card>

    <div class="flex flex-col-reverse sm:flex-row items-center justify-between gap-3 pt-2">
        <flux:button wire:click="$set('step', 1)" variant="subtle" icon="arrow-left" class="w-full sm:w-auto">
            Back to Form Details
        </flux:button>

        <flux:button wire:click="goToStep3" variant="primary" icon-trailing="arrow-right"
            class="w-full sm:w-auto justify-center">
            Next: Pinpoint Location
        </flux:button>
    </div>
</div>