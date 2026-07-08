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
import ProfilePage from './pages/ProfilePage.vue'
import TermsPage from './pages/TermsPage.vue'
import AuthCallbackPage from './pages/AuthCallbackPage.vue'

const routes = [
    { path: '/', name: 'home', component: HomePage },
    { path: '/products/:slug', name: 'product-detail', component: ProductDetailPage },
    { path: '/cart', name: 'cart', component: CartPage },
    { path: '/catalog', name: 'catalog', component: CatalogPage },
    { path: '/catalog/:slug', name: 'catalog-category', component: CatalogPage },
    { path: '/checkout', name: 'checkout', component: CheckoutPage },
    { path: '/order/:orderNumber', name: 'order-confirmation', component: OrderConfirmationPage },
    { path: '/login', name: 'login', component: LoginPage },
    { path: '/register', name: 'register', component: RegisterPage },
    { path: '/profile', name: 'profile', component: ProfilePage },
    { path: '/terms', name: 'terms', component: TermsPage },
    { path: '/auth/callback', name: 'auth-callback', component: AuthCallbackPage },
    { path: '/:pathMatch(.*)*', name: 'not-found', redirect: '/' },
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
