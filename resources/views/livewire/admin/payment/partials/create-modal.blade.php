<flux:modal name="create-order-modal" class="w-full max-w-xl" backdrop="blur">
    <div class="space-y-4">
        <div>
            <flux:heading size="lg">Generate New Payment Order</flux:heading>
            <flux:subheading class="text-xs sm:text-sm">Compute regulatory zoning fees for a locational clearance
                application.</flux:subheading>
        </div>

        <form wire:submit="createPaymentOrder" class="space-y-4">
            <!-- Clearance Selector -->
            <div>
                <flux:select wire:model.live="application_id" label="Locational Clearance Application *"
                    placeholder="Select application">
                    @forelse($this->availableClearances as $clearance)
                    <flux:select.option value="{{ $clearance->id }}">
                        {{ $clearance->application_no ?? ('App #' . $clearance->id) }} - {{ $clearance->applicant_name
                        }} (Cost: ₱{{ number_format($clearance->project_cost, 2) }})
                    </flux:select.option>
                    @empty
                    <flux:select.option value="" disabled>No pending clearance applications found</flux:select.option>
                    @endforelse
                </flux:select>
            </div>

            <!-- Approver Selector -->
            <div>
                <flux:select wire:model="approved_by" label="Approved By (Zoning Administrator / MPDC Head) *"
                    placeholder="Select approving officer">
                    @foreach($this->approvers as $approver)
                    <flux:select.option value="{{ $approver->id }}">
                        {{ $approver->full_name ?? $approver->name }}
                        @if(!empty($approver->position))
                        ({{ $approver->position }})
                        @endif
                    </flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <flux:select wire:model="category_type" label="Zoning Category *">
                    <flux:select.option value="Residential">Residential</flux:select.option>
                    <flux:select.option value="Commercial">Commercial</flux:select.option>
                    <flux:select.option value="Industrial">Industrial</flux:select.option>
                    <flux:select.option value="Institutional">Institutional</flux:select.option>
                    <flux:select.option value="Special Use">Special Use</flux:select.option>
                </flux:select>

                <flux:input type="number" step="0.01" wire:model="project_cost" label="Project Cost (PHP) *" />
            </div>

            <!-- Fee Itemization -->
            <div
                class="p-3 bg-zinc-50 dark:bg-zinc-800/50 rounded-lg border border-zinc-200 dark:border-zinc-700 space-y-3">
                <div class="text-xs font-bold uppercase tracking-wider text-zinc-500">Zoning Fee Itemization</div>

                <div class="grid grid-cols-2 gap-3">
                    <flux:input type="number" step="0.01" wire:model="base_clearance_fee" label="Base Clearance Fee" />
                    <flux:input type="number" step="0.01" wire:model="excess_fee" label="Excess Fee" />
                    <flux:input type="number" step="0.01" wire:model="processing_fee" label="Processing Fee" />
                    <flux:input type="number" step="0.01" wire:model="inspection_fee" label="Inspection Fee" />
                    <flux:input type="number" step="0.01" wire:model="surcharge_fee" label="Surcharge / Penalty"
                        class="col-span-2" />
                </div>
            </div>

            <flux:input wire:model="remarks" label="Remarks (Optional)"
                placeholder="e.g. Assessed as per Municipal Zoning Ordinance" />

            <div class="flex justify-end gap-2 pt-2 border-t border-zinc-100 dark:border-zinc-800">
                <flux:modal.close>
                    <flux:button variant="subtle" size="sm">Cancel</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary" size="sm">
                    Generate Order
                </flux:button>
            </div>
        </form>
    </div>
</flux:modal>