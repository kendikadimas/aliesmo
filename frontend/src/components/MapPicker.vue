<template>
    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <label class="block text-sm font-semibold text-charcoal/70">
                <MapPinIcon class="w-4 h-4 inline-block mr-1 -mt-0.5" />
                Pin Lokasi Pengiriman
            </label>
            <button 
                type="button"
                @click="getCurrentLocation"
                :disabled="locating"
                class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors"
                :class="locating
                    ? 'bg-maroon-50 text-maroon/50 dark:bg-[#28282a] dark:text-maroon/40 cursor-not-allowed'
                    : 'bg-maroon-50 text-maroon hover:bg-maroon-100 dark:bg-[#28282a] dark:text-maroon dark:hover:bg-[#303032]'">
                <ArrowPathIcon class="w-3.5 h-3.5" :class="locating ? 'animate-spin' : ''" />
                {{ locating ? 'Mencari...' : 'Gunakan Lokasi Saya' }}
            </button>
        </div>
        
        <!-- Banner geser pin — tampil saat koordinat sudah ada -->
        <div v-if="modelValue && !geocoding" class="flex items-center gap-2 px-3 py-2 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800/50 rounded-xl">
            <svg class="w-4 h-4 text-amber-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
            </svg>
            <p class="text-xs text-amber-700 dark:text-amber-400 leading-relaxed">
                <span class="font-semibold">Geser pin</span> ke lokasi rumah yang tepat agar kurir bisa menemukan alamat Anda dengan mudah.
            </p>
        </div>

        <div 
            ref="mapContainer" 
            class="w-full h-64 rounded-xl border-2 overflow-hidden transition-colors"
            :class="modelValue ? 'border-maroon-200' : 'border-gray-200'">
        </div>
        
        <div class="flex items-start gap-2 px-1">
            <InformationCircleIcon class="w-4 h-4 text-charcoal/40 shrink-0 mt-0.5" />
            <p class="text-xs text-charcoal/50 leading-relaxed">
                <span v-if="geocoding">Mencari lokasi...</span>
                <span v-else-if="!modelValue">Pilih alamat dari dropdown untuk menampilkan peta.</span>
                <span v-else>
                    Koordinat: <span class="font-medium text-charcoal/60">{{ formatCoord(modelValue.lat) }}, {{ formatCoord(modelValue.lng) }}</span>
                </span>
            </p>
        </div>
    </div>
</template>

<script setup>
import { ref, watch, onMounted, onUnmounted, nextTick } from 'vue'
import { MapPinIcon, ArrowPathIcon, InformationCircleIcon } from '@heroicons/vue/24/outline'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'

const props = defineProps({
    modelValue: {
        type: Object,
        default: null
    },
    initialLat: {
        type: [Number, String],
        default: null
    },
    initialLng: {
        type: [Number, String],
        default: null
    }
})

const emit = defineEmits(['update:modelValue'])

const mapContainer = ref(null)
const geocoding = ref(false)
const locating = ref(false)
let map = null
let marker = null

// Default center: Indonesia (tengah Jawa)
const DEFAULT_CENTER = [-7.5, 110.0]
const DEFAULT_ZOOM = 13

function formatCoord(val) {
    if (!val) return '-'
    return Number(val).toFixed(6)
}

function initMap() {
    if (!mapContainer.value) return

    // Create map
    map = L.map(mapContainer.value, {
        center: props.modelValue 
            ? [props.modelValue.lat, props.modelValue.lng]
            : (props.initialLat && props.initialLng 
                ? [Number(props.initialLat), Number(props.initialLng)] 
                : DEFAULT_CENTER),
        zoom: props.modelValue || (props.initialLat && props.initialLng) ? 15 : DEFAULT_ZOOM,
        zoomControl: true,
        attributionControl: false
    })

    // Add OpenStreetMap tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
    }).addTo(map)

    // Paksa recalculate setelah tiles ditambahkan
    setTimeout(() => { if (map) map.invalidateSize({ animate: false }) }, 0)

    // Custom icon
    const icon = L.divIcon({
        className: 'custom-marker',
        html: `<div class="flex items-center justify-center w-8 h-8">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#8B1A1A" class="w-8 h-8 drop-shadow-lg">
                <path fill-rule="evenodd" d="M11.54 22.351l.07.04.028.016a.76.76 0 00.723 0l.028-.015.071-.041a16.975 16.975 0 001.144-.742 19.58 19.58 0 002.683-2.282c1.944-1.99 3.963-4.98 3.963-8.827a8.25 8.25 0 00-16.5 0c0 3.846 2.02 6.837 3.963 8.827a19.58 19.58 0 002.682 2.282 16.975 16.975 0 001.145.742zM12 13.5a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />
            </svg>
        </div>`,
        iconSize: [32, 32],
        iconAnchor: [16, 32],
    })

    // Add marker
    const lat = props.modelValue?.lat || (props.initialLat ? Number(props.initialLat) : DEFAULT_CENTER[0])
    const lng = props.modelValue?.lng || (props.initialLng ? Number(props.initialLng) : DEFAULT_CENTER[1])
    
    marker = L.marker([lat, lng], { 
        icon, 
        draggable: true,
        autoPan: true 
    }).addTo(map)

    // Handle marker drag
    marker.on('dragend', () => {
        const pos = marker.getLatLng()
        emit('update:modelValue', { lat: pos.lat, lng: pos.lng })
    })

    // Handle map click
    map.on('click', (e) => {
        marker.setLatLng(e.latlng)
        emit('update:modelValue', { lat: e.latlng.lat, lng: e.latlng.lng })
    })

    // Fix blank map on first render (lazy-loaded component)
    // invalidateSize() recalculates map dimensions after container is visible
    setTimeout(() => {
        if (map) {
            map.invalidateSize()
            // Re-center if we have coordinates
            if (props.modelValue) {
                map.setView([props.modelValue.lat, props.modelValue.lng], 15)
            } else if (props.initialLat && props.initialLng) {
                map.setView([Number(props.initialLat), Number(props.initialLng)], 15)
            }
        }
    }, 100)
}

function moveTo(lat, lng) {
    if (!map || !marker) return
    const latlng = L.latLng(lat, lng)
    marker.setLatLng(latlng)
    map.invalidateSize() // Ensure map is properly rendered
    map.setView(latlng, 16, { animate: true, duration: 0.5 })
    emit('update:modelValue', { lat, lng })
}

// Geocode address using Nominatim (free, no API key)
// Best practice for Indonesia: village + postal code > village + city > postal code > broader query
async function geocodeAddress(address, context = {}) {
    try {
        const baseParams = {
            format: 'jsonv2',
            limit: '5',  // Get multiple results to filter by postal code
            countrycodes: 'id',
            addressdetails: '1',
            'accept-language': 'id'
        }

        // Extract village name from address
        // Address format: "KALIJARAN, MAOS, CILACAP, JAWA TENGAH, 53272"
        const village = address ? address.split(',')[0].trim() : null

        // Strategy 1: Village name + postal code (most accurate for duplicate names)
        if (village && village.length > 2 && context.postal_code) {
            const query = `${village}, ${context.postal_code}, Indonesia`
            const params = new URLSearchParams({ ...baseParams, q: query })
            const results = await fetchNominatimMultiple(params)
            if (results.length > 0) {
                // Find result that matches the postal code
                const match = results.find(r => r.postal_code === context.postal_code) || results[0]
                console.log('[MapPicker] Geocoded by village+postal:', query, '→', match.lat, match.lng)
                return match
            }
        }

        // Strategy 2: Village name + city (for disambiguation)
        if (village && village.length > 2 && context.city) {
            const query = `${village}, ${context.city}, Indonesia`
            const params = new URLSearchParams({ ...baseParams, q: query })
            const results = await fetchNominatimMultiple(params)
            if (results.length > 0) {
                // Find result that matches the city
                const match = results.find(r => 
                    r.display_name.toLowerCase().includes(context.city.toLowerCase())
                ) || results[0]
                console.log('[MapPicker] Geocoded by village+city:', query, '→', match.lat, match.lng)
                return match
            }
        }

        // Strategy 3: Postal code only (fallback)
        if (context.postal_code) {
            const postalParams = new URLSearchParams({ 
                ...baseParams, 
                postalcode: context.postal_code,
                limit: '1'
            })
            const postalResult = await fetchNominatim(postalParams)
            if (postalResult) {
                console.log('[MapPicker] Geocoded by postal code:', context.postal_code, '→', postalResult.lat, postalResult.lng)
                return postalResult
            }
        }

        // Strategy 4: Broader query (city + province only)
        if (context.city && context.province) {
            const broaderQuery = `${context.city}, ${context.province}, Indonesia`
            const broaderParams = new URLSearchParams({ ...baseParams, q: broaderQuery, limit: '1' })
            const broaderResult = await fetchNominatim(broaderParams)
            if (broaderResult) {
                console.log('[MapPicker] Geocoded by broader query:', broaderQuery, '→', broaderResult.lat, broaderResult.lng)
                return broaderResult
            }
        }

        console.warn('[MapPicker] No geocoding result for:', address, context)
    } catch (e) {
        console.warn('[MapPicker] Geocoding failed:', e.message)
    }
    return null
}

async function fetchNominatimMultiple(params) {
    const url = `https://nominatim.openstreetmap.org/search?${params.toString()}`
    const res = await fetch(url)
    const data = await res.json()
    
    return data.map(result => ({
        lat: parseFloat(result.lat),
        lng: parseFloat(result.lon),
        display_name: result.display_name,
        postal_code: result.address?.postcode || null
    }))
}

async function fetchNominatim(params) {
    const url = `https://nominatim.openstreetmap.org/search?${params.toString()}`
    const res = await fetch(url)
    const data = await res.json()
    
    if (data.length > 0) {
        const result = data[0]
        return {
            lat: parseFloat(result.lat),
            lng: parseFloat(result.lon),
            display_name: result.display_name
        }
    }
    return null
}

async function moveToAddress(address, context = {}) {
    geocoding.value = true
    try {
        // Wait for map to be ready (handles first-input timing issue)
        if (!map || !marker) {
            console.log('[MapPicker] Map not ready, waiting...')
            await new Promise(resolve => {
                const checkMap = setInterval(() => {
                    if (map && marker) {
                        clearInterval(checkMap)
                        resolve()
                    }
                }, 100)
                // Timeout after 3 seconds
                setTimeout(() => {
                    clearInterval(checkMap)
                    resolve()
                }, 3000)
            })
        }

        const coords = await geocodeAddress(address, context)
        if (coords) {
            moveTo(coords.lat, coords.lng)
        }
    } finally {
        geocoding.value = false
    }
}

function getCurrentLocation() {
    if (!navigator.geolocation || locating.value) return
    
    locating.value = true
    navigator.geolocation.getCurrentPosition(
        (pos) => {
            moveTo(pos.coords.latitude, pos.coords.longitude)
            locating.value = false
        },
        (err) => {
            console.warn('[MapPicker] Geolocation error:', err.message)
            locating.value = false
        },
        { enableHighAccuracy: true, timeout: 10000 }
    )
}

// Initialize map on mount
onMounted(() => {
    nextTick(() => {
        initMap()
        // Paksa Leaflet recalculate ukuran container
        setTimeout(() => { if (map) map.invalidateSize() }, 100)
        setTimeout(() => { if (map) map.invalidateSize() }, 500)

        // ResizeObserver — handle kasus container baru visible setelah v-if
        if (mapContainer.value && typeof ResizeObserver !== 'undefined') {
            const ro = new ResizeObserver(() => {
                if (map) map.invalidateSize()
            })
            ro.observe(mapContainer.value)
            // Cleanup saat unmount
            onUnmounted(() => ro.disconnect())
        }
    })
})

// Cleanup
onUnmounted(() => {
    if (map) {
        map.remove()
        map = null
    }
})

defineExpose({ moveTo, moveToAddress, getCurrentLocation })
</script>

<style>
.custom-marker {
    background: none !important;
    border: none !important;
}

/* Hardware acceleration for map container */
.leaflet-container {
    will-change: transform;
    transform: translateZ(0);
    -webkit-transform: translateZ(0);
}

/* Optimize tile rendering */
.leaflet-tile {
    will-change: opacity;
}

/* Smooth transitions */
.leaflet-fade-anim .leaflet-tile {
    transition: opacity 0.2s ease-in-out;
}
</style>
