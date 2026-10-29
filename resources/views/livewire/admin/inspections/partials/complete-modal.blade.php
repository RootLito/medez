<flux:modal name="complete-modal" class="w-full max-w-xl" backdrop="blur">
    <div class="space-y-4">
        <div>
            <flux:heading size="lg">
                {{ $this->completingInspection?->status === 'completed' ? 'Inspection Report & Findings' : 'Log Site
                Inspection Findings' }}
            </flux:heading>
            <flux:subheading class="text-xs sm:text-sm">
                Application: <span class="font-mono font-semibold">{{
                    $this->completingInspection?->locClearance?->application_no }}</span>
                ({{ $this->completingInspection?->locClearance?->applicant_name }})
            </flux:subheading>
        </div>

        @if($this->completingInspection?->status === 'completed')
        <!-- Read-only View for Completed Inspections -->
        <div class="space-y-3 text-sm">
            <div
                class="p-3 bg-zinc-50 dark:bg-zinc-800/50 rounded-lg space-y-2 border border-zinc-200 dark:border-zinc-700">
                <div class="flex justify-between items-center">
                    <span class="text-xs font-bold uppercase text-zinc-500">Compliance Outcome:</span>
                    @php
                    $compColor = match($this->completingInspection->compliance_status) {
                    'compliant' => 'green',
                    'non_compliant' => 'red',
                    'conditional' => 'yellow',
                    default => 'zinc',
                    };
                    @endphp
                    <flux:badge size="sm" :color="$compColor">
                        {{ ucfirst(str_replace('_', ' ', $this->completingInspection->compliance_status)) }}
                    </flux:badge>
                </div>

                <div>
                    <span class="text-xs font-semibold text-zinc-500 block">Inspected On:</span>
                    <span class="text-zinc-800 dark:text-zinc-200">{{
                        $this->completingInspection->inspected_at?->format('F d, Y h:i A') ?? 'N/A' }}</span>
                </div>

                <div>
                    <span class="text-xs font-semibold text-zinc-500 block">Ocular Findings & Setback Checks:</span>
                    <p class="text-zinc-800 dark:text-zinc-200 whitespace-pre-line">{{
                        $this->completingInspection->findings }}</p>
                </div>

                @if($this->completingInspection->recommendations)
                <div>
                    <span class="text-xs font-semibold text-zinc-500 block">Zoning Recommendations:</span>
                    <p class="text-zinc-800 dark:text-zinc-200 whitespace-pre-line">{{
                        $this->completingInspection->recommendations }}</p>
                </div>
                @endif
            </div>

            <div class="flex justify-end pt-2">
                <flux:modal.close>
                    <flux:button variant="subtle" size="sm">Close</flux:button>
                </flux:modal.close>
            </div>
        </div>
        @else
        <!-- Form to Record Findings -->
        <form wire:submit="saveFindings" class="space-y-4">
            <div>
                <flux:select wire:model="compliance_status" label="Zoning Compliance Result *">
                    <flux:select.option value="compliant">Compliant (Conforms to setbacks & zoning ordinance)
                    </flux:select.option>
                    <flux:select.option value="conditional">Conditional (Minor adjustments required)
                    </flux:select.option>
                    <flux:select.option value="non_compliant">Non-Compliant (Violates setbacks / land use
                        classification)</flux:select.option>
                </flux:select>
            </div>

            <div>
                <flux:textarea wire:model="findings" label="Ocular Findings & Observations *" rows="4"
                    placeholder="Detail front/rear/side setback measurements, actual site use, proximity to waterways/easements, and road right-of-way." />
            </div>

            <div>
                <flux:textarea wire:model="recommendations" label="Zoning Recommendations (Optional)" rows="2"
                    placeholder="e.g. Recommend issuance of Locational Clearance subject to 3-meter setback compliance." />
            </div>

            <div class="flex justify-end gap-2 pt-2 border-t border-zinc-100 dark:border-zinc-800">
                <flux:modal.close>
                    <flux:button variant="subtle" size="sm">Cancel</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary" size="sm">
                    Submit & Mark Completed
                </flux:button>
            </div>
        </form>
        @endif
    </div>
</flux:modal>