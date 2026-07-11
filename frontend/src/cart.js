import { reactive, toRefs } from 'vue'

// Migrate item lama yang belum punya cart_key
function migrateItems(items) {
    return items.map(i => {
        if (!i.cart_key) {
            return { ...i, cart_key: i.variant_id ? `${i.product_id}_${i.variant_id}` : `${i.product_id}` }
        }
        return i
    })
}

const state = reactive({
    items: migrateItems(JSON.parse(localStorage.getItem('cart') || '[]')),
})

function save() {
    localStorage.setItem('cart', JSON.stringify(state.items))
}

// Unique key per item: product_id + variant_id (null jika tidak ada varian)
function itemKey(productId, variantId = null) {
    return variantId ? `${productId}_${variantId}` : `${productId}`
}

export function useCartStore() {
    function addItem(product, quantity = 1) {
        const variantId = product.selectedVariant?.id || null
        const variantName = product.selectedVariant?.name || null
        const price = product.selectedVariant?.price ?? product.price
        const key = itemKey(product.id, variantId)

        const existing = state.items.find(i => i.cart_key === key)
        if (existing) {
            existing.quantity += quantity
        } else {
            state.items.push({
                cart_key: key,
                product_id: product.id,
                name: product.name,
                slug: product.slug,
                price,
                thumbnail: product.thumbnail,
                quantity,
                variant_id: variantId,
                variant_name: variantName,
            })
        }
        save()
        window.dispatchEvent(new CustomEvent('cart:added', {
            detail: { name: product.name, thumbnail: product.thumbnail, quantity, variant_name: variantName }
        }))
    }

    function updateQuantity(cartKey, quantity) {
        const item = state.items.find(i => i.cart_key === cartKey)
        if (item) {
            item.quantity = quantity
            if (item.quantity <= 0) removeItem(cartKey)
        }
        save()
    }

    function removeItem(cartKey) {
        state.items = state.items.filter(i => i.cart_key !== cartKey)
        save()
    }

    function clear() {
        state.items = []
        save()
    }

    const total = () => state.items.reduce((sum, i) => sum + i.price * i.quantity, 0)

    return {
        items: toRefs(state).items,
        addItem,
        updateQuantity,
        removeItem,
        clear,
        total,
    }
}
