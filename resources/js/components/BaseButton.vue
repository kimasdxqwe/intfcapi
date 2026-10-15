<template>
    <button ref="el" :type="type" :class="classes" :disabled="disabled">
        <svg
            v-if="pending"
            class="-my-1 size-5 shrink-0 animate-spin"
            viewBox="0 0 20 20"
            fill="none"
            aria-hidden="true"
        >
            <circle cx="10" cy="10" r="7" stroke="currentColor" stroke-width="2.5" class="opacity-25" />
            <path d="M17 10a7 7 0 00-7-7" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" />
        </svg>

        <slot>{{ label }}</slot>
    </button>
</template>

<script setup>
import {computed, ref} from 'vue';

const props = defineProps({
    variant: {
        type: String,
        default: 'default',
        validator: (v) => ['clear', 'default', 'danger'].includes(v),
    },
    label: { type: String, default: '' },
    type: { type: String, default: 'button' },
    disabled: { type: Boolean, default: false },
    pending: { type: Boolean, default: false },
});

const el = ref(null);

defineExpose({
    focus: () => el.value?.focus(),
    el, // optional: the raw <button> element
});

const base =
    'inline-flex items-center justify-center gap-2 cursor-pointer rounded border border-gray-300 px-3 py-1 font-data ' +
    'disabled:cursor-not-allowed disabled:opacity-50 ' +
    'focus:outline-none focus:ring-2 focus:border-slate-500 focus:ring-slate-300';

const variants = {
    clear: 'bg-white text-gray-700 hover:bg-gray-200',
    default: 'bg-gray-700 text-white text-shadow-md hover:bg-gray-600',
    danger: 'bg-red-600 text-white text-shadow-md hover:bg-red-700',
};

const classes = computed(() => [base, variants[props.variant]]);
</script>
