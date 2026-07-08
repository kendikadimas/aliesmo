import { createApp } from 'vue'
import { createRouter, createWebHistory } from 'vue-router'
import App from './App.vue'
import HomePage from './pages/HomePage.vue'
import ProductDetailPage from './pages/ProductDetailPage.vue'
import CartPage from './pages/CartPage.vue'
import CatalogPage from './pages/CatalogPage.vue'
import CheckoutPage from './pages/CheckoutPage.vue'
import OrderConfirmationPage from './pages/OrderConfirmationPage.vue'
import LoginPage from './pages/LoginPage.vue'
import RegisterPage from './pages/RegisterPage.vue'
import MyOrdersPage from './pages/MyOrdersPage.vue'
import ProfilePage from './pages/ProfilePage.vue'
import ForgotPasswordPage from './pages/ForgotPasswordPage.vue'
import ResetPasswordPage from './pages/ResetPasswordPage.vue'
import NotFoundPage from './pages/NotFoundPage.vue'
import PrivacyPage from './pages/PrivacyPage.vue'
import TermsPage from './pages/TermsPage.vue'
import ShippingInfoPage from './pages/ShippingInfoPage.vue'
import SizeGuidePage from './pages/SizeGuidePage.vue'
import TrackOrderPage from './pages/TrackOrderPage.vue'
import EmailVerificationPage from './pages/EmailVerificationPage.vue'

const routes = [
    { path: '/', name: 'home', component: HomePage },
    { path: '/products/:slug', name: 'product-detail', component: ProductDetailPage },
    { path: '/cart', name: 'cart', component: CartPage },
    { path: '/catalog', name: 'catalog', component: CatalogPage },
    { path: '/catalog/:slug', name: 'catalog-category', component: CatalogPage },
    { path: '/checkout', name: 'checkout', component: CheckoutPage },
    { path: '/order/:orderNumber', name: 'order-confirmation', component: OrderConfirmationPage },
    { path: '/track-order', name: 'track-order', component: TrackOrderPage },
    { path: '/login', name: 'login', component: LoginPage },
    { path: '/register', name: 'register', component: RegisterPage },
    { path: '/orders', name: 'my-orders', component: MyOrdersPage },
    { path: '/profile', name: 'profile', component: ProfilePage },
    { path: '/forgot-password', name: 'forgot-password', component: ForgotPasswordPage },
    { path: '/reset-password', name: 'reset-password', component: ResetPasswordPage },
    { path: '/privacy', name: 'privacy', component: PrivacyPage },
    { path: '/terms', name: 'terms', component: TermsPage },
    { path: '/shipping-info', name: 'shipping-info', component: ShippingInfoPage },
    { path: '/size-guide', name: 'size-guide', component: SizeGuidePage },
    { path: '/email/verify', name: 'email-verify', component: EmailVerificationPage },
    { path: '/:pathMatch(.*)*', name: 'not-found', component: NotFoundPage },
]

const router = createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior() {
        return { top: 0 }
    },
})

const app = createApp(App)
app.use(router)
app.mount('#app')

// Handle auth expired event dari api.js interceptor
// Pakai router.push supaya navigate di dalam Vue, bukan reload ke Laravel port
window.addEventListener('auth:expired', () => {
    router.push('/login')
})

// Handle email belum terverifikasi — arahkan ke halaman verifikasi
window.addEventListener('auth:unverified', () => {
    router.push('/email/verify')
})
