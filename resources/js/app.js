
// import './bootstrap'
import App from './App.vue'
import router from './router'
import { createApp } from 'vue'
import { createPinia } from 'pinia';
import { useAuthStore } from '@/stores/auth';
import { useEnv } from "@/composables/useEnv";
import { useModalStore } from "@/stores/modal";

const app = createApp(App);

app.use(createPinia()); // before the router, since the guard uses the store
app.use(router);

// Session expired while using the app: any 401 on a protected page goes to login
window.addEventListener('auth:unauthenticated', async () => {

    const { isProduction } = useEnv();
    const auth = useAuthStore();

    auth.resetUser();

    const current = router.currentRoute.value;

    if (current.meta.requiresAuth) {

        const modal = useModalStore();

        await modal.alert(null, {
            title: 'Unauthenticated',
            subTitle: 'Login again to restore session',
            showCloseButton: false,
            dismissible: false
        });

        auth.redirectToLogin(current.fullPath);
    }

    if(!isProduction){

        console.log({
            'Event': 'auth:unauthenticated',
            'Current route': current,
            'Current route meta': current.meta,
            'Auth user': auth.user
        });
    }
});

app.mount('#app');
