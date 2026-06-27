import { createApp } from 'vue'
import { createRouter, createWebHistory } from 'vue-router'
import App from './App.vue'
import HomePage from './pages/HomePage.vue'
import ProductDetailPage from './pages/ProductDetailPage.vue'
import CartPage from './pages/CartPage.vue'
import CheckoutPage from './pages/CheckoutPage.vue'
import OrderConfirmationPage from './pages/OrderConfirmationPage.vue'
import LoginPage from './pages/LoginPage.vue'
import RegisterPage from './pages/RegisterPage.vue'
import './style.css'

const routes = [
    { path: '/', name: 'home', component: HomePage },
    { path: '/products/:slug', name: 'product-detail', component: ProductDetailPage },
    { path: '/cart', name: 'cart', component: CartPage },
    { path: '/checkout', name: 'checkout', component: CheckoutPage },
    { path: '/order/:orderNumber', name: 'order-confirmation', component: OrderConfirmationPage },
    { path: '/login', name: 'login', component: LoginPage },
    { path: '/register', name: 'register', component: RegisterPage },
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
