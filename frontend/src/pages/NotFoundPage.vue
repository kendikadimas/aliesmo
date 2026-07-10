<template>
    <div class="flex flex-col items-center justify-center min-h-[60vh] px-4 text-center">
        <div class="text-6xl sm:text-8xl font-black text-maroon/20 tracking-tighter select-none">404</div>
        <h1 class="mt-4 text-2xl font-bold text-charcoal dark:text-slate-100">Halaman Tidak Ditemukan</h1>
        <p class="mt-3 text-sm text-charcoal/50 dark:text-slate-400 max-w-sm leading-relaxed">
            Halaman yang kamu cari tidak ada atau sudah dipindahkan.
        </p>
        <div class="mt-8 flex flex-wrap gap-3 justify-center">
            <router-link to="/"
                class="px-6 py-2.5 bg-maroon text-white text-sm font-semibold rounded-xl hover:bg-maroon-600 transition-all active:scale-[0.97] shadow-lg shadow-maroon/25">
                Ke Beranda
            </router-link>
            <router-link to="/products"
                class="px-6 py-2.5 border-2 border-maroon-100 dark:border-slate-600 text-charcoal dark:text-slate-200 text-sm font-semibold rounded-xl hover:border-maroon transition-all">
                Lihat Produk
            </router-link>
        </div>
    </div>
</template>
