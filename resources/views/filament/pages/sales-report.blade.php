<x-filament-panels::page>
    <div class="space-y-6">
        <form wire:submit="updateFilters" class="flex gap-4 items-end">
            <div>
                <label class="block text-sm font-medium">Start Date</label>
                <input type="date" wire:model.live="startDate" class="border rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium">End Date</label>
                <input type="date" wire:model.live="endDate" class="border rounded px-3 py-2">
            </div>
        </form>

        <div class="grid grid-cols-3 gap-4">
            <div class="bg-white rounded-lg shadow p-4">
                <h3 class="text-sm text-gray-500">Total Revenue</h3>
                <p class="text-2xl font-bold">Rp {{ number_format($this->getSummaryStats()['revenue'], 0, ',', '.') }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <h3 class="text-sm text-gray-500">Total Orders</h3>
                <p class="text-2xl font-bold">{{ $this->getSummaryStats()['orders'] }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <h3 class="text-sm text-gray-500">Items Sold</h3>
                <p class="text-2xl font-bold">{{ $this->getSummaryStats()['items_sold'] }}</p>
            </div>
        </div>

        {{ $this->table }}
    </div>
</x-filament-panels::page>
