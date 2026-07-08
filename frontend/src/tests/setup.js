import { config } from '@vue/test-utils'
import { RouterLink, RouterView } from 'vue-router'

// Stub router components globally — tanpa install router plugin
// Router plugin di-pass per-test di mountCheckout() saja
config.global.stubs = {
    RouterLink: true,
    RouterView: true,
}
