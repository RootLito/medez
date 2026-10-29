<flux:modal name="log-detail-modal" class="w-full max-w-2xl" backdrop="blur">
    <div class="space-y-4">
        <!-- Header with Subheading -->
        <div class="border-b border-zinc-200 dark:border-zinc-700 pb-3 pr-8 print:hidden">
            <flux:heading size="lg">Site Inspection Report</flux:heading>
            <flux:subheading class="text-xs sm:text-sm">Official ocular inspection details and setback verification
                notes.</flux:subheading>
        </div>

        @if($this->selectedLog)
        <!-- Printable Ocular Inspection Document -->
        <div id="printable-inspection-report"
            class="bg-white text-zinc-900 p-4 space-y-5 text-sm rounded-lg border border-zinc-200 dark:border-zinc-700">
            <!-- LGU Letterhead -->
            <div class="text-center space-y-0.5">
                <p class="text-xs uppercase tracking-widest text-zinc-500">Republic of the Philippines</p>
                <p class="text-xs uppercase font-medium">Province of Davao del Sur</p>
                <p class="text-sm font-bold uppercase">Municipality of Malalag</p>
                <h2
                    class="text-base font-extrabold uppercase mt-2 text-zinc-900 border-b-2 border-black inline-block pb-0.5">
                    Office of the Municipal Planning & Development Coordinator (MPDC)
                </h2>
                <p class="text-xs font-semibold tracking-wider text-zinc-600 uppercase">Zoning Administration &
                    Enforcement Division</p>
                <p
                    class="text-sm font-bold tracking-widest mt-3 bg-zinc-100 py-1 border border-dashed border-zinc-400 uppercase">
                    OCULAR SITE INSPECTION & SETBACK VERIFICATION REPORT
                </p>
            </div>

            <!-- Meta Details -->
            <div class="grid grid-cols-2 gap-4 text-xs border p-3 rounded">
                <div>
                    <p><span class="font-bold">Inspection No:</span> <span class="font-mono">{{
                            $this->selectedLog->inspection_no }}</span></p>
                    <p><span class="font-bold">Date Inspected:</span> {{ $this->selectedLog->inspected_at?->format('F d,
                        Y h:i A') ?? $this->selectedLog->scheduled_date?->format('F d, Y') }}</p>
                    <p><span class="font-bold">Applicant:</span> {{ $this->selectedLog->locClearance?->applicant_name }}
                    </p>
                    <p><span class="font-bold">Clearance App No:</span> <span class="font-mono">{{
                            $this->selectedLog->locClearance?->application_no ?? 'Unassigned' }}</span></p>
                </div>
                <div>
                    <p><span class="font-bold">Inspection Type:</span> {{ $this->selectedLog->inspection_type }}</p>
                    <p><span class="font-bold">Project Location:</span> {{
                        $this->selectedLog->locClearance?->project_location ?? 'N/A' }}</p>
                    <p><span class="font-bold">Zoning Category:</span> {{
                        ucfirst($this->selectedLog->locClearance?->existing_land_use ?? 'Standard') }}</p>
                    <p><span class="font-bold">Compliance:</span>
                        <span
                            class="font-extrabold uppercase {{ $this->selectedLog->compliance_status === 'compliant' ? 'text-emerald-700' : ($this->selectedLog->compliance_status === 'non_compliant' ? 'text-red-700' : 'text-amber-700') }}">
                            {{ str_replace('_', ' ', $this->selectedLog->compliance_status) }}
                        </span>
                    </p>
                </div>
            </div>

            <!-- Ocular Findings & Setbacks -->
            <div class="space-y-2">
                <span class="font-bold uppercase tracking-wider text-xs text-zinc-700 block border-b pb-1">
                    1. Ocular Site Observations & Setback Verification:
                </span>
                <div class="p-3 bg-zinc-50 border rounded text-xs whitespace-pre-line leading-relaxed font-sans">
                    {{ $this->selectedLog->findings ?: 'No explicit observations recorded.' }}
                </div>
            </div>

            <!-- Zoning Recommendations -->
            <div class="space-y-2">
                <span class="font-bold uppercase tracking-wider text-xs text-zinc-700 block border-b pb-1">
                    2. Zoning Officer Recommendations:
                </span>
                <div class="p-3 bg-zinc-50 border rounded text-xs whitespace-pre-line leading-relaxed font-sans">
                    {{ $this->selectedLog->recommendations ?: 'No special conditions or recommendations required.' }}
                </div>
            </div>

            <!-- Signatories -->
            <div class="grid grid-cols-2 gap-8 pt-6 text-center text-xs">
                <div>
                    <p class="font-bold border-b border-black uppercase pb-1">
                        {{ $this->selectedLog->inspector?->full_name ?? $this->selectedLog->inspector?->name ?? 'ZONING
                        INSPECTOR' }}
                    </p>
                    <p class="text-zinc-500 mt-1">Conducted By (Zoning Inspector)</p>
                </div>
                <div>
                    <p class="font-bold border-b border-black uppercase pb-1">
                        ENGR. [MPDC HEAD NAME]
                    </p>
                    <p class="text-zinc-500 mt-1">Noted By (Zoning Administrator / MPDC)</p>
                </div>
            </div>
        </div>

        <!-- Full-Width Bottom Print Button -->
        <div class="pt-2 print:hidden">
            <flux:button onclick="window.print()" variant="primary" class="w-full" icon="printer">
                Print Inspection Report
            </flux:button>
        </div>
        @endif
    </div>

    <!-- Print CSS -->
    <style>
        @media print {
            body * {
                visibility: hidden !important;
            }

            #printable-inspection-report,
            #printable-inspection-report * {
                visibility: visible !important;
            }

            #printable-inspection-report {
                position: absolute !important;
                left: 0 !important;
                top: 0 !important;
                width: 100% !important;
                padding: 10px !important;
                border: none !important;
            }
        }
    </style>
</flux:modal>