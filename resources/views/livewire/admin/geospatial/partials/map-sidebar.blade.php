<div
    class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-4 sm:p-5 shadow-xs flex flex-col justify-between space-y-4">
    <div class="space-y-3">
        <!-- Sidebar Header -->
        <div class="flex items-center justify-between border-b border-zinc-200/80 dark:border-zinc-800 pb-3">
            <div>
                <flux:heading size="lg" class="text-base sm:text-lg">Zone Places</flux:heading>
                <flux:subheading class="text-xs">Mapped landmarks & plots</flux:subheading>
            </div>
            <flux:badge size="sm" color="zinc">{{ count($locations) }}</flux:badge>
        </div>

        <!-- Search Input -->
        <flux:input x-model="searchQuery" placeholder="Search landmarks or zones..." icon="magnifying-glass" size="sm"
            clearable />

        <!-- Places Card List -->
        <div class="space-y-2.5 max-h-[460px] overflow-y-auto pr-1">
            <template x-for="loc in filteredLocations" :key="loc.id">
                <div class="p-3 bg-zinc-50/80 dark:bg-zinc-800/40 border border-zinc-200/80 dark:border-zinc-700/60 rounded-xl cursor-pointer transition-all space-y-2 hover:bg-zinc-100/60 dark:hover:bg-zinc-800/80"
                    @click="zoomTo(loc.latitude, loc.longitude)">
                    <div class="flex items-start justify-between gap-1.5">
                        <span class="text-xs font-semibold text-zinc-900 dark:text-zinc-100 line-clamp-1"
                            x-text="loc.title"></span>
                        <span
                            class="text-[10px] font-semibold px-1.5 py-0.5 rounded-md uppercase tracking-wider shrink-0"
                            :class="getBadgeClasses(loc.zone_type)" x-text="loc.zone_type"></span>
                    </div>

                    <div class="text-[11px] text-zinc-500 dark:text-zinc-400 space-y-0.5">
                        <div class="truncate">
                            <span class="text-zinc-400">Ref:</span>
                            <span class="font-mono font-medium" x-text="loc.application_id || 'Direct Plot'"></span>
                        </div>
                        <div class="font-mono text-[10px] text-zinc-400">
                            <span x-text="Number(loc.latitude).toFixed(4)"></span>, <span
                                x-text="Number(loc.longitude).toFixed(4)"></span>
                        </div>
                    </div>

                    <!-- Edit Location Link (Editable for any linked clearance) -->
                    <template x-if="loc.clearance_id">
                        <div class="pt-2 border-t border-zinc-200/60 dark:border-zinc-700/50 flex justify-end">
                            <a :href="'{{ url('admin/geospatial/create') }}?clearance_id=' + loc.clearance_id"
                                wire:navigate
                                class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 py-1 px-2.5 rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-950/50 transition border border-indigo-200/50 dark:border-indigo-800/50"
                                @click.stop>
                                <flux:icon name="pencil-square" class="size-3.5" />
                                <span>Edit Location</span>
                            </a>
                        </div>
                    </template>
                </div>
            </template>

            <template x-if="filteredLocations.length === 0">
                <div
                    class="p-6 text-center text-xs text-zinc-400 border border-dashed border-zinc-200 dark:border-zinc-800 rounded-lg">
                    No matching zone locations found.
                </div>
            </template>
        </div>
    </div>

    <!-- Quick Reset Button -->
    <div class="pt-3 border-t border-zinc-100 dark:border-zinc-800">
        <flux:button class="w-full justify-center" variant="subtle" size="sm" icon="arrows-pointing-out"
            @click="resetView()">
            Recenter Malalag Map
        </flux:button>
    </div>
</div>