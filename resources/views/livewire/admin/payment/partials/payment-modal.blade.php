<flux:modal name="payment-modal" class="w-full max-w-sm" backdrop="blur">
    <div class="space-y-4">
        <div>
            <flux:heading size="lg">Record Treasury Payment</flux:heading>
            <flux:subheading class="text-xs sm:text-sm">Enter the Official Receipt issued by the Municipal Treasurer's
                Office.</flux:subheading>
        </div>

        <form wire:submit="savePayment" class="space-y-3">
            <flux:input wire:model="or_no" label="Official Receipt (OR) Number *" placeholder="e.g., OR-8923412" />
            <flux:input type="date" wire:model="payment_date" label="Date Paid at Treasury *" />

            <div class="flex justify-end gap-2 pt-2 border-t border-zinc-100 dark:border-zinc-800">
                <flux:modal.close>
                    <flux:button variant="subtle" size="sm">Cancel</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary" size="sm">
                    Save Record
                </flux:button>
            </div>
        </form>
    </div>
</flux:modal>