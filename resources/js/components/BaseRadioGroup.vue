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
                    type="radio"
                    class="peer sr-only"
                    :name="name"
                    :value="o.value"
                    :checked="isSelected(o)"
                    :disabled="o.disabled"
                    @change="select(o)"
                />

                <span
                    :class="[
                        'flex size-4 shrink-0 items-center justify-center rounded-full border',
                        'peer-focus-visible:ring-2 peer-focus-visible:ring-slate-300',
                        isSelected(o)
                            ? 'border-slate-700 bg-slate-700'
                            : error
                              ? 'border-red-500 bg-white'
                              : 'border-gray-300 bg-white',
                    ]"
                >
                    <span v-if="isSelected(o)" class="size-1.5 rounded-full bg-white"></span>
                </span>

                <span :class="isSelected(o) && 'font-semibold'">{{ o.label }}</span>
            </label>
        </div>
    </fieldset>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    modelValue: { type: Array, default: () => [] }, // [] or [{ label, value }]
    options: { type: Array, default: () => [] },    // [{ label, value, disabled }]
    optionLabel: { type: String, default: 'label' },
    optionValue: { type: String, default: 'value' },
    legend: { type: String, default: '' },
    direction: { type: String, default: 'vertical' }, // 'vertical' | 'horizontal'
    disabled: { type: Boolean, default: false },
    error: { type: Boolean, default: false },
});

const emit = defineEmits(['update:modelValue']);

const name = 'radio-' + Math.random().toString(36).slice(2, 8);

const normalized = computed(() =>
    props.options.map((o) => ({
        label: String(o[props.optionLabel]),
        value: o[props.optionValue],
        disabled: !!o.disabled,
    }))
);

// single selection: only the first item counts
const selectedOptions = computed(() =>
    (Array.isArray(props.modelValue) ? props.modelValue : []).slice(0, 1)
);

function isSelected(option) {
    return selectedOptions.value[0]?.value === option.value;
}

function select(option) {
    if (option.disabled) return;
    emit('update:modelValue', [{ label: option.label, value: option.value }]);
}
</script>
