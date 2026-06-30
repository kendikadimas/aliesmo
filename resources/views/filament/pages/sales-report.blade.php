<x-filament-panels::page>
    <div class="space-y-6">
        @php
            $productCount = App\Models\Product::count();
            $maxProducts = 30;
        @endphp

        @if($productCount >= $maxProducts)
            <div class="bg-amber-50 border-2 border-amber-200 rounded-xl p-4 flex items-center justify-between">
                <div>
                    <p class="text-sm font-bold text-amber-800">Anda telah mencapai batas maksimal {{ $maxProducts }} produk.</p>
                    <p class="text-xs text-amber-700 mt-1">Upgrade sekarang untuk menambah kapasitas produk.</p>
                </div>
                <a href="https://wa.me/6285196811722?text=Halo%20Kalana%20Labs,%20saya%20ingin%20upgrade%20produk" target="_blank" class="px-5 py-2 bg-green-600 text-white text-sm font-semibold rounded-lg hover:bg-green-700 transition-colors inline-flex items-center gap-2">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" stroke="none"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    Upgrade via WA
                </a>
            </div>
        @elseif($productCount >= 25)
            <div class="bg-blue-50 border-2 border-blue-200 rounded-xl p-4">
                <p class="text-sm font-semibold text-blue-800">Slot produk hampir habis ({{ $productCount }}/{{ $maxProducts }}). <a href="https://wa.me/6285196811722?text=Halo%20Kalana%20Labs,%20saya%20ingin%20upgrade%20produk" target="_blank" class="underline font-bold">Hubungi kami</a> untuk upgrade.</p>
            </div>
        @endif

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
