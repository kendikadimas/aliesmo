import { reactive, toRefs } from 'vue'

// Migrate item lama yang belum punya cart_key atau size_id
function migrateItems(items) {
    return items.map(i => {
        if (!i.cart_key) {
            const key = i.size_id
                ? `${i.product_id}_${i.variant_id}_${i.size_id}`
                : i.variant_id
                    ? `${i.product_id}_${i.variant_id}`
                    : `${i.product_id}`
            return { ...i, cart_key: key }
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

// Unique key per item: product_id + variant_id + size_id
function itemKey(productId, variantId = null, sizeId = null) {
    if (sizeId) return `${productId}_${variantId}_${sizeId}`
    if (variantId) return `${productId}_${variantId}`
    return `${productId}`
}

export function useCartStore() {
    function addItem(product, quantity = 1) {
        const variantId   = product.selectedVariant?.id || null
        const variantName = product.selectedVariant?.name || null
        const sizeId      = product.selectedSize?.id || null
        const sizeName    = product.selectedSize?.name || null
        const price       = product.selectedVariant?.price ?? product.price
        // Ambil weight dari size > variant > product > default 300g
        const weight      = product.selectedSize?.weight ?? product.selectedVariant?.weight ?? product.weight ?? 300
        const key         = itemKey(product.id, variantId, sizeId)

        // Compose display name: "Warna - Ukuran" or just "Warna" or just product name
        let displayName = product.name
        let variantDisplayName = null
        if (variantName && sizeName) {
            variantDisplayName = `${variantName} - ${sizeName}`
        } else if (variantName) {
            variantDisplayName = variantName
        }

        const existing = state.items.find(i => i.cart_key === key)
        if (existing) {
            existing.quantity += quantity
        } else {
            state.items.push({
                cart_key:     key,
                product_id:   product.id,
                name:         product.name,
                slug:         product.slug,
                price,
                weight,
                thumbnail:    product.thumbnail,
                quantity,
                variant_id:   variantId,
                variant_name: variantName,
                size_id:      sizeId,
                size_name:    sizeName,
            })
        }
        save()
        window.dispatchEvent(new CustomEvent('cart:added', {
            detail: { name: product.name, thumbnail: product.thumbnail, quantity, variant_name: variantDisplayName }
        }))
    }

    function updateQuantity(cartKey, quantity) {
        const item = state.items.find(i => i.cart_key === cartKey)
        if (!item) return
        if (quantity <= 0) {
            removeItem(cartKey) // removeItem sudah panggil save()
            return
        }
        item.quantity = quantity
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
