<flux:modal name="summary-print-modal" class="w-full max-w-2xl" backdrop="blur">
    <div class="space-y-4">
        <!-- Header -->
        <div class="border-b border-zinc-200 dark:border-zinc-700 pb-3 pr-8 print:hidden">
            <flux:heading size="lg">Official Accomplishment Report</flux:heading>
            <flux:subheading class="text-xs sm:text-sm">Summary document for Municipal Planning & Development
                Coordinator archives.</flux:subheading>
        </div>

        <!-- Printable Document Area -->
        <div id="printable-summary-report"
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
                    Geographic Information Division</p>
                <p
                    class="text-sm font-bold tracking-widest mt-3 bg-zinc-100 py-1 border border-dashed border-zinc-400 uppercase">
                    ACCOMPLISHMENT REPORT ON LOCATIONAL CLEARANCES
                </p>
                <p class="text-xs text-zinc-600 font-medium pt-1">
                    Coverage Period: {{ \Carbon\Carbon::parse($this->dateFrom)->format('F d, Y') }} — {{
                    \Carbon\Carbon::parse($this->dateTo)->format('F d, Y') }}
                </p>
            </div>

            <!-- Key Indicators Table -->
            <div class="space-y-2">
                <span class="font-bold uppercase tracking-wider text-xs text-zinc-700 block border-b pb-1">
                    I. Summary of Regulatory Operations:
                </span>
                <table class="w-full text-xs border border-collapse">
                    <tbody class="divide-y">
                        <tr class="bg-zinc-50 font-medium">
                            <td class="p-2 border-r">Total Locational Clearance Applications Received</td>
                            <td class="p-2 text-right font-mono font-bold">{{ $this->kpi['total_apps'] }}</td>
                        </tr>
                        <tr>
                            <td class="p-2 border-r pl-6">• Walk-In Submissions (Physical Counter)</td>
                            <td class="p-2 text-right font-mono">{{ $this->kpi['walk_in'] }}</td>
                        </tr>
                        <tr>
                            <td class="p-2 border-r pl-6">• Online Submissions (Portal)</td>
                            <td class="p-2 text-right font-mono">{{ $this->kpi['online'] }}</td>
                        </tr>
                        <tr>
                            <td class="p-2 border-r pl-6 text-emerald-800 font-semibold">• Approved Locational
                                Clearances Issued</td>
                            <td class="p-2 text-right font-mono text-emerald-800 font-bold">{{
                                $this->kpi['approved_apps'] }}</td>
                        </tr>
                        <tr>
                            <td class="p-2 border-r pl-6">• Pending Zoning Evaluation / Verification</td>
                            <td class="p-2 text-right font-mono">{{ $this->kpi['pending_apps'] }}</td>
                        </tr>
                        <tr class="bg-zinc-50 font-medium">
                            <td class="p-2 border-r">Ocular Site Inspections Successfully Conducted</td>
                            <td class="p-2 text-right font-mono font-bold">{{ $this->kpi['inspections_done'] }}</td>
                        </tr>
                        <tr class="bg-zinc-100 font-bold border-t-2 border-black">
                            <td class="p-2 border-r uppercase">Total Zoning Revenue Remitted to Treasury</td>
                            <td class="p-2 text-right font-mono">₱{{ number_format($this->kpi['revenue'], 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Category Breakdown Table -->
            <div class="space-y-2">
                <span class="font-bold uppercase tracking-wider text-xs text-zinc-700 block border-b pb-1">
                    II. Breakdown by Land Use / Zoning Classification:
                </span>
                <table class="w-full text-xs border border-collapse">
                    <thead>
                        <tr class="bg-zinc-100 text-left border-b">
                            <th class="p-2 border-r">Classification</th>
                            <th class="p-2 text-right">Clearance Count</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($this->categoryBreakdown as $row)
                        <tr>
                            <td class="p-2 border-r capitalize">{{ $row->category ?: 'General' }}</td>
                            <td class="p-2 text-right font-mono">{{ $row->count }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="2" class="p-2 text-center text-zinc-400">No applications recorded.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Signatures -->
            <div class="grid grid-cols-2 gap-8 pt-8 text-center text-xs">
                <div>
                    <p class="font-bold border-b border-black uppercase pb-1">
                        {{ auth('admin')->user()?->full_name ?? auth('admin')->user()?->name ?? 'MPDC REPORTING OFFICER'
                        }}
                    </p>
                    <p class="text-zinc-500 mt-1">Prepared By (Zoning Evaluator / Staff)</p>
                </div>
                <div>
                    <p class="font-bold border-b border-black uppercase pb-1">
                        ENGR. [MPDC HEAD NAME]
                    </p>
                    <p class="text-zinc-500 mt-1">Approved By (Municipal Planning & Dev. Coordinator)</p>
                </div>
            </div>
        </div>

        <!-- Full-Width Bottom Print Button -->
        <div class="pt-2 print:hidden">
            <flux:button onclick="window.print()" variant="primary" class="w-full" icon="printer">
                Print Accomplishment Report
            </flux:button>
        </div>
    </div>

    <style>
        @media print {
            body * {
                visibility: hidden !important;
            }

            #printable-summary-report,
            #printable-summary-report * {
                visibility: visible !important;
            }

            #printable-summary-report {
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