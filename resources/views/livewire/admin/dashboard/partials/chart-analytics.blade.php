<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 sm:gap-6">
    <!-- Left 2 Cols: Monthly Application & Approval Trends Chart -->
    <flux:card class="lg:col-span-2 p-5 space-y-4">
        <div class="flex items-center justify-between border-b border-zinc-100 dark:border-zinc-800 pb-3">
            <div>
                <flux:heading size="base" class="font-bold">Clearance Application & Approval Trends</flux:heading>
                <flux:subheading class="text-xs">Month-over-month filings compared against approved certificates.
                </flux:subheading>
            </div>
            <div class="flex items-center gap-3 text-xs font-medium">
                <span class="flex items-center gap-1.5 text-blue-600 dark:text-blue-400">
                    <span class="size-2 rounded-full bg-blue-600 inline-block"></span> Total Filings
                </span>
                <span class="flex items-center gap-1.5 text-emerald-600 dark:text-emerald-400">
                    <span class="size-2 rounded-full bg-emerald-600 inline-block"></span> Approved
                </span>
            </div>
        </div>

        <!-- Chart.js Container via Alpine -->
        <div x-data="{
                chart: null,
                init() {
                    if (typeof Chart === 'undefined') {
                        let script = document.createElement('script');
                        script.src = 'https://cdn.jsdelivr.net/npm/chart.js';
                        script.onload = () => this.renderChart();
                        document.head.appendChild(script);
                    } else {
                        this.renderChart();
                    }
                },
                renderChart() {
                    const ctx = this.$refs.canvas.getContext('2d');
                    if (this.chart) this.chart.destroy();

                    this.chart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: @js($this->chartData['labels']),
                            datasets: [
                                {
                                    label: 'Filings',
                                    data: @js($this->chartData['applications']),
                                    backgroundColor: '#3b82f6',
                                    borderRadius: 6,
                                    barPercentage: 0.6,
                                },
                                {
                                    label: 'Approved',
                                    data: @js($this->chartData['approved']),
                                    backgroundColor: '#10b981',
                                    borderRadius: 6,
                                    barPercentage: 0.6,
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false }
                            },
                            scales: {
                                x: {
                                    grid: { display: false },
                                    ticks: { font: { size: 11 } }
                                },
                                y: {
                                    beginAtZero: true,
                                    grid: { color: 'rgba(200, 200, 200, 0.15)' },
                                    ticks: { precision: 0, font: { size: 11 } }
                                }
                            }
                        }
                    });
                }
            }" class="h-64 sm:h-72 w-full relative">
            <canvas x-ref="canvas"></canvas>
        </div>
    </flux:card>

    <!-- Right 1 Col: Land-Use Classification Breakdown -->
    <flux:card class="p-5 flex flex-col justify-between space-y-4">
        <div>
            <div class="border-b border-zinc-100 dark:border-zinc-800 pb-3 mb-3">
                <flux:heading size="base" class="font-bold">Zoning Classification</flux:heading>
                <flux:subheading class="text-xs">Dominant land-use categories processed</flux:subheading>
            </div>

            <div class="space-y-3.5">
                @php
                $colors = ['bg-blue-600', 'bg-emerald-600', 'bg-purple-600', 'bg-amber-600'];
                @endphp
                @forelse($this->zoningShare as $index => $item)
                @php
                $pct = $this->stats['total_applications'] > 0
                ? round(($item->count / $this->stats['total_applications']) * 100)
                : 0;
                $color = $colors[$index % count($colors)];
                @endphp
                <div class="space-y-1">
                    <div class="flex justify-between items-center text-xs">
                        <span class="font-medium text-zinc-800 dark:text-zinc-200 capitalize">
                            {{ $item->category }}
                        </span>
                        <span class="text-zinc-500 font-mono">{{ $item->count }} ({{ $pct }}%)</span>
                    </div>
                    <div class="h-2 w-full bg-zinc-100 dark:bg-zinc-800 rounded-full overflow-hidden">
                        <div class="h-full {{ $color }} rounded-full" style="width: {{ $pct }}%"></div>
                    </div>
                </div>
                @empty
                <p class="text-xs text-zinc-400 text-center py-4">No zoning records yet.</p>
                @endforelse
            </div>
        </div>

        <div class="pt-3 border-t border-zinc-100 dark:border-zinc-800 text-[11px] text-zinc-400">
            Based on active Comprehensive Land Use Plan (CLUP) compliance records.
        </div>
    </flux:card>
</div>