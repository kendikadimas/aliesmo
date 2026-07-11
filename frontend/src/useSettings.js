import { ref } from 'vue'
import api from './api'

// Module-level cache — shared across all composable instances
let _settings = null
let _promise = null

export function useSettings() {
    const settings = ref(_settings || {})
    const loading = ref(!_settings)

    async function fetchSettings(forceRefresh = false) {
        // Force refresh - clear cache
        if (forceRefresh) {
            _settings = null
            _promise = null
        }

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
        console.log('[useSettings] Fetching settings from API...')
        _promise = api.get('/settings')
            .then(res => {
                console.log('[useSettings] API response:', res.data)
                _settings = res.data.data || {}
                console.log('[useSettings] Parsed settings:', _settings)
                return _settings
            })
            .catch((err) => {
                console.error('[useSettings] Fetch error:', err)
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
