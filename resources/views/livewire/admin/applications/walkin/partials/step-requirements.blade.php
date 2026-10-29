<div class="space-y-5">
    <!-- Header Badge Counter -->
    <div
        class="flex flex-wrap items-center gap-2 p-3.5 bg-zinc-50 dark:bg-zinc-800/40 rounded-xl border border-zinc-200/80 dark:border-zinc-800 text-xs text-zinc-500">
        <span class="font-medium text-zinc-700 dark:text-zinc-300">Attached files summary:</span>
        <flux:badge size="sm" :color="count($basic_docs_files) > 0 ? 'green' : 'zinc'">1. Basic Docs ({{
            count($basic_docs_files) }})</flux:badge>
        <flux:badge size="sm" :color="count($land_right_files) > 0 ? 'green' : 'zinc'">2. Land Rights ({{
            count($land_right_files) }})</flux:badge>
        <flux:badge size="sm" :color="count($plans_specs_files) > 0 ? 'green' : 'zinc'">3. Plans & Specs ({{
            count($plans_specs_files) }})</flux:badge>
        <flux:badge size="sm" :color="count($local_clearances_files) > 0 ? 'green' : 'zinc'">4. Clearances ({{
            count($local_clearances_files) }})</flux:badge>
        <flux:badge size="sm" :color="count($special_req_files) > 0 ? 'green' : 'zinc'">5. Special Reqs ({{
            count($special_req_files) }})</flux:badge>
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
                    @if (count($basic_docs_files) > 0)
                    <flux:badge size="sm" color="green" icon="check">{{ count($basic_docs_files) }} Attached
                    </flux:badge>
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
                    <span class="text-xs font-medium text-zinc-700 dark:text-zinc-300">Upload basic documents</span>
                    <span class="text-[10px] text-zinc-400">PNG, JPG, PDF up to 10MB</span>
                    <input type="file" wire:model="basic_docs_files" multiple accept="image/*,application/pdf"
                        class="hidden" />
                </label>

                @if ($basic_docs_files)
                <div class="grid grid-cols-4 sm:grid-cols-6 gap-2 pt-1">
                    @foreach ($basic_docs_files as $index => $file)
                    <div
                        class="relative group rounded-lg overflow-hidden border border-zinc-200 dark:border-zinc-700 aspect-square bg-zinc-100 dark:bg-zinc-800">
                        @if(str_contains($file->getMimeType(), 'image'))
                        <img src="{{ $file->temporaryUrl() }}" class="size-full object-cover" />
                        @else
                        <div class="size-full flex flex-col items-center justify-center p-1 text-center">
                            <flux:icon name="document-text" class="size-5 text-zinc-400" />
                            <span class="text-[9px] truncate max-w-full text-zinc-500">{{ $file->getClientOriginalName()
                                }}</span>
                        </div>
                        @endif
                        <button type="button" wire:click="removeFile('basic_docs_files', {{ $index }})"
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
                    @if (count($land_right_files) > 0)
                    <flux:badge size="sm" color="green" icon="check">{{ count($land_right_files) }} Attached
                    </flux:badge>
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
                    <span class="text-xs font-medium text-zinc-700 dark:text-zinc-300">Upload land documents</span>
                    <span class="text-[10px] text-zinc-400">PNG, JPG, PDF up to 10MB</span>
                    <input type="file" wire:model="land_right_files" multiple accept="image/*,application/pdf"
                        class="hidden" />
                </label>

                @if ($land_right_files)
                <div class="grid grid-cols-4 sm:grid-cols-6 gap-2 pt-1">
                    @foreach ($land_right_files as $index => $file)
                    <div
                        class="relative group rounded-lg overflow-hidden border border-zinc-200 dark:border-zinc-700 aspect-square bg-zinc-100 dark:bg-zinc-800">
                        @if(str_contains($file->getMimeType(), 'image'))
                        <img src="{{ $file->temporaryUrl() }}" class="size-full object-cover" />
                        @else
                        <div class="size-full flex flex-col items-center justify-center p-1 text-center">
                            <flux:icon name="document-text" class="size-5 text-zinc-400" />
                            <span class="text-[9px] truncate max-w-full text-zinc-500">{{ $file->getClientOriginalName()
                                }}</span>
                        </div>
                        @endif
                        <button type="button" wire:click="removeFile('land_right_files', {{ $index }})"
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
                    @if (count($plans_specs_files) > 0)
                    <flux:badge size="sm" color="green" icon="check">{{ count($plans_specs_files) }} Attached
                    </flux:badge>
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
                    <span class="text-xs font-medium text-zinc-700 dark:text-zinc-300">Upload plans & specs</span>
                    <span class="text-[10px] text-zinc-400">PNG, JPG, PDF up to 10MB</span>
                    <input type="file" wire:model="plans_specs_files" multiple accept="image/*,application/pdf"
                        class="hidden" />
                </label>

                @if ($plans_specs_files)
                <div class="grid grid-cols-4 sm:grid-cols-6 gap-2 pt-1">
                    @foreach ($plans_specs_files as $index => $file)
                    <div
                        class="relative group rounded-lg overflow-hidden border border-zinc-200 dark:border-zinc-700 aspect-square bg-zinc-100 dark:bg-zinc-800">
                        @if(str_contains($file->getMimeType(), 'image'))
                        <img src="{{ $file->temporaryUrl() }}" class="size-full object-cover" />
                        @else
                        <div class="size-full flex flex-col items-center justify-center p-1 text-center">
                            <flux:icon name="document-text" class="size-5 text-zinc-400" />
                            <span class="text-[9px] truncate max-w-full text-zinc-500">{{ $file->getClientOriginalName()
                                }}</span>
                        </div>
                        @endif
                        <button type="button" wire:click="removeFile('plans_specs_files', {{ $index }})"
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
                    @if (count($local_clearances_files) > 0)
                    <flux:badge size="sm" color="green" icon="check">{{ count($local_clearances_files) }} Attached
                    </flux:badge>
                    @endif
                </div>
                <ul class="text-xs text-zinc-500 list-disc list-inside space-y-0.5 pl-2">
                    <li>Barangay Clearance for Locational Clearance &bull; Real Property Tax Clearance</li>
                </ul>
            </div>

            <div class="space-y-2 pt-2">
                <label
                    class="flex flex-col items-center justify-center border-2 border-dashed border-zinc-200 dark:border-zinc-700 hover:border-zinc-400 rounded-xl p-3 text-center cursor-pointer transition bg-zinc-50/50 dark:bg-zinc-800/20">
                    <flux:icon name="arrow-up-tray" class="size-4 text-zinc-400 mb-1" />
                    <span class="text-xs font-medium text-zinc-700 dark:text-zinc-300">Upload local clearances</span>
                    <span class="text-[10px] text-zinc-400">PNG, JPG, PDF up to 10MB</span>
                    <input type="file" wire:model="local_clearances_files" multiple accept="image/*,application/pdf"
                        class="hidden" />
                </label>

                @if ($local_clearances_files)
                <div class="grid grid-cols-4 sm:grid-cols-6 gap-2 pt-1">
                    @foreach ($local_clearances_files as $index => $file)
                    <div
                        class="relative group rounded-lg overflow-hidden border border-zinc-200 dark:border-zinc-700 aspect-square bg-zinc-100 dark:bg-zinc-800">
                        @if(str_contains($file->getMimeType(), 'image'))
                        <img src="{{ $file->temporaryUrl() }}" class="size-full object-cover" />
                        @else
                        <div class="size-full flex flex-col items-center justify-center p-1 text-center">
                            <flux:icon name="document-text" class="size-5 text-zinc-400" />
                            <span class="text-[9px] truncate max-w-full text-zinc-500">{{ $file->getClientOriginalName()
                                }}</span>
                        </div>
                        @endif
                        <button type="button" wire:click="removeFile('local_clearances_files', {{ $index }})"
                            class="absolute top-1 right-1 size-5 bg-red-600 text-white rounded-full flex items-center justify-center shadow-md hover:bg-red-700 transition">
                            <flux:icon name="x-mark" class="size-3" />
                        </button>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </flux:card>

        <!-- 5. Special Requirements (Spans full width on widescreen) -->
        <flux:card class="p-5 space-y-3 flex flex-col justify-between xl:col-span-2">
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <span class="font-semibold text-sm text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                        <span
                            class="size-5 rounded-full bg-zinc-200 dark:bg-zinc-700 text-xs font-bold flex items-center justify-center">5</span>
                        Special Requirements (Project-Specific)
                    </span>
                    @if (count($special_req_files) > 0)
                    <flux:badge size="sm" color="green" icon="check">{{ count($special_req_files) }} Attached
                    </flux:badge>
                    @endif
                </div>
                <ul class="text-xs text-zinc-500 list-disc list-inside space-y-0.5 pl-2">
                    <li>DTI / SEC Registration &bull; HOA Clearance &bull; DPWH Clearance &bull; Environmental (ECC/CNC)
                        &bull; DAR Conversion</li>
                </ul>
            </div>

            <div class="space-y-2 pt-2">
                <label
                    class="flex flex-col items-center justify-center border-2 border-dashed border-zinc-200 dark:border-zinc-700 hover:border-zinc-400 rounded-xl p-3 text-center cursor-pointer transition bg-zinc-50/50 dark:bg-zinc-800/20">
                    <flux:icon name="arrow-up-tray" class="size-4 text-zinc-400 mb-1" />
                    <span class="text-xs font-medium text-zinc-700 dark:text-zinc-300">Upload special conditional
                        clearances</span>
                    <span class="text-[10px] text-zinc-400">PNG, JPG, PDF up to 10MB</span>
                    <input type="file" wire:model="special_req_files" multiple accept="image/*,application/pdf"
                        class="hidden" />
                </label>

                @if ($special_req_files)
                <div class="grid grid-cols-4 sm:grid-cols-8 md:grid-cols-10 gap-2 pt-1">
                    @foreach ($special_req_files as $index => $file)
                    <div
                        class="relative group rounded-lg overflow-hidden border border-zinc-200 dark:border-zinc-700 aspect-square bg-zinc-100 dark:bg-zinc-800">
                        @if(str_contains($file->getMimeType(), 'image'))
                        <img src="{{ $file->temporaryUrl() }}" class="size-full object-cover" />
                        @else
                        <div class="size-full flex flex-col items-center justify-center p-1 text-center">
                            <flux:icon name="document-text" class="size-5 text-zinc-400" />
                            <span class="text-[9px] truncate max-w-full text-zinc-500">{{ $file->getClientOriginalName()
                                }}</span>
                        </div>
                        @endif
                        <button type="button" wire:click="removeFile('special_req_files', {{ $index }})"
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

    <!-- Navigation Buttons -->
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