<x-filament-panels::page>
    @php
        $productCount = App\Models\Product::count();
        $maxProducts = 30;
        $stats = $this->getSummaryStats();
    @endphp

    <div style="display:flex; flex-direction:column; gap:1rem;">

        {{-- Alert: batas produk --}}
        @if($productCount >= $maxProducts)
            <div style="border-radius:0.75rem; border:1px solid rgba(239,68,68,0.3); background:rgba(239,68,68,0.1); padding:1rem; display:flex; align-items:center; justify-content:space-between; gap:1rem; flex-wrap:wrap;">
                <div>
                    <p style="font-size:0.875rem; font-weight:600; color:#f87171; margin:0 0 0.25rem 0;">Batas Produk Tercapai ({{ $productCount }}/{{ $maxProducts }})</p>
                    <p style="font-size:0.875rem; color:#9ca3af; margin:0;">Upgrade sekarang untuk menambah kapasitas produk.</p>
                </div>
                <a href="https://wa.me/6285196811722?text=Halo%20Kalana%20Labs,%20saya%20ingin%20upgrade%20produk"
                   target="_blank"
                   style="display:inline-flex;align-items:center;gap:0.5rem;padding:0.5rem 1.25rem;background:#16a34a;color:white;font-size:0.875rem;font-weight:600;border-radius:0.5rem;text-decoration:none;white-space:nowrap;">
                    Upgrade via WA
                </a>
            </div>
        @elseif($productCount >= 25)
            <div style="border-radius:0.75rem; border:1px solid rgba(234,179,8,0.3); background:rgba(234,179,8,0.1); padding:1rem;">
                <p style="font-size:0.875rem; font-weight:600; color:#facc15; margin:0 0 0.25rem 0;">Slot Produk Hampir Habis ({{ $productCount }}/{{ $maxProducts }})</p>
                <p style="font-size:0.875rem; color:#9ca3af; margin:0;">
                    <a href="https://wa.me/6285196811722?text=Halo%20Kalana%20Labs,%20saya%20ingin%20upgrade%20produk"
                       target="_blank" style="font-weight:700; text-decoration:underline; color:#fde047;">Hubungi kami</a> untuk upgrade.
                </p>
            </div>
        @endif

        {{-- Filter Tanggal --}}
        <div style="border-radius:0.75rem; border:1px solid rgba(255,255,255,0.08); background:rgba(255,255,255,0.04); padding:1.25rem;">
            <p style="font-size:0.75rem; font-weight:600; color:#9ca3af; text-transform:uppercase; letter-spacing:0.05em; margin:0 0 0.75rem 0;">Filter Laporan</p>
            <div style="display:flex; gap:1rem; flex-wrap:wrap;">
                <div>
                    <label style="display:block; font-size:0.75rem; font-weight:500; color:#9ca3af; margin-bottom:0.375rem;">Tanggal Mulai</label>
                    <input type="date" wire:model.live="startDate"
                        style="border:1px solid rgba(255,255,255,0.1); border-radius:0.5rem; padding:0.5rem 0.75rem; font-size:0.875rem; background:#1f2937; color:#f3f4f6; outline:none;">
                </div>
                <div>
                    <label style="display:block; font-size:0.75rem; font-weight:500; color:#9ca3af; margin-bottom:0.375rem;">Tanggal Akhir</label>
                    <input type="date" wire:model.live="endDate"
                        style="border:1px solid rgba(255,255,255,0.1); border-radius:0.5rem; padding:0.5rem 0.75rem; font-size:0.875rem; background:#1f2937; color:#f3f4f6; outline:none;">
                </div>
            </div>
        </div>

        {{-- Summary Stats --}}
        <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:1rem;">
            <div style="border-radius:0.75rem; border:1px solid rgba(255,255,255,0.08); background:rgba(255,255,255,0.04); padding:1.25rem;">
                <p style="font-size:0.75rem; font-weight:600; color:#9ca3af; text-transform:uppercase; letter-spacing:0.05em; margin:0 0 0.5rem 0;">Total Pendapatan</p>
                <p style="font-size:1.5rem; font-weight:700; color:#f9fafb; margin:0;">
                    Rp {{ number_format($stats['revenue'], 0, ',', '.') }}
                </p>
            </div>
            <div style="border-radius:0.75rem; border:1px solid rgba(255,255,255,0.08); background:rgba(255,255,255,0.04); padding:1.25rem;">
                <p style="font-size:0.75rem; font-weight:600; color:#9ca3af; text-transform:uppercase; letter-spacing:0.05em; margin:0 0 0.5rem 0;">Total Pesanan</p>
                <p style="font-size:1.5rem; font-weight:700; color:#f9fafb; margin:0;">
                    {{ $stats['orders'] }}
                </p>
            </div>
            <div style="border-radius:0.75rem; border:1px solid rgba(255,255,255,0.08); background:rgba(255,255,255,0.04); padding:1.25rem;">
                <p style="font-size:0.75rem; font-weight:600; color:#9ca3af; text-transform:uppercase; letter-spacing:0.05em; margin:0 0 0.5rem 0;">Item Terjual</p>
                <p style="font-size:1.5rem; font-weight:700; color:#f9fafb; margin:0;">
                    {{ $stats['items_sold'] }}
                </p>
            </div>
        </div>

        {{-- Table --}}
        <div>
            {{ $this->table }}
        </div>

    </div>
</x-filament-panels::page>
