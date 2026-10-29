<form wire:submit.prevent="goToStep2" class="space-y-4 sm:space-y-6">
    <!-- Section 1: Applicant Information -->
    <flux:card class="p-4 sm:p-6 space-y-4">
        <div class="border-b border-zinc-200/80 dark:border-zinc-800 pb-3">
            <flux:heading size="lg" class="text-base sm:text-lg">Applicant & Representative Information</flux:heading>
            <flux:subheading class="text-xs">Identify the legal applicant or corporate body</flux:subheading>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
            <flux:field>
                <flux:label>1. Applicant Full Name <span class="text-red-500">*</span></flux:label>
                <flux:input wire:model="applicant_name" placeholder="Last Name, First Name, Middle Name" required />
                <flux:error name="applicant_name" />
            </flux:field>

            <flux:field>
                <flux:label>2. Name of Corporation</flux:label>
                <flux:input wire:model="corporation_name" placeholder="Leave blank if personal" />
                <flux:error name="corporation_name" />
            </flux:field>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
            <flux:field>
                <flux:label>3. Applicant Address / Tel. No. <span class="text-red-500">*</span></flux:label>
                <flux:input wire:model="representative_address_tel" placeholder="Address and mobile/phone" required />
                <flux:error name="representative_address_tel" />
            </flux:field>

            <flux:field>
                <flux:label>4. Corporation Address / Tel. No.</flux:label>
                <flux:input wire:model="corporation_address_tel" placeholder="If applicable" />
                <flux:error name="corporation_address_tel" />
            </flux:field>
        </div>

        <div class="pt-3 border-t border-zinc-100 dark:border-zinc-800 space-y-3">
            <span class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider block">
                Authorized Representative (Optional)
            </span>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                <flux:field>
                    <flux:label>5. Name of Representative</flux:label>
                    <flux:input wire:model="authorized_representative_name" placeholder="Full name of representative" />
                    <flux:error name="authorized_representative_name" />
                </flux:field>

                <flux:field>
                    <flux:label>6. Address / Tel. No.</flux:label>
                    <flux:input wire:model="authorized_representative_address_tel"
                        placeholder="Representative contact info" />
                    <flux:error name="authorized_representative_address_tel" />
                </flux:field>
            </div>
        </div>
    </flux:card>

    <!-- Section 2: Project Specifications -->
    <flux:card class="p-4 sm:p-6 space-y-4">
        <div class="border-b border-zinc-200/80 dark:border-zinc-800 pb-3">
            <flux:heading size="lg" class="text-base sm:text-lg">Project Details & Measurements</flux:heading>
            <flux:subheading class="text-xs">Physical specifications of the intended structure</flux:subheading>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
            <flux:field>
                <flux:label>7. Project Type <span class="text-red-500">*</span></flux:label>
                <flux:input wire:model="project_type" placeholder="e.g. Residential, Commercial, Warehouse" required />
                <flux:error name="project_type" />
            </flux:field>

            <flux:field>
                <flux:label>8. Project Nature <span class="text-red-500">*</span></flux:label>
                <flux:input wire:model="project_nature" placeholder="e.g. New Construction, Expansion, Renovation"
                    required />
                <flux:error name="project_nature" />
            </flux:field>
        </div>

        <flux:field>
            <flux:label>9. Complete Project Location <span class="text-red-500">*</span></flux:label>
            <flux:textarea wire:model="project_location" rows="2"
                placeholder="Lot No., Block, Street, Barangay, City/Municipality" required />
            <flux:error name="project_location" />
        </flux:field>

        <div class="space-y-2 pt-1">
            <flux:label class="font-medium">10. Project Area & Measurements</flux:label>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
                <flux:field>
                    <flux:label class="text-xs text-zinc-500">Lot Area (sq.m) <span class="text-red-500">*</span>
                    </flux:label>
                    <flux:input type="number" step="0.01" wire:model="lot_area_sqm" placeholder="0.00" required />
                    <flux:error name="lot_area_sqm" />
                </flux:field>

                <flux:field>
                    <flux:label class="text-xs text-zinc-500">Building Area (sq.m)</flux:label>
                    <flux:input type="number" step="0.01" wire:model="building_area_sqm" placeholder="Optional" />
                    <flux:error name="building_area_sqm" />
                </flux:field>

                <flux:field>
                    <flux:label class="text-xs text-zinc-500">Perimeter (meters)</flux:label>
                    <flux:input type="number" step="0.01" wire:model="perimeter_m" placeholder="Optional" />
                    <flux:error name="perimeter_m" />
                </flux:field>
            </div>
        </div>
    </flux:card>

    <!-- Section 3: Right Over Land, Land Use & Cost -->
    <flux:card class="p-4 sm:p-6 space-y-4">
        <div class="border-b border-zinc-200/80 dark:border-zinc-800 pb-3">
            <flux:heading size="lg" class="text-base sm:text-lg">Right Over Land, Land Use & Cost</flux:heading>
            <flux:subheading class="text-xs">Legal tenancy, zoning classification, and estimated investment
            </flux:subheading>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
            <flux:field>
                <flux:label>11. Right Over Land <span class="text-red-500">*</span></flux:label>
                <flux:select wire:model.live="right_over_land" required>
                    <flux:select.option value="owner">Owner</flux:select.option>
                    <flux:select.option value="heirs">Heirs</flux:select.option>
                    <flux:select.option value="lease">Lease</flux:select.option>
                    <flux:select.option value="others">Others (Specify)</flux:select.option>
                </flux:select>
            </flux:field>

            <flux:field>
                <flux:label>12. Project Tenure <span class="text-red-500">*</span></flux:label>
                <flux:select wire:model="project_tenure" required>
                    <flux:select.option value="permanent">Permanent</flux:select.option>
                    <flux:select.option value="temporary">Temporary</flux:select.option>
                </flux:select>
            </flux:field>
        </div>

        @if ($right_over_land === 'others')
        <flux:field>
            <flux:label>Specify Right Over Land <span class="text-red-500">*</span></flux:label>
            <flux:input wire:model="right_over_land_specify" placeholder="Describe your legal rights" required />
            <flux:error name="right_over_land_specify" />
        </flux:field>
        @endif

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
            <flux:field>
                <flux:label>13. Existing Land Use <span class="text-red-500">*</span></flux:label>
                <flux:select wire:model.live="existing_land_use" required>
                    <flux:select.option value="residential">Residential</flux:select.option>
                    <flux:select.option value="commercial">Commercial</flux:select.option>
                    <flux:select.option value="institutional">Institutional</flux:select.option>
                    <flux:select.option value="industrial">Industrial</flux:select.option>
                    <flux:select.option value="vacant_idle">Vacant / Idle</flux:select.option>
                    <flux:select.option value="agriculture">Agriculture</flux:select.option>
                    <flux:select.option value="others">Others</flux:select.option>
                </flux:select>
            </flux:field>

            <div class="flex items-center pt-2 sm:pt-6">
                <flux:checkbox label="Is the project site currently tenanted?" wire:model="is_tenanted" />
            </div>
        </div>

        @if ($existing_land_use === 'agriculture')
        <flux:field>
            <flux:label>Specify Agricultural Crops <span class="text-red-500">*</span></flux:label>
            <flux:input wire:model="agriculture_crops_specify" placeholder="e.g. Rice, Corn, Coconut" required />
            <flux:error name="agriculture_crops_specify" />
        </flux:field>
        @endif

        @if ($existing_land_use === 'others')
        <flux:field>
            <flux:label>Specify Existing Land Use <span class="text-red-500">*</span></flux:label>
            <flux:input wire:model="existing_land_use_specify" placeholder="Describe the current land use" required />
            <flux:error name="existing_land_use_specify" />
        </flux:field>
        @endif

        <div class="pt-1">
            <flux:field>
                <flux:label>14. Project Cost (in Philippine Pesos) <span class="text-red-500">*</span></flux:label>
                <flux:input type="number" step="0.01" wire:model="project_cost" placeholder="₱ 0.00" required />
                <flux:error name="project_cost" />
            </flux:field>
        </div>
    </flux:card>

    <!-- Section 4: Legal Proceedings -->
    <flux:card class="p-4 sm:p-6 space-y-4">
        <div class="border-b border-zinc-200/80 dark:border-zinc-800 pb-3">
            <flux:heading size="lg" class="text-base sm:text-lg">Written Notices & Related Actions</flux:heading>
            <flux:subheading class="text-xs">Prior administrative proceedings</flux:subheading>
        </div>

        <div class="space-y-3">
            <flux:label class="text-xs sm:text-sm font-medium leading-relaxed block">
                15. Is this project the subject of written notice(s) from the Board or LGU to apply for Locational
                Clearance?
            </flux:label>

            <flux:radio.group wire:model.live="has_written_notice" class="flex items-center gap-4">
                <flux:radio value="1" label="Yes" />
                <flux:radio value="0" label="No" />
            </flux:radio.group>

            @if ((bool) $has_written_notice)
            <div
                class="p-3.5 sm:p-4 rounded-xl bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-200 dark:border-zinc-700/60 space-y-3 mt-2">
                <span class="text-xs font-semibold text-zinc-600 dark:text-zinc-300">Written Notice Details</span>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <flux:field>
                        <flux:label class="text-xs">Issuing Officer <span class="text-red-500">*</span></flux:label>
                        <flux:input wire:model="notice_issuing_officer" placeholder="Name/Title" required />
                    </flux:field>
                    <flux:field>
                        <flux:label class="text-xs">Order / Notice No. <span class="text-red-500">*</span></flux:label>
                        <flux:input wire:model="notice_order_number" placeholder="Reference #" required />
                    </flux:field>
                    <flux:field>
                        <flux:label class="text-xs">Date of Notice <span class="text-red-500">*</span></flux:label>
                        <flux:input type="date" wire:model="notice_date" required />
                    </flux:field>
                </div>
            </div>
            @endif
        </div>

        <div class="border-t border-zinc-100 dark:border-zinc-800 pt-3"></div>

        <div class="space-y-3">
            <flux:label class="text-xs sm:text-sm font-medium leading-relaxed block">
                16. Is the project applied for the subject of related action(s) with other offices of the Board/LGU?
            </flux:label>

            <flux:radio.group wire:model.live="has_related_action" class="flex items-center gap-4">
                <flux:radio value="1" label="Yes" />
                <flux:radio value="0" label="No" />
            </flux:radio.group>

            @if ((bool) $has_related_action)
            <div
                class="p-3.5 sm:p-4 rounded-xl bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-200 dark:border-zinc-700/60 space-y-3 mt-2">
                <span class="text-xs font-semibold text-zinc-600 dark:text-zinc-300">Related Action Details</span>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <flux:field>
                        <flux:label class="text-xs">Office Filed <span class="text-red-500">*</span></flux:label>
                        <flux:input wire:model="action_office_filed" placeholder="Department/Agency" required />
                    </flux:field>
                    <flux:field>
                        <flux:label class="text-xs">Date Filed <span class="text-red-500">*</span></flux:label>
                        <flux:input type="date" wire:model="action_date_filed" required />
                    </flux:field>
                    <flux:field>
                        <flux:label class="text-xs">Action Taken <span class="text-red-500">*</span></flux:label>
                        <flux:input wire:model="action_taken" placeholder="Current resolution" required />
                    </flux:field>
                </div>
            </div>
            @endif
        </div>
    </flux:card>

    <!-- Section 5: Mode of Release -->
    <flux:card class="p-4 sm:p-6 space-y-3">
        <flux:field>
            <flux:label class="font-medium">17. Preferred Mode of Release <span class="text-red-500">*</span>
            </flux:label>
            <flux:select wire:model="preferred_mode_of_release" required>
                <flux:select.option value="pick_up">Personal Pick-up at Municipal Planning Office</flux:select.option>
                <flux:select.option value="mail_applicant">By Mail addressed to Applicant</flux:select.option>
                <flux:select.option value="mail_representative">By Mail addressed to Authorized Representative
                </flux:select.option>
            </flux:select>
        </flux:field>
    </flux:card>

    <div class="flex flex-col-reverse sm:flex-row items-center justify-end gap-3 pt-2">
        <flux:button href="{{ route('client.applications.index') }}" wire:navigate variant="subtle"
            class="w-full sm:w-auto">
            Cancel
        </flux:button>
        <flux:button type="submit" variant="primary" icon-trailing="arrow-right"
            class="w-full sm:w-auto justify-center">
            Next: Requirements Checklist
        </flux:button>
    </div>
</form>