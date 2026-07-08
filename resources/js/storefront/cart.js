import { reactive, toRefs, computed } from 'vue'

const state = reactive({
    items: JSON.parse(localStorage.getItem('cart') || '[]'),
    selectedIds: JSON.parse(localStorage.getItem('cart_selected') || '[]'),
})

state.items = state.items.map(item => ({
    ...item,
    cart_key: item.cart_key || `${item.product_id}`,
}))
state.selectedIds = state.selectedIds.map(id => `${id}`)

// Event bus untuk animasi fly-to-cart
const cartAnimationListeners = []
export function onCartAnimation(fn) {
    cartAnimationListeners.push(fn)
}
export function emitCartAnimation(payload) {
    cartAnimationListeners.forEach(fn => fn(payload))
}

function save() {
    localStorage.setItem('cart', JSON.stringify(state.items))
}

function saveSelected() {
    localStorage.setItem('cart_selected', JSON.stringify(state.selectedIds))
}

export function useCartStore() {
    function addItem(product, quantity = 1, sourceRect = null) {
        const variant = product.selectedVariant || null
        const cartKey = variant ? `${product.id}:${variant.id}` : `${product.id}`
        const existing = state.items.find(i => i.cart_key === cartKey)
        if (existing) {
            existing.quantity += quantity
        } else {
            state.items.push({
                cart_key: cartKey,
                product_id: product.id,
                variant_id: variant?.id || null,
                variant_name: variant?.name || null,
                name: product.name,
                slug: product.slug,
                price: variant?.price ?? product.price,
                weight: variant?.weight || product.weight || 300,
                thumbnail: product.thumbnail,
                quantity,
            })
            // Auto-select item baru yang ditambahkan
            if (!state.selectedIds.includes(cartKey)) {
                state.selectedIds.push(cartKey)
                saveSelected()
            }
        }
        save()
        emitCartAnimation({
            thumbnail: product.thumbnail || product.images?.[0]?.path || null,
            sourceRect,
        })
    }

    function itemKey(itemOrId) {
        if (typeof itemOrId === 'object') return itemOrId.cart_key || `${itemOrId.product_id}`
        return `${itemOrId}`
    }

    function updateQuantity(productId, quantity) {
        const key = itemKey(productId)
        const item = state.items.find(i => (i.cart_key || `${i.product_id}`) === key)
        if (item) {
            item.quantity = quantity
            if (item.quantity <= 0) {
                removeItem(key)
            }
        }
        save()
    }

    function removeItem(productId) {
        const key = itemKey(productId)
        state.items = state.items.filter(i => (i.cart_key || `${i.product_id}`) !== key)
        // Hapus dari selected juga
        state.selectedIds = state.selectedIds.filter(id => id !== key)
        save()
        saveSelected()
    }

    function clear() {
        state.items = []
        state.selectedIds = []
        save()
        saveSelected()
    }

    function toggleSelect(productId) {
        const key = itemKey(productId)
        const idx = state.selectedIds.indexOf(key)
        if (idx === -1) {
            state.selectedIds.push(key)
        } else {
            state.selectedIds.splice(idx, 1)
        }
        saveSelected()
    }

    function selectAll() {
        state.selectedIds = state.items.map(i => i.cart_key || `${i.product_id}`)
        saveSelected()
    }

    function deselectAll() {
        state.selectedIds = []
        saveSelected()
    }

    function isSelected(productId) {
        return state.selectedIds.includes(itemKey(productId))
    }

    const selectedItems = computed(() =>
        state.items.filter(i => state.selectedIds.includes(i.cart_key || `${i.product_id}`))
    )

    const total = () => state.items.reduce((sum, i) => sum + i.price * i.quantity, 0)

    const selectedTotal = () => selectedItems.value.reduce((sum, i) => sum + i.price * i.quantity, 0)

    // Inisialisasi: jika belum ada selected, select semua
    if (state.selectedIds.length === 0 && state.items.length > 0) {
        state.selectedIds = state.items.map(i => i.cart_key || `${i.product_id}`)
        saveSelected()
    }

    return {
        items: toRefs(state).items,
        selectedIds: toRefs(state).selectedIds,
        selectedItems,
        addItem,
        updateQuantity,
        removeItem,
        clear,
        toggleSelect,
        selectAll,
        deselectAll,
        isSelected,
        total,
        selectedTotal,
    }
}
