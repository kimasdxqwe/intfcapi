<template>
    <button ref="el" :type="type" :class="classes" :disabled="disabled || loading">
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
    loading: { type: Boolean, default: false },
});

const el = ref(null);

defineExpose({
    focus: () => el.value?.focus(),
    el, // optional: the raw <button> element
});

const base =
    'cursor-pointer rounded border border-gray-300 px-3 py-1 font-label ' +
    'disabled:cursor-not-allowed disabled:opacity-50 ' +
    'focus:outline-none focus:ring-2 focus:border-slate-500 focus:ring-slate-300';

const variants = {
    clear: 'bg-white text-gray-700 hover:bg-gray-200',
    default: 'bg-gray-700 text-white text-shadow-md hover:bg-gray-600',
    danger: 'bg-red-600 text-white text-shadow-md hover:bg-red-700',
};

const classes = computed(() => [base, variants[props.variant]]);
</script>
