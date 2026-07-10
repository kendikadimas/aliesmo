<template>
    <div
        v-if="loading"
        class="skeleton-loader"
        :style="{
            width: width,
            height: height,
            borderRadius: computedRadius,
        }"
    />
    <slot v-else />
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
    loading: {
        type: Boolean,
        default: true,
    },
    height: {
        type: String,
        default: '16px',
    },
    width: {
        type: String,
        default: '100%',
    },
    radius: {
        type: Number,
        default: 8,
    },
})

const computedRadius = computed(() => `${props.radius}px`)
</script>

<style scoped>
.skeleton-loader {
    display: block;
    background: linear-gradient(
        90deg,
        rgba(0, 0, 0, 0.06) 25%,
        rgba(0, 0, 0, 0.12) 50%,
        rgba(0, 0, 0, 0.06) 75%
    );
    background-size: 200% 100%;
    animation: skeleton-shimmer 1.5s infinite;
}

:global(.dark) .skeleton-loader {
    background: linear-gradient(
        90deg,
        rgba(255, 255, 255, 0.05) 25%,
        rgba(255, 255, 255, 0.09) 50%,
        rgba(255, 255, 255, 0.05) 75%
    );
    background-size: 200% 100%;
}

@keyframes skeleton-shimmer {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}
</style>
