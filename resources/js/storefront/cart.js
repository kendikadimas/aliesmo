import { reactive, toRefs } from 'vue'

const state = reactive({
    items: JSON.parse(localStorage.getItem('cart') || '[]'),
})

function save() {
    localStorage.setItem('cart', JSON.stringify(state.items))
}

export function useCartStore() {
    function addItem(product, quantity = 1) {
        const existing = state.items.find(i => i.product_id === product.id)
        if (existing) {
            existing.quantity += quantity
        } else {
            state.items.push({
                product_id: product.id,
                name: product.name,
                slug: product.slug,
                price: product.price,
                thumbnail: product.thumbnail,
                quantity,
            })
        }
        save()
    }

    function updateQuantity(productId, quantity) {
        const item = state.items.find(i => i.product_id === productId)
        if (item) {
            item.quantity = quantity
            if (item.quantity <= 0) {
                removeItem(productId)
            }
        }
        save()
    }

    function removeItem(productId) {
        state.items = state.items.filter(i => i.product_id !== productId)
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
