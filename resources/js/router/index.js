import About from '@/pages/About.vue'
import Dashboard from '@/pages/Dashboard.vue'
import Dev from "@/pages/Dev.vue";
import Home from '@/pages/Home.vue'
import NotFound from "@/pages/NotFound.vue";
import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth';
import { useEnv } from '@/composables/useEnv';

const appName = import.meta.env.VITE_APP_NAME ?? 'App name';
const { isProduction } = useEnv();

const devRoutes = !isProduction
    ? [
        { path: '/dev', name: 'dev', component: Dev, meta: { requiresAuth: true }}
    ] : [];

const router = createRouter({
    history: createWebHistory(),
    routes: [
        { path: '/home', name: 'home', component: Home, meta: { title: 'Home' } },
        { path: '/about', name: 'about', component: About, meta: { title: 'About' } },
        { path: '/dashboard', name: 'dashboard', component: Dashboard, meta: { title: 'Dashboard', requiresAuth: true} },
        ...devRoutes,
        { path: '/:pathMatch(.*)*', name: 'not-found', component: NotFound, meta: { title: 'Not found' } },
    ],
})

router.afterEach((to) => {
    document.title = to.meta.title ? `${to.meta.title} | ${appName}` : appName
})

router.beforeEach(async (to) => {
    const auth = useAuthStore();

    if (!auth.ready) {
        // first load or hard refresh
        await auth.fetchUser();
    }

    if (to.meta.requiresAuth && !auth.isAuthenticated) {
        auth.redirectToLogin(to.fullPath);
        // cancel the in-app navigation, the browser is leaving
        return false;
    }
});

export default router
