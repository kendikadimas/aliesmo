import { ref } from 'vue'
import api from './api'

// Module-level cache — shared across all composable instances
let _settings = null
let _promise = null

export function useSettings() {
    const settings = ref(_settings || {})
    const loading = ref(!_settings)

    async function fetchSettings() {
        // Already fetched — return immediately
        if (_settings) {
            settings.value = _settings
            loading.value = false
            return _settings
        }

        // Already in-flight — wait for it
        if (_promise) {
            const result = await _promise
            settings.value = result
            loading.value = false
            return result
        }

        // First fetch
        _promise = api.get('/settings')
            .then(res => {
                _settings = res.data.data || {}
                return _settings
            })
            .catch(() => {
                _settings = {}
                return {}
            })
            .finally(() => {
                _promise = null
            })

        const result = await _promise
        settings.value = result
        loading.value = false
        return result
    }

    function get(key, fallback = '') {
        return settings.value[key] ?? fallback
    }

    return { settings, loading, fetchSettings, get }
}
