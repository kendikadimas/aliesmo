import { reactive } from 'vue'

const state = reactive({
    visible: false,
    message: '',
    type: 'success',
    timer: null,
})

export function useToast() {
    function show(message, type = 'success', duration = 3000) {
        if (state.timer) clearTimeout(state.timer)
        state.message = message
        state.type = type
        state.visible = true
        state.timer = setTimeout(() => {
            state.visible = false
        }, duration)
    }

    function hide() {
        state.visible = false
        if (state.timer) clearTimeout(state.timer)
    }

    return { state, show, hide }
}
