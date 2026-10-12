<template>
    <label
        class="p-2"
        :class="[
            'inline-flex items-center gap-2 text-sm',
            disabled ? 'cursor-not-allowed text-gray-400 opacity-50' : 'cursor-pointer text-gray-700',
        ]"
    >
        <input
            type="checkbox"
            class="peer sr-only"
            :checked="modelValue"
            :disabled="disabled"
            :aria-checked="indeterminate ? 'mixed' : modelValue"
            @change="onChange"
        />

        <span
            :class="[
                'flex size-4 shrink-0 items-center justify-center rounded border',
                'peer-focus-visible:ring-2 peer-focus-visible:ring-slate-300',
                modelValue || indeterminate
                    ? 'border-slate-700 bg-slate-700 text-white'
                    : error
                      ? 'border-red-500 bg-white'
                      : 'border-gray-300 bg-white',
            ]"
        >
            <svg v-if="indeterminate" class="size-3" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path d="M4 9.25h12a.75.75 0 010 1.5H4a.75.75 0 010-1.5z" />
            </svg>
            <svg v-else-if="modelValue" class="size-3" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path
                    fill-rule="evenodd"
                    d="M16.7 5.3a1 1 0 010 1.4l-8 8a1 1 0 01-1.4 0l-4-4a1 1 0 011.4-1.4L8 12.6l7.3-7.3a1 1 0 011.4 0z"
                    clip-rule="evenodd"
                />
            </svg>
        </span>

        <slot>{{ label }}</slot>
    </label>
</template>

<script setup>
const props = defineProps({
    modelValue: { type: Boolean, default: false },
    label: { type: String, default: '' },
    disabled: { type: Boolean, default: false },
    error: { type: Boolean, default: false },
    indeterminate: { type: Boolean, default: false }, // dash instead of a check (e.g. a "select all" parent)
});

const emit = defineEmits(['update:modelValue']);

function onChange(e) {
    emit('update:modelValue', e.target.checked);
}
</script>
