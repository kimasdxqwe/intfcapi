import { defineStore } from 'pinia';
import { ref, markRaw } from 'vue';

let nextId = 1;

export const useModalStore = defineStore('modal', () => {
    const stack = ref([]);

    function push(config) {

        return new Promise((resolve) => {
            stack.value.push({ id: nextId++, resolve, ...config });
        });
    }

    function close(id, result) {

        const index = stack.value.findIndex((m) => m.id === id);

        if (index === -1) return;

        const [modal] = stack.value.splice(index, 1);

        modal.resolve(result);
    }

    function canDismiss(modal, source) {

        const fallback = modal.dismissible !== false;

        const specific = {
            escape: modal.closeOnEscape,
            backdrop: modal.closeOnBackdrop,
            button: modal.showCloseButton,
        }[source];

        return specific ?? fallback; // specific option wins, otherwise the shorthand
    }

    function triggerShake(id) {

        const modal = stack.value.find((m) => m.id === id);

        if (!modal || modal.shake) return; // ignore if already shaking

        modal.shake = true;

        setTimeout(() => {
            modal.shake = false;
        }, 300);
    }

    // Backdrop click, X button, Escape
    function dismiss(id, source = 'button') {

        const modal = stack.value.find((m) => m.id === id);

        if (!modal) return false;

        if (!canDismiss(modal, source)) {
            triggerShake(id);
            return false;
        }

        close(id, modal.dismissValue);

        return true;
    }

    function alert(message, options = {}) {
        return push({
            type: 'alert',
            title: 'Notice',
            subTitle: null,
            okText: 'Okay',
            dismissValue: true,
            message,
            ...options
        });
    }

    function confirm(message, options = {}) {
        return push({
            type: 'confirm',
            title: 'Please confirm',
            subTitle: null,
            okText: 'Confirm',
            cancelText: 'Cancel',
            dismissValue: false,
            message,
            ...options,
        });
    }

    function prompt(message, options = {}) {
        return push({
            type: 'prompt',
            title: 'Input required',
            subTitle: null,
            okText: 'Okay',
            cancelText: 'Cancel',
            dismissValue: null,
            message,
            ...options,
        });
    }

    function open(component, props = {}, options = {}) {
        return push({
            type: 'custom',
            title: '',
            subTitle: null,
            size: 'md',
            dismissValue: null,
            component: markRaw(component),
            props,
            ...options,
        });
    }

    return { stack, close, dismiss, canDismiss, alert, confirm, prompt, open };
});
