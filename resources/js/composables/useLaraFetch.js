import { createFetch } from '@vueuse/core';
import { useEnv } from '@/composables/useEnv';
import {useModalStore} from "@/stores/modal";

// Laravel keeps the CSRF token (URL-encoded) in this cookie
function getXsrfToken() {

    const match = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]+)/);

    return match ? decodeURIComponent(match[1]) : '';
}

const CSRF_METHODS = ['POST', 'PUT', 'PATCH', 'DELETE'];

export const useLaraFetch = createFetch({
    options: {
        updateDataOnError: true, // error bodies (e.g. 422 errors) land in `data`

        beforeFetch({ options }) {

            const method = (options.method || 'GET').toUpperCase();

            const headers = {
                ...options.headers,
                Accept: 'application/json', // 401/422 JSON instead of redirects
                'X-Requested-With': 'XMLHttpRequest',
            };

            if (CSRF_METHODS.includes(method)) {
                headers['X-XSRF-TOKEN'] = getXsrfToken();
            }

            options.headers = headers;

            return { options };
        },

        onFetchError(ctx) {

            let message = ctx?.error?.message;

            const modal = useModalStore();

            const { isProduction } = useEnv();

            if(!isProduction){

                console.log({
                    'Fetch error': ctx,
                    'Status': ctx.response?.status,
                    'Message': message
                });
            }

            if (ctx.response?.status >= 400 && ctx.response?.status < 500) {

                if (ctx.response?.status === 401) {

                    window.dispatchEvent(new Event('auth:unauthenticated'));

                    return ctx;
                }

                modal.alert(null, {
                    title: 'Client error',
                    subTitle: message ?? 'Message not found',
                    showCloseButton: true,
                    dismissible: true
                });
            }

            if (ctx.response?.status >= 500) {

                if (ctx.response?.status === 503) {

                    modal.alert(null, {
                        title: 'Server is on Maintenance Mode',
                        subTitle: message ?? 'Service Unavailable',
                        showCloseButton: true,
                        dismissible: false
                    });

                    return ctx;
                }

                modal.alert(null, {
                    title: message ?? 'Something Went Wrong',
                    subTitle: 'Try again some time',
                    showCloseButton: true,
                    dismissible: false
                });
            }

            return ctx;
        },
    },
});
