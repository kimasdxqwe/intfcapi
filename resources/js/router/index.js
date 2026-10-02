import { createRouter, createWebHistory } from 'vue-router'
import Home from '../pages/Home.vue'
import About from '../pages/About.vue'
import Contact from '../pages/Contact.vue'
import Dashboard from '../pages/Dashboard.vue'

const router = createRouter({
    history: createWebHistory(),
    routes: [
        { path: '/home', name: 'home', component: Home, meta: { title: 'Home' } },
        { path: '/about', name: 'about', component: About, meta: { title: 'About' } },
        { path: '/contact', name: 'contact', component: Contact, meta: { title: 'Contact' } },
        { path: '/dashboard', name: 'dashboard', component: Dashboard, meta: { title: 'Dashboard' } },
    ],
})

const appName = 'Intfcapi'

router.afterEach((to) => {
    document.title = to.meta.title ? `${to.meta.title} | ${appName}` : appName
})

export default router
