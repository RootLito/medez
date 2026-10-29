<flux:modal name="schedule-modal" class="w-full max-w-xl" backdrop="blur">
    <div class="space-y-4">
        <div>
            <flux:heading size="lg">Schedule Site Inspection</flux:heading>
            <flux:subheading class="text-xs sm:text-sm">Dispatch zoning inspectors to verify boundary setbacks and land
                use compliance.</flux:subheading>
        </div>

        <form wire:submit="createSchedule" class="space-y-4">
            <!-- Clearance Selector -->
            <div>
                <flux:select wire:model="application_id" label="Clearance Application *"
                    placeholder="Select application">
                    @foreach($this->availableClearances as $clearance)
                    <flux:select.option value="{{ $clearance->id }}">
                        {{ $clearance->application_no ?? ('App #' . $clearance->id) }} - {{ $clearance->applicant_name
                        }} ({{ $clearance->project_location }})
                    </flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            <!-- Inspector Selector -->
            <div>
                <flux:select wire:model="inspector_id" label="Assigned Inspector *" placeholder="Select inspector">
                    @foreach($this->inspectors as $inspector)
                    <flux:select.option value="{{ $inspector->id }}">
                        {{ $inspector->full_name ?? $inspector->name }}
                        @if(!empty($inspector->position))
                        ({{ $inspector->position }})
                        @endif
                    </flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            <!-- Inspection Type -->
            <div>
                <flux:select wire:model="inspection_type" label="Inspection Type *">
                    <flux:select.option value="Ocular Site Inspection">Ocular Site Inspection</flux:select.option>
                    <flux:select.option value="Setback & Boundary Verification">Setback & Boundary Verification
                    </flux:select.option>
                    <flux:select.option value="Pre-Approval Verification">Pre-Approval Verification</flux:select.option>
                    <flux:select.option value="Post-Construction Compliance">Post-Construction Compliance
                    </flux:select.option>
                    <flux:select.option value="Complaint Investigation">Complaint Investigation</flux:select.option>
                </flux:select>
            </div>

            <!-- Date and Time -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <flux:input type="date" wire:model="scheduled_date" label="Inspection Date *" />
                <flux:input type="time" wire:model="scheduled_time" label="Target Time" />
            </div>

            <flux:input wire:model="remarks" label="Special Instructions / Access Notes"
                placeholder="e.g. Contact applicant prior to gate entry, check rear easement" />

            <div class="flex justify-end gap-2 pt-2 border-t border-zinc-100 dark:border-zinc-800">
                <flux:modal.close>
                    <flux:button variant="subtle" size="sm">Cancel</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary" size="sm">
                    Schedule Visit
                </flux:button>
            </div>
        </form>
    </div>
</flux:modal>