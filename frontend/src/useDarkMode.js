import { ref } from 'vue'

const isDark = ref(false)

export function useDarkMode() {
    function apply(dark) {
        if (dark) {
            document.documentElement.classList.add('dark')
        } else {
            document.documentElement.classList.remove('dark')
        }
    }

    function toggle() {
        isDark.value = !isDark.value
        localStorage.setItem('theme', isDark.value ? 'dark' : 'light')
        apply(isDark.value)
    }

    function init() {
        const saved = localStorage.getItem('theme')
        if (saved) {
            isDark.value = saved === 'dark'
        } else {
            isDark.value = false  // default: light mode
        }
        apply(isDark.value)
    }

    return { isDark, toggle, init }
}
