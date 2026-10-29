<div class="grid grid-cols-1 lg:grid-cols-2 gap-5 sm:gap-6">
    <!-- Status Breakdown -->
    <flux:card class="p-5 space-y-4">
        <div>
            <flux:heading size="base" class="font-bold">Application Status Breakdown</flux:heading>
            <flux:subheading class="text-xs">Distribution of applications within the selected timeframe.
            </flux:subheading>
        </div>

        <div class="space-y-3">
            @php
            $statusList = [
            'approved' => ['label' => 'Approved', 'color' => 'bg-emerald-500', 'badge' => 'green', 'count' =>
            ($this->statusBreakdown['approve'] ?? 0) + ($this->statusBreakdown['approved'] ?? 0)],
            'pending' => ['label' => 'Pending Review', 'color' => 'bg-blue-500', 'badge' => 'blue', 'count' =>
            $this->statusBreakdown['pending'] ?? 0],
            'resubmit' => ['label' => 'For Resubmission', 'color' => 'bg-amber-500', 'badge' => 'yellow', 'count' =>
            $this->statusBreakdown['resubmit'] ?? 0],
            'rejected' => ['label' => 'Disapproved / Rejected', 'color' => 'bg-red-500', 'badge' => 'red', 'count' =>
            $this->statusBreakdown['rejected'] ?? 0],
            ];
            @endphp

            @foreach($statusList as $item)
            @php
            $pct = $this->kpi['total_apps'] > 0 ? round(($item['count'] / $this->kpi['total_apps']) * 100) : 0;
            @endphp
            <div class="space-y-1">
                <div class="flex justify-between text-xs font-medium">
                    <span class="text-zinc-700 dark:text-zinc-300">{{ $item['label'] }}</span>
                    <span class="text-zinc-500">{{ $item['count'] }} ({{ $pct }}%)</span>
                </div>
                <div class="h-2 w-full bg-zinc-100 dark:bg-zinc-800 rounded-full overflow-hidden">
                    <div class="h-full {{ $item['color'] }}" style="width: {{ $pct }}%"></div>
                </div>
            </div>
            @endforeach
        </div>
    </flux:card>

    <!-- Land Use / Category Distribution -->
    <flux:card class="p-5 space-y-4">
        <div>
            <flux:heading size="base" class="font-bold">Zoning Category / Project Types</flux:heading>
            <flux:subheading class="text-xs">Top project classifications processed.</flux:subheading>
        </div>

        <div class="overflow-hidden border border-zinc-200 dark:border-zinc-700 rounded-lg">
            <table class="w-full text-xs text-left">
                <thead
                    class="bg-zinc-50 dark:bg-zinc-800 text-zinc-500 uppercase font-semibold border-b border-zinc-200 dark:border-zinc-700">
                    <tr>
                        <th class="p-2.5">Category / Classification</th>
                        <th class="p-2.5 text-right">Volume</th>
                        <th class="p-2.5 text-right">Share</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse($this->categoryBreakdown as $row)
                    @php
                    $share = $this->kpi['total_apps'] > 0 ? round(($row->count / $this->kpi['total_apps']) * 100, 1) :
                    0;
                    @endphp
                    <tr>
                        <td class="p-2.5 font-medium text-zinc-800 dark:text-zinc-200 capitalize">
                            {{ $row->category ?: 'Unspecified' }}
                        </td>
                        <td class="p-2.5 text-right text-zinc-700 dark:text-zinc-300 font-mono font-semibold">
                            {{ $row->count }}
                        </td>
                        <td class="p-2.5 text-right text-zinc-500">
                            {{ $share }}%
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="p-4 text-center text-zinc-400">No applications recorded in this period.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </flux:card>
</div>