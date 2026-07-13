<template>
    <div class="min-h-screen bg-white dark:bg-[#161618]">
        <!-- Loading -->
        <div v-if="loading" class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8 lg:py-14">
            <SkeletonLoader :loading="true" :radius="99" height="12px" width="80px" class="mb-4" />
            <SkeletonLoader :loading="true" :radius="8" height="36px" width="85%" class="mb-3" />
            <SkeletonLoader :loading="true" :radius="99" height="10px" width="120px" class="mb-6" />
            <SkeletonLoader :loading="true" :radius="12" height="320px" width="100%" class="mb-8" />
            <div class="space-y-2">
                <SkeletonLoader v-for="n in 8" :key="n" :loading="true" :radius="99" height="10px" :width="n % 3 === 0 ? '80%' : '100%'" />
            </div>
        </div>

        <!-- Not Found -->
        <div v-else-if="notFound" class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-24 text-center">
            <p class="text-sm text-charcoal/50 dark:text-[#8a8a8e]">Artikel tidak ditemukan :(</p>
            <router-link to="/blog" class="inline-block mt-3 text-sm font-semibold text-maroon dark:text-[#f0eeeb] hover:underline">
                Kembali ke Blog
            </router-link>
        </div>

        <!-- Konten Artikel -->
        <article v-else-if="article" class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8 lg:py-14">
            <!-- Breadcrumb -->
            <router-link to="/blog" class="inline-flex items-center gap-1.5 text-xs font-semibold text-charcoal/40 dark:text-[#6a6a6e] hover:text-maroon dark:hover:text-[#f0eeeb] transition-colors mb-6">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
                Kembali ke Blog
            </router-link>

            <!-- Meta -->
            <p class="text-[10px] font-semibold text-maroon-400 uppercase tracking-widest mb-3">
                {{ formatDate(article.published_at) }}
                <span v-if="article.author"> &middot; {{ article.author }}</span>
            </p>

            <!-- Judul -->
            <h1 class="text-2xl lg:text-4xl font-bold text-charcoal dark:text-[#f0eeeb] leading-tight tracking-tight">
                {{ article.title }}
            </h1>

            <!-- Excerpt -->
            <p v-if="article.excerpt" class="mt-4 text-base text-charcoal/60 dark:text-[#8a8a8e] leading-relaxed border-l-4 border-maroon pl-4">
                {{ article.excerpt }}
            </p>

            <!-- Thumbnail -->
            <div v-if="article.thumbnail" class="mt-6 rounded-2xl overflow-hidden aspect-video bg-maroon-50 dark:bg-[#1c1c1e]">
                <img :src="article.thumbnail" :alt="article.title" class="w-full h-full object-cover" />
            </div>

            <!-- Konten HTML -->
            <div
                class="mt-8 prose prose-sm lg:prose-base max-w-none
                    prose-headings:font-bold prose-headings:text-charcoal dark:prose-headings:text-[#f0eeeb]
                    prose-p:text-charcoal/75 dark:prose-p:text-[#d0ceca] prose-p:leading-relaxed
                    prose-a:text-maroon dark:prose-a:text-[#f0eeeb] prose-a:underline
                    prose-strong:text-charcoal dark:prose-strong:text-[#f0eeeb]
                    prose-li:text-charcoal/75 dark:prose-li:text-[#d0ceca]
                    prose-blockquote:border-l-maroon prose-blockquote:text-charcoal/60 dark:prose-blockquote:text-[#8a8a8e]
                    prose-img:rounded-xl prose-img:w-full"
                v-html="article.content"
            ></div>

            <!-- Divider -->
            <div class="mt-12 pt-8 border-t border-ink-10 dark:border-[#303032]">
                <p class="text-xs text-charcoal/40 dark:text-[#6a6a6e] mb-4">Artikel lainnya</p>
                <router-link to="/blog" class="inline-flex items-center gap-2 px-5 py-2.5 border-2 border-maroon-100 dark:border-[#303032] text-sm font-semibold text-charcoal dark:text-[#f0eeeb] rounded-xl hover:border-charcoal dark:hover:border-[#f0eeeb] transition-all">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
                    Lihat Semua Artikel
                </router-link>
            </div>
        </article>
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import api from '../api'
import SkeletonLoader from '../components/SkeletonLoader.vue'

const route   = useRoute()
const article = ref(null)
const loading = ref(true)
const notFound = ref(false)

function formatDate(dateString) {
    if (!dateString) return ''
    return new Intl.DateTimeFormat('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }).format(new Date(dateString))
}

async function fetchArticle(slug) {
    loading.value  = true
    notFound.value = false
    article.value  = null
    try {
        const res = await api.get(`/articles/${slug}`)
        article.value = res.data.data || res.data
    } catch (e) {
        if (e.response?.status === 404) notFound.value = true
        article.value = null
    } finally {
        loading.value = false
    }
}

onMounted(() => fetchArticle(route.params.slug))
watch(() => route.params.slug, (slug) => { if (slug) fetchArticle(slug) })
</script>
