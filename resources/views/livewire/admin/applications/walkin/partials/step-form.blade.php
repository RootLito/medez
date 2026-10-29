<form wire:submit.prevent="goToStep2" class="space-y-5 sm:space-y-6">

    <!-- Section 1: Applicant & Representation Information (4 Columns on Desktop) -->
    <flux:card class="p-5 sm:p-6 space-y-4">
        <div class="border-b border-zinc-200/80 dark:border-zinc-800 pb-3">
            <flux:heading size="lg" class="text-base sm:text-lg">1 to 6. Applicant & Representative Information
            </flux:heading>
            <flux:subheading class="text-xs">Identify the legal applicant or corporate body</flux:subheading>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <flux:field>
                <flux:label class="text-xs font-semibold">1. Applicant Full Name <span class="text-red-500">*</span>
                </flux:label>
                <flux:input wire:model="applicant_name" placeholder="Last Name, First Name, Middle Name" required />
                <flux:error name="applicant_name" />
            </flux:field>

            <flux:field>
                <flux:label class="text-xs font-semibold">2. Name of Corporation</flux:label>
                <flux:input wire:model="corporation_name" placeholder="Leave blank if personal" />
                <flux:error name="corporation_name" />
            </flux:field>

            <flux:field>
                <flux:label class="text-xs font-semibold">3. Applicant Address / Tel. No. <span
                        class="text-red-500">*</span></flux:label>
                <flux:input wire:model="representative_address_tel" placeholder="Address and contact" required />
                <flux:error name="representative_address_tel" />
            </flux:field>

            <flux:field>
                <flux:label class="text-xs font-semibold">4. Corporation Address / Tel.</flux:label>
                <flux:input wire:model="corporation_address_tel" placeholder="If applicable" />
                <flux:error name="corporation_address_tel" />
            </flux:field>
        </div>

        <div class="pt-3 border-t border-zinc-100 dark:border-zinc-800 space-y-3">
            <span class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider block">
                Authorized Representative (Optional)
            </span>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:field>
                    <flux:label class="text-xs">5. Name of Representative</flux:label>
                    <flux:input wire:model="authorized_representative_name" placeholder="Full name of representative" />
                    <flux:error name="authorized_representative_name" />
                </flux:field>

                <flux:field>
                    <flux:label class="text-xs">6. Address / Tel. No. of Representative</flux:label>
                    <flux:input wire:model="authorized_representative_address_tel"
                        placeholder="Contact address and mobile" />
                    <flux:error name="authorized_representative_address_tel" />
                </flux:field>
            </div>
        </div>
    </flux:card>

    <!-- Section 2: Project Specifications -->
    <flux:card class="p-5 sm:p-6 space-y-4">
        <div class="border-b border-zinc-200/80 dark:border-zinc-800 pb-3">
            <flux:heading size="lg" class="text-base sm:text-lg">7 to 10. Project Details & Measurements</flux:heading>
            <flux:subheading class="text-xs">Physical specifications of the intended structure and site boundaries
            </flux:subheading>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <flux:field>
                <flux:label class="text-xs font-semibold">7. Project Type <span class="text-red-500">*</span>
                </flux:label>
                <flux:input wire:model="project_type" placeholder="e.g. Residential, Commercial, Warehouse" required />
                <flux:error name="project_type" />
            </flux:field>

            <flux:field>
                <flux:label class="text-xs font-semibold">8. Project Nature <span class="text-red-500">*</span>
                </flux:label>
                <flux:input wire:model="project_nature" placeholder="e.g. New Construction, Expansion, Renovation"
                    required />
                <flux:error name="project_nature" />
            </flux:field>

            <flux:field class="md:col-span-3">
                <flux:label class="text-xs font-semibold">9. Complete Project Location / Address <span
                        class="text-red-500">*</span></flux:label>
                <flux:textarea wire:model="project_location" rows="2"
                    placeholder="Lot No., Block, Street, Barangay, City/Municipality" required />
                <flux:error name="project_location" />
            </flux:field>
        </div>

        <div class="space-y-2 pt-2 border-t border-zinc-100 dark:border-zinc-800">
            <flux:label class="text-xs font-semibold text-zinc-600 dark:text-zinc-300">10. Project Area & Measurements
            </flux:label>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
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
    <flux:card class="p-5 sm:p-6 space-y-4">
        <div class="border-b border-zinc-200/80 dark:border-zinc-800 pb-3">
            <flux:heading size="lg" class="text-base sm:text-lg">11 to 14. Right Over Land, Land Use & Cost
            </flux:heading>
            <flux:subheading class="text-xs">Tenancy, zoning classification, and capital investment</flux:subheading>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <flux:field>
                <flux:label class="text-xs font-semibold">11. Right Over Land <span class="text-red-500">*</span>
                </flux:label>
                <flux:select wire:model.live="right_over_land" required>
                    <flux:select.option value="owner">Owner</flux:select.option>
                    <flux:select.option value="heirs">Heirs</flux:select.option>
                    <flux:select.option value="lease">Lease</flux:select.option>
                    <flux:select.option value="others">Others (Specify)</flux:select.option>
                </flux:select>
            </flux:field>

            <flux:field>
                <flux:label class="text-xs font-semibold">12. Project Tenure <span class="text-red-500">*</span>
                </flux:label>
                <flux:select wire:model="project_tenure" required>
                    <flux:select.option value="permanent">Permanent</flux:select.option>
                    <flux:select.option value="temporary">Temporary</flux:select.option>
                </flux:select>
            </flux:field>

            <flux:field>
                <flux:label class="text-xs font-semibold">13. Existing Land Use <span class="text-red-500">*</span>
                </flux:label>
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

            <flux:field>
                <flux:label class="text-xs font-semibold">14. Project Cost (₱) <span class="text-red-500">*</span>
                </flux:label>
                <flux:input type="number" step="0.01" wire:model="project_cost" placeholder="0.00" required />
                <flux:error name="project_cost" />
            </flux:field>
        </div>

        @if ($right_over_land === 'others')
        <flux:field>
            <flux:label class="text-xs font-semibold">Specify Right Over Land <span class="text-red-500">*</span>
            </flux:label>
            <flux:input wire:model="right_over_land_specify" placeholder="Describe legal right" required />
            <flux:error name="right_over_land_specify" />
        </flux:field>
        @endif

        @if ($existing_land_use === 'agriculture')
        <flux:field>
            <flux:label class="text-xs font-semibold">Specify Agricultural Crops <span class="text-red-500">*</span>
            </flux:label>
            <flux:input wire:model="agriculture_crops_specify" placeholder="e.g. Rice, Corn, Coconut" required />
            <flux:error name="agriculture_crops_specify" />
        </flux:field>
        @endif

        @if ($existing_land_use === 'others')
        <flux:field>
            <flux:label class="text-xs font-semibold">Specify Existing Land Use <span class="text-red-500">*</span>
            </flux:label>
            <flux:input wire:model="existing_land_use_specify" placeholder="Describe the current land use" required />
            <flux:error name="existing_land_use_specify" />
        </flux:field>
        @endif

        <div class="pt-2">
            <flux:checkbox label="Is the project site currently tenanted?" wire:model="is_tenanted" />
        </div>
    </flux:card>

    <!-- Section 4: Legal & Official Proceedings -->
    <flux:card class="p-5 sm:p-6 space-y-4">
        <div class="border-b border-zinc-200/80 dark:border-zinc-800 pb-3">
            <flux:heading size="lg" class="text-base sm:text-lg">15 to 17. Legal Proceedings & Mode of Release
            </flux:heading>
            <flux:subheading class="text-xs">Prior administrative notices and decision release preference
            </flux:subheading>
        </div>

        <div class="space-y-4">
            <!-- Notice Question -->
            <div class="space-y-2">
                <flux:label class="text-xs sm:text-sm font-medium block">
                    15. Is this project the subject of written notice(s) from the Board or LGU to apply for Locational
                    Clearance?
                </flux:label>

                <flux:radio.group wire:model.live="has_written_notice" class="flex items-center gap-4">
                    <flux:radio value="1" label="Yes" />
                    <flux:radio value="0" label="No" />
                </flux:radio.group>

                @if ((bool) $has_written_notice)
                <div
                    class="p-4 rounded-xl bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-200 dark:border-zinc-700/60 grid grid-cols-1 sm:grid-cols-3 gap-4 mt-2">
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
                @endif
            </div>

            <div class="border-t border-zinc-100 dark:border-zinc-800 pt-3"></div>

            <!-- Related Action Question -->
            <div class="space-y-2">
                <flux:label class="text-xs sm:text-sm font-medium block">
                    16. Is the project applied for the subject of related action(s) with other offices of the Board/LGU?
                </flux:label>

                <flux:radio.group wire:model.live="has_related_action" class="flex items-center gap-4">
                    <flux:radio value="1" label="Yes" />
                    <flux:radio value="0" label="No" />
                </flux:radio.group>

                @if ((bool) $has_related_action)
                <div
                    class="p-4 rounded-xl bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-200 dark:border-zinc-700/60 grid grid-cols-1 sm:grid-cols-3 gap-4 mt-2">
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
                        <flux:input wire:model="action_taken" placeholder="Current status" required />
                    </flux:field>
                </div>
                @endif
            </div>

            <div class="border-t border-zinc-100 dark:border-zinc-800 pt-3"></div>

            <!-- Release Preference -->
            <flux:field class="max-w-md">
                <flux:label class="font-medium text-xs">17. Preferred Mode of Release <span
                        class="text-red-500">*</span></flux:label>
                <flux:select wire:model="preferred_mode_of_release" required>
                    <flux:select.option value="pick_up">Personal Pick-up at Municipal Planning Office
                    </flux:select.option>
                    <flux:select.option value="mail_applicant">By Mail addressed to Applicant</flux:select.option>
                    <flux:select.option value="mail_representative">By Mail addressed to Authorized Representative
                    </flux:select.option>
                </flux:select>
            </flux:field>
        </div>
    </flux:card>

    <!-- Navigation Buttons -->
    <div class="flex flex-col-reverse sm:flex-row items-center justify-between gap-3 pt-2">
        <flux:button href="{{ route('admin.applications.walkin-index') }}" wire:navigate icon="arrow-left" variant="subtle"
            class="w-full sm:w-auto">
            Cancel
        </flux:button>
        <flux:button type="submit" variant="primary" icon-trailing="arrow-right"
            class="w-full sm:w-auto justify-center">
            Proceed to Requirements Checklist
        </flux:button>
    </div>
</form>