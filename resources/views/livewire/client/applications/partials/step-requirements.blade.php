<div class="space-y-5">
    <!-- Header Badge Counter -->
    <div
        class="flex flex-wrap items-center gap-2 p-3 bg-zinc-50 dark:bg-zinc-800/40 rounded-xl border border-zinc-200/80 dark:border-zinc-800 text-xs text-zinc-500">
        <span class="font-medium text-zinc-700 dark:text-zinc-300">Attached files:</span>
        <flux:badge size="sm" :color="count($basic_docs_files) > 0 ? 'green' : 'zinc'">Docs: {{ count($basic_docs_files)
            }}</flux:badge>
        <flux:badge size="sm" :color="count($land_right_files) > 0 ? 'green' : 'zinc'">Land: {{ count($land_right_files)
            }}</flux:badge>
        <flux:badge size="sm" :color="count($plans_specs_files) > 0 ? 'green' : 'zinc'">Plans: {{
            count($plans_specs_files) }}</flux:badge>
        <flux:badge size="sm" :color="count($local_clearances_files) > 0 ? 'green' : 'zinc'">Clearance: {{
            count($local_clearances_files) }}</flux:badge>
        <flux:badge size="sm" :color="count($special_req_files) > 0 ? 'green' : 'zinc'">Special: {{
            count($special_req_files) }}</flux:badge>
    </div>

    <!-- Requirements Card -->
    <flux:card class="p-4 sm:p-6 space-y-6">
        <div>
            <flux:heading size="lg" class="text-base sm:text-lg">Requirements Checklist & Attachments</flux:heading>
            <flux:subheading class="text-xs">
                Attach photos or scans (JPG, PNG, PDF up to 10MB each). All uploads are optional during initial filing.
            </flux:subheading>
        </div>

        <!-- 1. Basic Application Documents -->
        <div class="space-y-3 pt-2">
            <div class="flex items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <span
                        class="size-6 rounded-full bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 text-xs font-bold flex items-center justify-center shrink-0">1</span>
                    <span class="font-semibold text-sm sm:text-base text-zinc-900 dark:text-zinc-100">Basic Application
                        Documents</span>
                </div>
                @if (count($basic_docs_files) > 0)
                <flux:badge size="sm" color="green" icon="check">{{ count($basic_docs_files) }} Attached</flux:badge>
                @endif
            </div>

            <ul class="text-xs sm:text-sm text-zinc-500 list-disc list-inside space-y-0.5 pl-2 sm:pl-8">
                <li>Notarized Application Form</li>
                <li>Valid Identification Card (ID)</li>
                <li>Special Power of Attorney (if filed by representative)</li>
            </ul>

            <div class="pl-2 sm:pl-8 space-y-2">
                <label
                    class="flex flex-col items-center justify-center border-2 border-dashed border-zinc-200 dark:border-zinc-700 hover:border-zinc-400 dark:hover:border-zinc-500 rounded-xl p-3 sm:p-4 text-center cursor-pointer transition bg-zinc-50/50 dark:bg-zinc-800/20">
                    <flux:icon name="arrow-up-tray" class="size-5 text-zinc-400 mb-1" />
                    <span class="text-xs font-medium text-zinc-700 dark:text-zinc-300">Tap to upload basic
                        documents</span>
                    <span class="text-[10px] text-zinc-400">PNG, JPG, PDF up to 10MB</span>
                    <input type="file" wire:model="basic_docs_files" multiple accept="image/*,application/pdf"
                        class="hidden" />
                </label>

                @if ($basic_docs_files)
                <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-2 pt-1">
                    @foreach ($basic_docs_files as $index => $file)
                    <div
                        class="relative group rounded-lg overflow-hidden border border-zinc-200 dark:border-zinc-700 aspect-square bg-zinc-100 dark:bg-zinc-800">
                        @if(str_contains($file->getMimeType(), 'image'))
                        <img src="{{ $file->temporaryUrl() }}" class="size-full object-cover" />
                        @else
                        <div class="size-full flex flex-col items-center justify-center p-1 text-center">
                            <flux:icon name="document-text" class="size-6 text-zinc-400" />
                            <span class="text-[10px] truncate max-w-full text-zinc-500">{{
                                $file->getClientOriginalName() }}</span>
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
        </div>

        <flux:separator />

        <!-- 2. Proof of Right Over Land -->
        <div class="space-y-3">
            <div class="flex items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <span
                        class="size-6 rounded-full bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 text-xs font-bold flex items-center justify-center shrink-0">2</span>
                    <span class="font-semibold text-sm sm:text-base text-zinc-900 dark:text-zinc-100">Proof of Right
                        Over Land</span>
                </div>
                @if (count($land_right_files) > 0)
                <flux:badge size="sm" color="green" icon="check">{{ count($land_right_files) }} Attached</flux:badge>
                @endif
            </div>

            <ul class="text-xs sm:text-sm text-zinc-500 list-disc list-inside space-y-0.5 pl-2 sm:pl-8">
                <li>TCT (For titled properties)</li>
                <li>Certified Tax Declaration & Real Property Tax Clearance</li>
                <li>Notarized Contract of Lease or Authorization to Use Land</li>
            </ul>

            <div class="pl-2 sm:pl-8 space-y-2">
                <label
                    class="flex flex-col items-center justify-center border-2 border-dashed border-zinc-200 dark:border-zinc-700 hover:border-zinc-400 dark:hover:border-zinc-500 rounded-xl p-3 sm:p-4 text-center cursor-pointer transition bg-zinc-50/50 dark:bg-zinc-800/20">
                    <flux:icon name="arrow-up-tray" class="size-5 text-zinc-400 mb-1" />
                    <span class="text-xs font-medium text-zinc-700 dark:text-zinc-300">Tap to upload land proof
                        documents</span>
                    <span class="text-[10px] text-zinc-400">PNG, JPG, PDF up to 10MB</span>
                    <input type="file" wire:model="land_right_files" multiple accept="image/*,application/pdf"
                        class="hidden" />
                </label>

                @if ($land_right_files)
                <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-2 pt-1">
                    @foreach ($land_right_files as $index => $file)
                    <div
                        class="relative group rounded-lg overflow-hidden border border-zinc-200 dark:border-zinc-700 aspect-square bg-zinc-100 dark:bg-zinc-800">
                        @if(str_contains($file->getMimeType(), 'image'))
                        <img src="{{ $file->temporaryUrl() }}" class="size-full object-cover" />
                        @else
                        <div class="size-full flex flex-col items-center justify-center p-1 text-center">
                            <flux:icon name="document-text" class="size-6 text-zinc-400" />
                            <span class="text-[10px] truncate max-w-full text-zinc-500">{{
                                $file->getClientOriginalName() }}</span>
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
        </div>

        <flux:separator />

        <!-- 3. Plans and Technical Specs -->
        <div class="space-y-3">
            <div class="flex items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <span
                        class="size-6 rounded-full bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 text-xs font-bold flex items-center justify-center shrink-0">3</span>
                    <span class="font-semibold text-sm sm:text-base text-zinc-900 dark:text-zinc-100">Plans and
                        Technical Specs</span>
                </div>
                @if (count($plans_specs_files) > 0)
                <flux:badge size="sm" color="green" icon="check">{{ count($plans_specs_files) }} Attached</flux:badge>
                @endif
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
                    <span class="text-xs font-medium text-zinc-700 dark:text-zinc-300">Tap to upload plans and
                        specs</span>
                    <span class="text-[10px] text-zinc-400">PNG, JPG, PDF up to 10MB</span>
                    <input type="file" wire:model="plans_specs_files" multiple accept="image/*,application/pdf"
                        class="hidden" />
                </label>

                @if ($plans_specs_files)
                <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-2 pt-1">
                    @foreach ($plans_specs_files as $index => $file)
                    <div
                        class="relative group rounded-lg overflow-hidden border border-zinc-200 dark:border-zinc-700 aspect-square bg-zinc-100 dark:bg-zinc-800">
                        @if(str_contains($file->getMimeType(), 'image'))
                        <img src="{{ $file->temporaryUrl() }}" class="size-full object-cover" />
                        @else
                        <div class="size-full flex flex-col items-center justify-center p-1 text-center">
                            <flux:icon name="document-text" class="size-6 text-zinc-400" />
                            <span class="text-[10px] truncate max-w-full text-zinc-500">{{
                                $file->getClientOriginalName() }}</span>
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
        </div>

        <flux:separator />

        <!-- 4. Supporting Local Clearances -->
        <div class="space-y-3">
            <div class="flex items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <span
                        class="size-6 rounded-full bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 text-xs font-bold flex items-center justify-center shrink-0">4</span>
                    <span class="font-semibold text-sm sm:text-base text-zinc-900 dark:text-zinc-100">Supporting Local
                        Clearances</span>
                </div>
                @if (count($local_clearances_files) > 0)
                <flux:badge size="sm" color="green" icon="check">{{ count($local_clearances_files) }} Attached
                </flux:badge>
                @endif
            </div>

            <ul class="text-xs sm:text-sm text-zinc-500 list-disc list-inside space-y-0.5 pl-2 sm:pl-8">
                <li>Barangay Clearance for Locational Clearance</li>
                <li>Real Property Tax Clearance / Latest Tax Receipt</li>
            </ul>

            <div class="pl-2 sm:pl-8 space-y-2">
                <label
                    class="flex flex-col items-center justify-center border-2 border-dashed border-zinc-200 dark:border-zinc-700 hover:border-zinc-400 dark:hover:border-zinc-500 rounded-xl p-3 sm:p-4 text-center cursor-pointer transition bg-zinc-50/50 dark:bg-zinc-800/20">
                    <flux:icon name="arrow-up-tray" class="size-5 text-zinc-400 mb-1" />
                    <span class="text-xs font-medium text-zinc-700 dark:text-zinc-300">Tap to upload local
                        clearances</span>
                    <span class="text-[10px] text-zinc-400">PNG, JPG, PDF up to 10MB</span>
                    <input type="file" wire:model="local_clearances_files" multiple accept="image/*,application/pdf"
                        class="hidden" />
                </label>

                @if ($local_clearances_files)
                <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-2 pt-1">
                    @foreach ($local_clearances_files as $index => $file)
                    <div
                        class="relative group rounded-lg overflow-hidden border border-zinc-200 dark:border-zinc-700 aspect-square bg-zinc-100 dark:bg-zinc-800">
                        @if(str_contains($file->getMimeType(), 'image'))
                        <img src="{{ $file->temporaryUrl() }}" class="size-full object-cover" />
                        @else
                        <div class="size-full flex flex-col items-center justify-center p-1 text-center">
                            <flux:icon name="document-text" class="size-6 text-zinc-400" />
                            <span class="text-[10px] truncate max-w-full text-zinc-500">{{
                                $file->getClientOriginalName() }}</span>
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
        </div>

        <flux:separator />

        <!-- 5. Special Requirements -->
        <div class="space-y-3">
            <div class="flex items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <span
                        class="size-6 rounded-full bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 text-xs font-bold flex items-center justify-center shrink-0">5</span>
                    <span class="font-semibold text-sm sm:text-base text-zinc-900 dark:text-zinc-100">Special
                        Requirements (If Applicable)</span>
                </div>
                @if (count($special_req_files) > 0)
                <flux:badge size="sm" color="green" icon="check">{{ count($special_req_files) }} Attached</flux:badge>
                @endif
            </div>

            <ul class="text-xs sm:text-sm text-zinc-500 list-disc list-inside space-y-0.5 pl-2 sm:pl-8">
                <li>DTI / SEC Registration, HOA Clearance, DPWH, ECC / CNC, DAR Conversion</li>
            </ul>

            <div class="pl-2 sm:pl-8 space-y-2">
                <label
                    class="flex flex-col items-center justify-center border-2 border-dashed border-zinc-200 dark:border-zinc-700 hover:border-zinc-400 dark:hover:border-zinc-500 rounded-xl p-3 sm:p-4 text-center cursor-pointer transition bg-zinc-50/50 dark:bg-zinc-800/20">
                    <flux:icon name="arrow-up-tray" class="size-5 text-zinc-400 mb-1" />
                    <span class="text-xs font-medium text-zinc-700 dark:text-zinc-300">Tap to upload special requirement
                        documents</span>
                    <span class="text-[10px] text-zinc-400">PNG, JPG, PDF up to 10MB</span>
                    <input type="file" wire:model="special_req_files" multiple accept="image/*,application/pdf"
                        class="hidden" />
                </label>

                @if ($special_req_files)
                <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-2 pt-1">
                    @foreach ($special_req_files as $index => $file)
                    <div
                        class="relative group rounded-lg overflow-hidden border border-zinc-200 dark:border-zinc-700 aspect-square bg-zinc-100 dark:bg-zinc-800">
                        @if(str_contains($file->getMimeType(), 'image'))
                        <img src="{{ $file->temporaryUrl() }}" class="size-full object-cover" />
                        @else
                        <div class="size-full flex flex-col items-center justify-center p-1 text-center">
                            <flux:icon name="document-text" class="size-6 text-zinc-400" />
                            <span class="text-[10px] truncate max-w-full text-zinc-500">{{
                                $file->getClientOriginalName() }}</span>
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