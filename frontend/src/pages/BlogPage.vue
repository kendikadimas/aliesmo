<template>
    <div class="min-h-screen bg-white dark:bg-[#161618]">
        <!-- Header -->
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-10 pt-8 pb-4 lg:pt-14 lg:pb-6">
            <p class="text-[10px] font-semibold text-maroon-400 uppercase tracking-widest mb-2">Blog & Inspirasi</p>
            <h1 class="text-2xl lg:text-4xl font-bold text-charcoal dark:text-[#f0eeeb] tracking-tight">Artikel</h1>
            <p class="mt-2 text-sm text-charcoal/50 dark:text-[#8a8a8e]">Tips gaya, inspirasi mix & match, dan cerita di balik koleksi Aliesmo.</p>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-10 py-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="n in 6" :key="n" class="rounded-2xl overflow-hidden border border-maroon-50 dark:border-[#303032]">
                    <SkeletonLoader :loading="true" :radius="0" height="200px" width="100%" />
                    <div class="p-4 space-y-2">
                        <SkeletonLoader :loading="true" :radius="99" height="10px" width="60%" />
                        <SkeletonLoader :loading="true" :radius="8" height="20px" width="90%" />
                        <SkeletonLoader :loading="true" :radius="99" height="10px" width="100%" />
                        <SkeletonLoader :loading="true" :radius="99" height="10px" width="80%" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty -->
        <div v-else-if="!articles.length" class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-10 py-24 text-center">
            <p class="text-sm text-charcoal/40 dark:text-[#6a6a6e]">Belum ada artikel yang dipublikasikan.</p>
        </div>

        <!-- Grid Artikel -->
        <div v-else class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-10 pb-16">
            <!-- Featured: artikel pertama -->
            <router-link
                :to="`/blog/${articles[0].slug}`"
                class="group block mb-8 rounded-2xl overflow-hidden border border-maroon-50 dark:border-[#303032] hover:border-maroon-200 dark:hover:border-[#f0eeeb]/20 transition-all hover:shadow-lg"
            >
                <div class="lg:flex">
                    <div class="lg:w-1/2 aspect-video bg-maroon-50 dark:bg-[#1c1c1e] overflow-hidden relative">
                        <img
                            v-if="articles[0].thumbnail"
                            :src="articles[0].thumbnail"
                            :alt="articles[0].title"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                        />
                        <div v-else class="w-full h-full flex items-center justify-center">
                            <svg class="w-12 h-12 text-maroon-200 dark:text-[#303032]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                        </div>
                        <span class="absolute top-3 left-3 bg-maroon text-white text-[10px] font-semibold px-2 py-1 rounded-lg">Terbaru</span>
                    </div>
                    <div class="lg:w-1/2 p-5 lg:p-8 flex flex-col justify-center">
                        <p class="text-[10px] font-semibold text-maroon-400 uppercase tracking-widest mb-2">
                            {{ formatDate(articles[0].published_at) }}
                            <span v-if="articles[0].author"> · {{ articles[0].author }}</span>
                        </p>
                        <h2 class="text-lg lg:text-2xl font-bold text-charcoal dark:text-[#f0eeeb] leading-snug group-hover:text-maroon dark:group-hover:text-[#f0eeeb] transition-colors">
                            {{ articles[0].title }}
                        </h2>
                        <p v-if="articles[0].excerpt" class="mt-3 text-sm text-charcoal/60 dark:text-[#8a8a8e] leading-relaxed line-clamp-3">
                            {{ articles[0].excerpt }}
                        </p>
                        <span class="mt-4 inline-flex items-center gap-1.5 text-xs font-semibold text-maroon dark:text-[#f0eeeb]">
                            Baca Selengkapnya
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                        </span>
                    </div>
                </div>
            </router-link>

            <!-- Grid artikel lainnya -->
            <div v-if="articles.length > 1" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <router-link
                    v-for="article in articles.slice(1)"
                    :key="article.id"
                    :to="`/blog/${article.slug}`"
                    class="group flex flex-col rounded-2xl overflow-hidden border border-maroon-50 dark:border-[#303032] hover:border-maroon-200 dark:hover:border-[#f0eeeb]/20 transition-all hover:shadow-md bg-white dark:bg-[#1c1c1e]"
                >
                    <div class="aspect-video bg-maroon-50 dark:bg-[#28282a] overflow-hidden relative">
                        <img
                            v-if="article.thumbnail"
                            :src="article.thumbnail"
                            :alt="article.title"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                        />
                        <div v-else class="w-full h-full flex items-center justify-center">
                            <svg class="w-8 h-8 text-maroon-200 dark:text-[#303032]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                        </div>
                    </div>
                    <div class="p-4 flex flex-col flex-1">
                        <p class="text-[10px] font-semibold text-maroon-400 uppercase tracking-widest mb-1.5">
                            {{ formatDate(article.published_at) }}
                            <span v-if="article.author"> · {{ article.author }}</span>
                        </p>
                        <h3 class="text-sm font-bold text-charcoal dark:text-[#f0eeeb] leading-snug line-clamp-2 group-hover:text-maroon dark:group-hover:text-[#f0eeeb] transition-colors">
                            {{ article.title }}
                        </h3>
                        <p v-if="article.excerpt" class="mt-2 text-xs text-charcoal/60 dark:text-[#8a8a8e] leading-relaxed line-clamp-2 flex-1">
                            {{ article.excerpt }}
                        </p>
                        <span class="mt-3 inline-flex items-center gap-1 text-[11px] font-semibold text-maroon dark:text-[#f0eeeb]">
                            Baca
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                        </span>
                    </div>
                </router-link>
            </div>

            <!-- Load More -->
            <div v-if="hasMorePages" class="mt-10 text-center">
                <button
                    @click="loadMore"
                    :disabled="loadingMore"
                    class="px-8 py-3 border-2 border-maroon-100 dark:border-[#303032] text-sm font-semibold text-charcoal dark:text-[#f0eeeb] rounded-xl hover:border-charcoal dark:hover:border-[#f0eeeb] transition-all disabled:opacity-50"
                >
                    {{ loadingMore ? 'Memuat...' : 'Muat Lebih Banyak' }}
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../api'
import SkeletonLoader from '../components/SkeletonLoader.vue'

const articles     = ref([])
const loading      = ref(true)
const loadingMore  = ref(false)
const hasMorePages = ref(false)
const currentPage  = ref(1)

function formatDate(dateString) {
    if (!dateString) return ''
    return new Intl.DateTimeFormat('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }).format(new Date(dateString))
}

async function fetchArticles(page = 1) {
    try {
        const res = await api.get('/articles', { params: { page, per_page: 12 } })
        const data = res.data.data || res.data
        const meta = res.data.meta || res.data

        if (page === 1) {
            articles.value = Array.isArray(data) ? data : []
        } else {
            articles.value = [...articles.value, ...(Array.isArray(data) ? data : [])]
        }

        // Cek apakah masih ada halaman berikutnya
        hasMorePages.value = meta?.current_page < meta?.last_page
        currentPage.value  = meta?.current_page ?? page
    } catch {
        articles.value = []
    }
}

async function loadMore() {
    loadingMore.value = true
    await fetchArticles(currentPage.value + 1)
    loadingMore.value = false
}

onMounted(async () => {
    await fetchArticles(1)
    loading.value = false
})
</script>
