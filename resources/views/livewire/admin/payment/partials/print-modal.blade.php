<flux:modal name="print-modal" class="w-full max-w-2xl" backdrop="blur">
    <div class="space-y-4">
        <!-- Header with Subheading -->
        <div class="border-b border-zinc-200 dark:border-zinc-700 pb-3 pr-8 print:hidden">
            <flux:heading size="lg">Order of Payment Preview</flux:heading>
            <flux:subheading class="text-xs sm:text-sm">Review assessment details before issuing the printed slip.
            </flux:subheading>
        </div>

        @if($this->printOrder)
        <!-- Printable Official MPDC Document -->
        <div id="printable-order"
            class="bg-white text-zinc-900 p-4 space-y-5 text-sm rounded-lg border border-zinc-200 dark:border-zinc-700">
            <div class="text-center space-y-0.5">
                <p class="text-xs uppercase tracking-widest text-zinc-500">Republic of the Philippines</p>
                <p class="text-xs uppercase font-medium">Province of Davao del Sur</p>
                <p class="text-sm font-bold uppercase">Municipality of Malalag</p>
                <h2
                    class="text-base font-extrabold uppercase mt-2 text-zinc-900 border-b-2 border-black inline-block pb-0.5">
                    Office of the Municipal Planning & Development Coordinator (MPDC)
                </h2>
                <p class="text-xs font-semibold tracking-wider text-zinc-600 uppercase">Zoning Administration Division
                </p>
                <p
                    class="text-sm font-bold tracking-widest mt-3 bg-zinc-100 py-1 border border-dashed border-zinc-400 uppercase">
                    ORDER OF PAYMENT / ASSESSMENT SLIP
                </p>
            </div>

            <div class="grid grid-cols-2 gap-4 text-xs border p-3 rounded">
                <div>
                    <p><span class="font-bold">Assessment No:</span> <span class="font-mono">{{
                            $this->printOrder->assessment_no }}</span></p>
                    <p><span class="font-bold">Date Assessed:</span> {{ $this->printOrder->created_at->format('F d, Y')
                        }}</p>
                    <p><span class="font-bold">Applicant:</span> {{ $this->printOrder->locClearance?->applicant_name }}
                    </p>
                </div>
                <div>
                    <p><span class="font-bold">Zoning Category:</span> {{ ucfirst($this->printOrder->category_type ??
                        'N/A') }}</p>
                    <p><span class="font-bold">Declared Project Cost:</span> ₱{{
                        number_format($this->printOrder->project_cost, 2) }}</p>
                    <p><span class="font-bold">Status:</span> {{ strtoupper($this->printOrder->status) }}</p>
                </div>
            </div>

            <div>
                <table class="w-full text-xs border border-collapse">
                    <thead>
                        <tr class="bg-zinc-100 text-left border-b">
                            <th class="p-2 border-r">Particulars / Regulatory Fees</th>
                            <th class="p-2 text-right w-36">Amount (PHP)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr>
                            <td class="p-2 border-r">Base Clearance Fee</td>
                            <td class="p-2 text-right">₱{{ number_format($this->printOrder->base_clearance_fee, 2) }}
                            </td>
                        </tr>
                        <tr>
                            <td class="p-2 border-r">Excess Fee (Calculated on Project Cost)</td>
                            <td class="p-2 text-right">₱{{ number_format($this->printOrder->excess_fee, 2) }}</td>
                        </tr>
                        <tr class="bg-zinc-50 font-semibold">
                            <td class="p-2 border-r">Total Clearance Fee</td>
                            <td class="p-2 text-right">₱{{ number_format($this->printOrder->total_clearance_fee, 2) }}
                            </td>
                        </tr>
                        <tr>
                            <td class="p-2 border-r">Processing Fee</td>
                            <td class="p-2 text-right">₱{{ number_format($this->printOrder->processing_fee, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="p-2 border-r">Site Inspection Fee</td>
                            <td class="p-2 text-right">₱{{ number_format($this->printOrder->inspection_fee, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="p-2 border-r">Surcharges / Penalties</td>
                            <td class="p-2 text-right">₱{{ number_format($this->printOrder->surcharge_fee, 2) }}</td>
                        </tr>
                        <tr class="bg-zinc-100 font-bold text-sm border-t-2 border-black">
                            <td class="p-2.5 border-r uppercase">Grand Total Assessed</td>
                            <td class="p-2.5 text-right font-mono">₱{{ number_format($this->printOrder->grand_total, 2)
                                }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="border p-3 text-xs rounded bg-zinc-50 space-y-1">
                <span class="font-bold uppercase tracking-wider text-[11px] text-zinc-600 block border-b pb-1">
                    To be accomplished by the Municipal Treasurer's Office (MTO):
                </span>
                <div class="grid grid-cols-3 gap-2 pt-1 font-mono">
                    <div>OR Number: <span class="font-bold underline">{{ $this->printOrder->or_no ?? '_______________'
                            }}</span></div>
                    <div>Date Paid: <span class="font-bold underline">{{ $this->printOrder->payment_date?->format('M d,
                            Y') ?? '_______________' }}</span></div>
                    <div>Amount Paid: <span class="font-bold underline">₱{{
                            number_format($this->printOrder->grand_total, 2) }}</span></div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-8 pt-6 text-center text-xs">
                <div>
                    <p class="font-bold border-b border-black uppercase pb-1">{{ $this->printOrder->assessor?->name ??
                        'ZONING OFFICER' }}</p>
                    <p class="text-zinc-500 mt-1">Assessed By (Zoning Evaluator)</p>
                </div>
                <div>
                    <p class="font-bold border-b border-black uppercase pb-1">{{ $this->printOrder->approver?->name ??
                        'MUNICIPAL PLANNING & DEV. COORDINATOR' }}</p>
                    <p class="text-zinc-500 mt-1">Approved By (Zoning Administrator)</p>
                </div>
            </div>
        </div>

        <!-- Full-Width Bottom Print Button -->
        <div class="pt-2 print:hidden">
            <flux:button onclick="window.print()" variant="primary" class="w-full" icon="printer">
                Print Slip
            </flux:button>
        </div>
        @endif
    </div>

    <style>
        @media print {
            body * {
                visibility: hidden !important;
            }

            #printable-order,
            #printable-order * {
                visibility: visible !important;
            }

            #printable-order {
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