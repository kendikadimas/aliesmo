<x-filament-panels::page>
    @php
        $productCount = App\Models\Product::count();
        $maxProducts = 30;
        $stats = $this->getSummaryStats();
    @endphp

    <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6 space-y-6">

        {{-- Alert: batas produk --}}
        @if($productCount >= $maxProducts)
            <x-filament::section>
                <x-slot name="heading">
                    <span style="color: #92400e;">Batas Produk Tercapai ({{ $productCount }}/{{ $maxProducts }})</span>
                </x-slot>
                <div style="display:flex; align-items:center; justify-content:space-between; gap:1rem;">
                    <p style="font-size:0.875rem; color:#78350f;">Upgrade sekarang untuk menambah kapasitas produk.</p>
                    <a href="https://wa.me/6285196811722?text=Halo%20Kalana%20Labs,%20saya%20ingin%20upgrade%20produk"
                       target="_blank"
                       style="display:inline-flex;align-items:center;gap:0.5rem;padding:0.5rem 1.25rem;background:#16a34a;color:white;font-size:0.875rem;font-weight:600;border-radius:0.5rem;text-decoration:none;">
                        Upgrade via WA
                    </a>
                </div>
            </x-filament::section>
        @elseif($productCount >= 25)
            <x-filament::section>
                <x-slot name="heading">
                    <span style="color: #1e40af;">Slot Produk Hampir Habis ({{ $productCount }}/{{ $maxProducts }})</span>
                </x-slot>
                <p style="font-size:0.875rem; color:#1d4ed8;">
                    <a href="https://wa.me/6285196811722?text=Halo%20Kalana%20Labs,%20saya%20ingin%20upgrade%20produk"
                       target="_blank" style="font-weight:700;text-decoration:underline;">Hubungi kami</a> untuk upgrade.
                </p>
            </x-filament::section>
        @endif

        {{-- Filter Tanggal --}}
        <x-filament::section>
            <x-slot name="heading">Filter Laporan</x-slot>
            <div style="display:flex; gap:1rem; align-items:flex-end; flex-wrap:wrap;">
                <div>
                    <label style="display:block; font-size:0.75rem; font-weight:600; color:#6b7280; margin-bottom:0.375rem;">Start Date</label>
                    <input type="date" wire:model.live="startDate"
                        style="border:1px solid #d1d5db; border-radius:0.5rem; padding:0.5rem 0.75rem; font-size:0.875rem; background:white; color:#111827;">
                </div>
                <div>
                    <label style="display:block; font-size:0.75rem; font-weight:600; color:#6b7280; margin-bottom:0.375rem;">End Date</label>
                    <input type="date" wire:model.live="endDate"
                        style="border:1px solid #d1d5db; border-radius:0.5rem; padding:0.5rem 0.75rem; font-size:0.875rem; background:white; color:#111827;">
                </div>
            </div>
        </x-filament::section>

        {{-- Summary Stats --}}
        <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:1rem;">
            <x-filament::section>
                <x-slot name="heading">Total Revenue</x-slot>
                <p style="font-size:1.5rem; font-weight:700; color:#111827;">
                    Rp {{ number_format($stats['revenue'], 0, ',', '.') }}
                </p>
            </x-filament::section>

            <x-filament::section>
                <x-slot name="heading">Total Orders</x-slot>
                <p style="font-size:1.5rem; font-weight:700; color:#111827;">
                    {{ $stats['orders'] }}
                </p>
            </x-filament::section>

            <x-filament::section>
                <x-slot name="heading">Items Sold</x-slot>
                <p style="font-size:1.5rem; font-weight:700; color:#111827;">
                    {{ $stats['items_sold'] }}
                </p>
            </x-filament::section>
        </div>

        {{-- Table --}}
        {{ $this->table }}

    </div>
</x-filament-panels::page>
