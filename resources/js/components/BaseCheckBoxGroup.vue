<template>
    <fieldset :disabled="disabled" class="min-w-0 border-0 p-0 disabled:opacity-50">
        <legend v-if="legend" class="mb-2 text-sm font-medium text-gray-700">{{ legend }}</legend>

        <div :class="['flex', direction === 'horizontal' ? 'flex-row flex-wrap gap-x-5 gap-y-2' : 'flex-col gap-2']">
            <label
                v-for="o in normalized"
                :key="String(o.value)"
                :class="[
                    'inline-flex items-center gap-2 text-sm',
                    o.disabled || disabled ? 'cursor-not-allowed text-gray-400' : 'cursor-pointer text-gray-800',
                ]"
            >
                <input
                    type="checkbox"
                    class="peer sr-only"
                    :name="name"
                    :value="o.value"
                    :checked="isSelected(o)"
                    :disabled="o.disabled"
                    @change="toggle(o)"
                />

                <span
                    :class="[
                        'flex size-4 shrink-0 items-center justify-center rounded border',
                        'peer-focus-visible:ring-2 peer-focus-visible:ring-slate-300',
                        isSelected(o)
                            ? 'border-slate-700 bg-slate-700 text-white'
                            : error
                              ? 'border-red-500 bg-white'
                              : 'border-gray-300 bg-white',
                    ]"
                >
                    <svg v-if="isSelected(o)" class="size-3" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path
                            fill-rule="evenodd"
                            d="M16.7 5.3a1 1 0 010 1.4l-8 8a1 1 0 01-1.4 0l-4-4a1 1 0 011.4-1.4L8 12.6l7.3-7.3a1 1 0 011.4 0z"
                            clip-rule="evenodd"
                        />
                    </svg>
                </span>

                <span class="font-data" :class="isSelected(o) && 'font-semibold'">{{ o.label }}</span>
            </label>
        </div>
    </fieldset>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    modelValue: { type: Array, default: () => [] }, // [{ label, value }, ...]
    options: { type: Array, default: () => [] },
    optionLabel: { type: String, default: 'label' },
    optionValue: { type: String, default: 'value' },
    legend: { type: String, default: '' },
    direction: { type: String, default: 'vertical' },
    disabled: { type: Boolean, default: false },
    error: { type: Boolean, default: false },
});

const emit = defineEmits(['update:modelValue']);

const name = 'checkbox-' + Math.random().toString(36).slice(2, 8);

const normalized = computed(() =>
    props.options.map((o) => ({
        label: String(o[props.optionLabel]),
        value: o[props.optionValue],
        disabled: !!o.disabled,
    }))
);

const selectedOptions = computed(() => (Array.isArray(props.modelValue) ? props.modelValue : []));
const selectedValues = computed(() => selectedOptions.value.map((o) => o.value));

function isSelected(option) {
    return selectedValues.value.includes(option.value);
}

function toggle(option) {
    if (option.disabled) return;

    const next = isSelected(option)
        ? selectedOptions.value.filter((o) => o.value !== option.value)
        : [...selectedOptions.value, { label: option.label, value: option.value }];

    emit('update:modelValue', next);
}
</script>
