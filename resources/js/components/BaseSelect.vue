<template>
    <div ref="root" class="relative" @keydown="onKeydown">
        <!-- Trigger -->
        <div
            ref="trigger"
            role="combobox"
            aria-haspopup="listbox"
            :aria-expanded="open"
            :aria-controls="`${uid}-list`"
            :aria-disabled="disabled"
            :tabindex="disabled ? -1 : 0"
            :class="[
                'flex min-h-[2.125rem] w-full items-center gap-2 rounded border bg-white px-3 py-1',
                'focus:outline-none focus:ring-2',
                error ? 'border-red-500 focus:ring-red-200' : 'border-gray-300 focus:border-slate-500 focus:ring-slate-300',
                disabled ? 'cursor-not-allowed opacity-50' : 'cursor-pointer',
            ]"
            @click="toggle"
        >
            <div class="flex min-w-0 flex-1 flex-wrap items-center gap-1">
                <!-- Multiple: chips -->
                <template v-if="multiple && selectedOptions.length">
                    <span
                        v-for="o in visibleOptions"
                        :key="String(o.value)"
                        class="inline-flex items-center gap-1 rounded bg-gray-200 px-2 py-0.5 text-sm font-data text-gray-800"
                    >
                        {{ o.label }}
                        <button
                            v-if="!disabled"
                            type="button"
                            :aria-label="`Remove ${o.label}`"
                            class="flex shrink-0 cursor-pointer items-center justify-center text-gray-500 hover:text-gray-900"
                            @click.stop="removeValue(o.value)"
                        >
                            <svg class="size-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path
                                    d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 6.28z"
                                />
                            </svg>
                        </button>
                    </span>

                    <span
                        v-if="hiddenCount > 0"
                        class="inline-flex items-center rounded bg-slate-200 px-2 py-0.5 text-sm font-data font-semibold text-slate-700"
                    >
                        +{{ hiddenCount }} more
                    </span>
                </template>

                <!-- Single: selected label -->
                <span v-else-if="!multiple && singleSelected" class="truncate text-gray-900 font-data">
                    {{ singleSelected.label }}
                </span>

                <!-- Placeholder -->
                <span v-else class="truncate text-gray-700 font-data">{{ placeholder }}</span>
            </div>

            <button
                v-if="clearable && hasValue && !disabled"
                type="button"
                aria-label="Clear selection"
                class="flex shrink-0 cursor-pointer items-center justify-center text-gray-400 hover:text-gray-700"
                @click.stop="clear"
            >
                <svg class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path
                        d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 6.28z"
                    />
                </svg>
            </button>

            <svg
                :class="['size-4 shrink-0 text-gray-500 transition-transform', open && 'rotate-180']"
                viewBox="0 0 20 20"
                fill="currentColor"
                aria-hidden="true"
            >
                <path
                    fill-rule="evenodd"
                    d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z"
                    clip-rule="evenodd"
                />
            </svg>
        </div>

        <!-- Dropdown -->
        <div
            v-if="open"
            class="absolute z-40 mt-1 w-full overflow-hidden rounded border border-gray-300 bg-white shadow-lg"
        >
            <div
                v-if="searchable || (multiple && showSelectAll)"
                class="flex items-center gap-2 border-b border-gray-200 px-3 py-2"
            >
                <!-- Select all checkbox (same box as the option rows) -->
                <button
                    v-if="multiple && showSelectAll"
                    type="button"
                    role="checkbox"
                    :aria-checked="allFilteredSelected ? 'true' : someFilteredSelected ? 'mixed' : 'false'"
                    aria-label="Select all"
                    :title="allFilteredSelected ? 'Deselect all' : 'Select all'"
                    :disabled="!selectableFiltered.length"
                    class="shrink-0 cursor-pointer disabled:cursor-not-allowed disabled:opacity-50"
                    @mousedown.prevent
                    @click="toggleAll"
                >
                <span
                    :class="[
                        'flex size-4 items-center justify-center rounded border text-xs leading-none',
                        allFilteredSelected || someFilteredSelected
                            ? 'border-slate-700 bg-slate-700 text-white'
                            : 'border-gray-300 bg-white',
                    ]"
                >
                <template v-if="allFilteredSelected">&#10003;</template>
                <template v-else-if="someFilteredSelected">&minus;</template>
                </span>
                        </button>

                        <input
                            v-if="searchable"
                            ref="searchEl"
                            v-model="query"
                            type="text"
                            placeholder="Search..."
                            autocomplete="off"
                            class="min-w-0 flex-1 rounded border border-gray-300 px-2 py-1 text-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-300"
                        />

                        <!-- Label when there is no search box -->
                        <span v-else-if="multiple && showSelectAll" class="text-sm text-gray-700">
                {{ allFilteredSelected ? 'Deselect all' : 'Select all' }}
            </span>
            </div>

            <ul
                :id="`${uid}-list`"
                role="listbox"
                :aria-multiselectable="multiple"
                class="max-h-60 overflow-y-auto py-1"
            >
                <li
                    v-for="(o, i) in filtered"
                    :id="`${uid}-opt-${i}`"
                    :key="String(o.value)"
                    role="option"
                    :aria-selected="isSelected(o)"
                    :aria-disabled="o.disabled"
                    :class="[
                        'flex items-center gap-2 px-3 py-1.5 text-sm',
                        o.disabled ? 'cursor-not-allowed text-gray-400' : 'cursor-pointer text-gray-800',
                        i === activeIndex && !o.disabled ? 'bg-gray-100' : '',
                        isSelected(o) ? 'font-semibold' : '',
                    ]"
                    @mouseenter="!o.disabled && (activeIndex = i)"
                    @click="select(o)"
                >
                    <!-- checkbox look for both single and multiple -->
                    <span
                        :class="[
                            'flex size-4 shrink-0 items-center justify-center border text-xs leading-none',
                            multiple ? 'rounded' : 'rounded-full',
                            isSelected(o) ? 'border-slate-700 bg-slate-700 text-white' : 'border-gray-300 bg-white',
                        ]"
                    >
                        <template v-if="isSelected(o)">{{ multiple ? '&#10003;' : '' }}</template>
                        <span v-if="isSelected(o) && !multiple" class="size-1.5 rounded-full bg-white"></span>
                    </span>

                    <span class="flex-1 truncate font-data">{{ o.label }}</span>
                </li>

                <li v-if="!filtered.length" class="px-3 py-2 text-sm text-gray-400">No results</li>
            </ul>
        </div>
    </div>
</template>

<script setup>
import {computed, nextTick, ref, watch} from 'vue';
import {onClickOutside} from '@vueuse/core';

const props = defineProps({
    modelValue: { type: Array, default: () => [] }, // always [{ label, value }]
    maxVisible: { type: Number, default: 0 },
    showSelectAll: { type: Boolean, default: true }, // multiple mode only
    options: {type: Array, default: () => []}, // ['a', 'b'] or [{ label, value, disabled }]
    multiple: {type: Boolean, default: false},
    placeholder: {type: String, default: 'Select...'},
    searchable: {type: Boolean, default: false},
    clearable: {type: Boolean, default: false},
    disabled: {type: Boolean, default: false},
    error: {type: Boolean, default: false},
    optionLabel: {type: String, default: 'label'},
    optionValue: {type: String, default: 'value'},
});

const emit = defineEmits(['update:modelValue']);

const uid = 'select-' + Math.random().toString(36).slice(2, 8);

const root = ref(null);
const trigger = ref(null);
const searchEl = ref(null);

const open = ref(false);
const query = ref('');
const activeIndex = ref(0);

// ---- options ----
const normalized = computed(() =>
    props.options.map((o) =>
        o !== null && typeof o === 'object'
            ? {label: String(o[props.optionLabel]), value: o[props.optionValue], disabled: !!o.disabled}
            : {label: String(o), value: o, disabled: false}
    )
);

const filtered = computed(() => {
    const q = query.value.trim().toLowerCase();
    return q ? normalized.value.filter((o) => o.label.toLowerCase().includes(q)) : normalized.value;
});

// ---- selection ----
// The model is the source of truth. Labels are stored with the values, so the
// selection still displays correctly when the option isn't in `options`
// (for example with async or paginated options).
const selectedOptions = computed(() => (Array.isArray(props.modelValue) ? props.modelValue : []));

const selectedValues = computed(() => selectedOptions.value.map((o) => o.value));

const singleSelected = computed(() => (props.multiple ? null : selectedOptions.value[0] ?? null));

const hasValue = computed(() => selectedOptions.value.length > 0);

const visibleOptions = computed(() =>
    props.multiple && props.maxVisible > 0
        ? selectedOptions.value.slice(0, props.maxVisible)
        : selectedOptions.value
);

const hiddenCount = computed(() => selectedOptions.value.length - visibleOptions.value.length);

const selectableFiltered = computed(() => filtered.value.filter((o) => !o.disabled));

const allFilteredSelected = computed(
    () => selectableFiltered.value.length > 0 && selectableFiltered.value.every(isSelected)
);

const someFilteredSelected = computed(
    () => !allFilteredSelected.value && selectableFiltered.value.some(isSelected)
);

function toggleAll() {
    const list = selectableFiltered.value;
    if (!list.length) return;

    if (allFilteredSelected.value) {
        // deselect only the listed options, keep selections that are filtered out
        const remove = new Set(list.map((o) => o.value));
        emit('update:modelValue', selectedOptions.value.filter((o) => !remove.has(o.value)));
    } else {
        const have = new Set(selectedValues.value);
        const added = list
            .filter((o) => !have.has(o.value))
            .map((o) => ({ label: o.label, value: o.value }));
        emit('update:modelValue', [...selectedOptions.value, ...added]);
    }
}

function isSelected(option) {
    return selectedValues.value.includes(option.value);
}

function select(option) {
    if (!option || option.disabled) return;

    const item = { label: option.label, value: option.value };

    if (props.multiple) {
        const next = isSelected(option)
            ? selectedOptions.value.filter((o) => o.value !== option.value)
            : [...selectedOptions.value, item];
        emit('update:modelValue', next);
        if (props.searchable) query.value = '';
    } else {
        emit('update:modelValue', [item]);
        close();
    }
}

function removeValue(value) {
    emit('update:modelValue', selectedOptions.value.filter((o) => o.value !== value));
}

function clear() {
    emit('update:modelValue', []);
}

// ---- open / close ----
function toggle() {
    open.value ? close() : openList();
}

function openList() {
    if (props.disabled) return;
    open.value = true;
    query.value = '';

    const firstSelected = filtered.value.findIndex(isSelected);
    activeIndex.value = firstSelected >= 0 ? firstSelected : firstEnabled(0, 1);

    nextTick(() => {
        searchEl.value?.focus();
        scrollToActive();
    });
}

function close() {
    if (!open.value) return;
    open.value = false;
    trigger.value?.focus();
}

onClickOutside(root, () => {
    open.value = false;
});

// reset highlight when the search text changes
watch(query, () => {
    activeIndex.value = firstEnabled(0, 1);
});

// ---- keyboard ----
function firstEnabled(start, step) {
    const list = filtered.value;
    if (!list.length) return -1;
    let i = start;
    for (let n = 0; n < list.length; n++) {
        const idx = (i + list.length) % list.length;
        if (!list[idx].disabled) return idx;
        i += step;
    }
    return -1;
}

function move(step) {
    if (!filtered.value.length) return;
    activeIndex.value = firstEnabled(activeIndex.value + step, step);
    nextTick(scrollToActive);
}

function scrollToActive() {
    document.getElementById(`${uid}-opt-${activeIndex.value}`)?.scrollIntoView({block: 'nearest'});
}

function onKeydown(e) {
    if (props.disabled) return;

    if (!open.value) {
        if (['ArrowDown', 'ArrowUp', 'Enter', ' '].includes(e.key)) {
            e.preventDefault();
            openList();
        }
        return;
    }

    switch (e.key) {
        case 'ArrowDown':
            e.preventDefault();
            move(1);
            break;
        case 'ArrowUp':
            e.preventDefault();
            move(-1);
            break;
        case 'Enter':
            e.preventDefault();
            select(filtered.value[activeIndex.value]);
            break;
        case 'Escape':
            e.preventDefault();
            e.stopPropagation(); // don't also close a parent modal
            close();
            break;
        case 'Tab':
            open.value = false;
            break;
        case 'Backspace':
            if (props.multiple && !query.value && selectedOptions.value.length) {
                removeValue(selectedOptions.value.at(-1).value);
            }
            break;
    }
}
</script>
