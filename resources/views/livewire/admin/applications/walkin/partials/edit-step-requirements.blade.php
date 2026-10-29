@php
$ebf = is_array($existing_basic_docs_files ?? null) ? $existing_basic_docs_files : [];
$nbf = is_array($basic_docs_files ?? null) ? $basic_docs_files : [];
$elrf = is_array($existing_land_right_files ?? null) ? $existing_land_right_files : [];
$nlrf = is_array($land_right_files ?? null) ? $land_right_files : [];
$epsf = is_array($existing_plans_specs_files ?? null) ? $existing_plans_specs_files : [];
$npsf = is_array($plans_specs_files ?? null) ? $plans_specs_files : [];
$elcf = is_array($existing_local_clearances_files ?? null) ? $existing_local_clearances_files : [];
$nlcf = is_array($local_clearances_files ?? null) ? $local_clearances_files : [];
$esrf = is_array($existing_special_req_files ?? null) ? $existing_special_req_files : [];
$nsrf = is_array($special_req_files ?? null) ? $special_req_files : [];

$c1 = count($ebf) + count($nbf);
$c2 = count($elrf) + count($nlrf);
$c3 = count($epsf) + count($npsf);
$c4 = count($elcf) + count($nlcf);
$c5 = count($esrf) + count($nsrf);
@endphp

<div class="space-y-5">
    <!-- Header Badge Counter -->
    <div
        class="flex flex-wrap items-center gap-2 p-3.5 bg-zinc-50 dark:bg-zinc-800/40 rounded-xl border border-zinc-200/80 dark:border-zinc-800 text-xs text-zinc-500">
        <span class="font-medium text-zinc-700 dark:text-zinc-300">Attached files summary:</span>
        <flux:badge size="sm" :color="$c1 > 0 ? 'green' : 'zinc'">1. Basic Docs ({{ $c1 }})</flux:badge>
        <flux:badge size="sm" :color="$c2 > 0 ? 'green' : 'zinc'">2. Land Rights ({{ $c2 }})</flux:badge>
        <flux:badge size="sm" :color="$c3 > 0 ? 'green' : 'zinc'">3. Plans & Specs ({{ $c3 }})</flux:badge>
        <flux:badge size="sm" :color="$c4 > 0 ? 'green' : 'zinc'">4. Clearances ({{ $c4 }})</flux:badge>
        <flux:badge size="sm" :color="$c5 > 0 ? 'green' : 'zinc'">5. Special Reqs ({{ $c5 }})</flux:badge>
    </div>

    <!-- 2-Column Responsive Desktop Grid -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-5 sm:gap-6">

        <!-- 1. Basic Docs -->
        <flux:card class="p-5 space-y-3 flex flex-col justify-between">
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <span class="font-semibold text-sm text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                        <span
                            class="size-5 rounded-full bg-zinc-200 dark:bg-zinc-700 text-xs font-bold flex items-center justify-center">1</span>
                        Basic Application Documents
                    </span>
                    @if ($c1 > 0) <flux:badge size="sm" color="green" icon="check">{{ $c1 }} Attached</flux:badge>
                    @endif
                </div>
                <ul class="text-xs text-zinc-500 list-disc list-inside space-y-0.5 pl-2">
                    <li>Notarized Application Form &bull; Valid ID &bull; Special Power of Attorney</li>
                </ul>
            </div>

            <div class="space-y-2 pt-2">
                <label
                    class="flex flex-col items-center justify-center border-2 border-dashed border-zinc-200 dark:border-zinc-700 hover:border-zinc-400 rounded-xl p-3 text-center cursor-pointer transition bg-zinc-50/50 dark:bg-zinc-800/20">
                    <flux:icon name="arrow-up-tray" class="size-4 text-zinc-400 mb-1" />
                    <span class="text-xs font-medium text-zinc-700 dark:text-zinc-300">Upload additional basic
                        docs</span>
                    <span class="text-[10px] text-zinc-400">PNG, JPG, PDF up to 10MB</span>
                    <input type="file" wire:model="basic_docs_files" multiple accept="image/*,application/pdf"
                        class="hidden" />
                </label>

                @if ($c1 > 0)
                <div class="grid grid-cols-4 sm:grid-cols-6 gap-2 pt-1">
                    @foreach ($ebf as $index => $path)
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
                    @foreach ($nbf as $index => $file)
                    <div
                        class="relative group rounded-lg overflow-hidden border-2 border-blue-500/70 aspect-square bg-zinc-100 dark:bg-zinc-800">
                        @if(str_contains($file->getMimeType(), 'image'))
                        <img src="{{ $file->temporaryUrl() }}" class="size-full object-cover" />
                        @else
                        <div class="size-full flex flex-col items-center justify-center p-1 text-center">
                            <flux:icon name="document-text" class="size-5 text-zinc-400" />
                            <span class="text-[9px] truncate max-w-full text-zinc-500">{{ $file->getClientOriginalName()
                                }}</span>
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
        </flux:card>

        <!-- 2. Land Rights -->
        <flux:card class="p-5 space-y-3 flex flex-col justify-between">
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <span class="font-semibold text-sm text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                        <span
                            class="size-5 rounded-full bg-zinc-200 dark:bg-zinc-700 text-xs font-bold flex items-center justify-center">2</span>
                        Proof of Right Over Land
                    </span>
                    @if ($c2 > 0) <flux:badge size="sm" color="green" icon="check">{{ $c2 }} Attached</flux:badge>
                    @endif
                </div>
                <ul class="text-xs text-zinc-500 list-disc list-inside space-y-0.5 pl-2">
                    <li>TCT &bull; Certified Tax Declaration & Tax Clearance &bull; Lease Contract</li>
                </ul>
            </div>

            <div class="space-y-2 pt-2">
                <label
                    class="flex flex-col items-center justify-center border-2 border-dashed border-zinc-200 dark:border-zinc-700 hover:border-zinc-400 rounded-xl p-3 text-center cursor-pointer transition bg-zinc-50/50 dark:bg-zinc-800/20">
                    <flux:icon name="arrow-up-tray" class="size-4 text-zinc-400 mb-1" />
                    <span class="text-xs font-medium text-zinc-700 dark:text-zinc-300">Upload additional land
                        docs</span>
                    <span class="text-[10px] text-zinc-400">PNG, JPG, PDF up to 10MB</span>
                    <input type="file" wire:model="land_right_files" multiple accept="image/*,application/pdf"
                        class="hidden" />
                </label>

                @if ($c2 > 0)
                <div class="grid grid-cols-4 sm:grid-cols-6 gap-2 pt-1">
                    @foreach ($elrf as $index => $path)
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
                    @foreach ($nlrf as $index => $file)
                    <div
                        class="relative group rounded-lg overflow-hidden border-2 border-blue-500/70 aspect-square bg-zinc-100 dark:bg-zinc-800">
                        @if(str_contains($file->getMimeType(), 'image'))
                        <img src="{{ $file->temporaryUrl() }}" class="size-full object-cover" />
                        @else
                        <div class="size-full flex flex-col items-center justify-center p-1 text-center">
                            <flux:icon name="document-text" class="size-5 text-zinc-400" />
                            <span class="text-[9px] truncate max-w-full text-zinc-500">{{ $file->getClientOriginalName()
                                }}</span>
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
        </flux:card>

        <!-- 3. Plans & Specs -->
        <flux:card class="p-5 space-y-3 flex flex-col justify-between">
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <span class="font-semibold text-sm text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                        <span
                            class="size-5 rounded-full bg-zinc-200 dark:bg-zinc-700 text-xs font-bold flex items-center justify-center">3</span>
                        Plans and Technical Specs
                    </span>
                    @if ($c3 > 0) <flux:badge size="sm" color="green" icon="check">{{ $c3 }} Attached</flux:badge>
                    @endif
                </div>
                <ul class="text-xs text-zinc-500 list-disc list-inside space-y-0.5 pl-2">
                    <li>Location / Vicinity Map &bull; Site Development Plan &bull; Cost Estimates</li>
                </ul>
            </div>

            <div class="space-y-2 pt-2">
                <label
                    class="flex flex-col items-center justify-center border-2 border-dashed border-zinc-200 dark:border-zinc-700 hover:border-zinc-400 rounded-xl p-3 text-center cursor-pointer transition bg-zinc-50/50 dark:bg-zinc-800/20">
                    <flux:icon name="arrow-up-tray" class="size-4 text-zinc-400 mb-1" />
                    <span class="text-xs font-medium text-zinc-700 dark:text-zinc-300">Upload additional plans</span>
                    <span class="text-[10px] text-zinc-400">PNG, JPG, PDF up to 10MB</span>
                    <input type="file" wire:model="plans_specs_files" multiple accept="image/*,application/pdf"
                        class="hidden" />
                </label>

                @if ($c3 > 0)
                <div class="grid grid-cols-4 sm:grid-cols-6 gap-2 pt-1">
                    @foreach ($epsf as $index => $path)
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
                    @foreach ($npsf as $index => $file)
                    <div
                        class="relative group rounded-lg overflow-hidden border-2 border-blue-500/70 aspect-square bg-zinc-100 dark:bg-zinc-800">
                        @if(str_contains($file->getMimeType(), 'image'))
                        <img src="{{ $file->temporaryUrl() }}" class="size-full object-cover" />
                        @else
                        <div class="size-full flex flex-col items-center justify-center p-1 text-center">
                            <flux:icon name="document-text" class="size-5 text-zinc-400" />
                            <span class="text-[9px] truncate max-w-full text-zinc-500">{{ $file->getClientOriginalName()
                                }}</span>
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
        </flux:card>

        <!-- 4. Local Clearances -->
        <flux:card class="p-5 space-y-3 flex flex-col justify-between">
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <span class="font-semibold text-sm text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                        <span
                            class="size-5 rounded-full bg-zinc-200 dark:bg-zinc-700 text-xs font-bold flex items-center justify-center">4</span>
                        Supporting Local Clearances
                    </span>
                    @if ($c4 > 0) <flux:badge size="sm" color="green" icon="check">{{ $c4 }} Attached</flux:badge>
                    @endif
                </div>
                <ul class="text-xs text-zinc-500 list-disc list-inside space-y-0.5 pl-2">
                    <li>Barangay Clearance &bull; Real Property Tax Clearance</li>
                </ul>
            </div>

            <div class="space-y-2 pt-2">
                <label
                    class="flex flex-col items-center justify-center border-2 border-dashed border-zinc-200 dark:border-zinc-700 hover:border-zinc-400 rounded-xl p-3 text-center cursor-pointer transition bg-zinc-50/50 dark:bg-zinc-800/20">
                    <flux:icon name="arrow-up-tray" class="size-4 text-zinc-400 mb-1" />
                    <span class="text-xs font-medium text-zinc-700 dark:text-zinc-300">Upload additional
                        clearances</span>
                    <span class="text-[10px] text-zinc-400">PNG, JPG, PDF up to 10MB</span>
                    <input type="file" wire:model="local_clearances_files" multiple accept="image/*,application/pdf"
                        class="hidden" />
                </label>

                @if ($c4 > 0)
                <div class="grid grid-cols-4 sm:grid-cols-6 gap-2 pt-1">
                    @foreach ($elcf as $index => $path)
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
                    @foreach ($nlcf as $index => $file)
                    <div
                        class="relative group rounded-lg overflow-hidden border-2 border-blue-500/70 aspect-square bg-zinc-100 dark:bg-zinc-800">
                        @if(str_contains($file->getMimeType(), 'image'))
                        <img src="{{ $file->temporaryUrl() }}" class="size-full object-cover" />
                        @else
                        <div class="size-full flex flex-col items-center justify-center p-1 text-center">
                            <flux:icon name="document-text" class="size-5 text-zinc-400" />
                            <span class="text-[9px] truncate max-w-full text-zinc-500">{{ $file->getClientOriginalName()
                                }}</span>
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
        </flux:card>

        <!-- 5. Special Reqs -->
        <flux:card class="p-5 space-y-3 flex flex-col justify-between xl:col-span-2">
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <span class="font-semibold text-sm text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                        <span
                            class="size-5 rounded-full bg-zinc-200 dark:bg-zinc-700 text-xs font-bold flex items-center justify-center">5</span>
                        Special Requirements (Project-Specific)
                    </span>
                    @if ($c5 > 0) <flux:badge size="sm" color="green" icon="check">{{ $c5 }} Attached</flux:badge>
                    @endif
                </div>
                <ul class="text-xs text-zinc-500 list-disc list-inside space-y-0.5 pl-2">
                    <li>DTI / SEC &bull; HOA &bull; DPWH &bull; Environmental (ECC/CNC) &bull; DAR Conversion</li>
                </ul>
            </div>

            <div class="space-y-2 pt-2">
                <label
                    class="flex flex-col items-center justify-center border-2 border-dashed border-zinc-200 dark:border-zinc-700 hover:border-zinc-400 rounded-xl p-3 text-center cursor-pointer transition bg-zinc-50/50 dark:bg-zinc-800/20">
                    <flux:icon name="arrow-up-tray" class="size-4 text-zinc-400 mb-1" />
                    <span class="text-xs font-medium text-zinc-700 dark:text-zinc-300">Upload additional special
                        clearances</span>
                    <span class="text-[10px] text-zinc-400">PNG, JPG, PDF up to 10MB</span>
                    <input type="file" wire:model="special_req_files" multiple accept="image/*,application/pdf"
                        class="hidden" />
                </label>

                @if ($c5 > 0)
                <div class="grid grid-cols-4 sm:grid-cols-8 md:grid-cols-10 gap-2 pt-1">
                    @foreach ($esrf as $index => $path)
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
                    @foreach ($nsrf as $index => $file)
                    <div
                        class="relative group rounded-lg overflow-hidden border-2 border-blue-500/70 aspect-square bg-zinc-100 dark:bg-zinc-800">
                        @if(str_contains($file->getMimeType(), 'image'))
                        <img src="{{ $file->temporaryUrl() }}" class="size-full object-cover" />
                        @else
                        <div class="size-full flex flex-col items-center justify-center p-1 text-center">
                            <flux:icon name="document-text" class="size-5 text-zinc-400" />
                            <span class="text-[9px] truncate max-w-full text-zinc-500">{{ $file->getClientOriginalName()
                                }}</span>
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
        </flux:card>
    </div>

    <div class="flex flex-col-reverse sm:flex-row items-center justify-between gap-3 pt-2">
        <flux:button wire:click="$set('step', 1)" variant="subtle" icon="arrow-left" class="w-full sm:w-auto">
            Back to Form Details
        </flux:button>

        <flux:button wire:click="goToStep3" variant="primary" icon-trailing="arrow-right"
            class="w-full sm:w-auto justify-center">
            Next: Pinpoint Location Map
        </flux:button>
    </div>
</div>