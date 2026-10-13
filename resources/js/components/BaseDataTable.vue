<script>
// Plain <script> block so the constant can be imported:
// import BaseDataTable, { DATA_SORT } from '@/components/BaseDataTable.vue';
export const DATA_SORT = {
    DEFAULT: 0, // no sort
    ASC: 1,
    DESC: 2,
};
</script>

<script setup>
import { computed, nextTick, onMounted, ref, watch } from 'vue';
import { onClickOutside, useResizeObserver } from '@vueuse/core';

const props = defineProps({
    rows: { type: Array, default: () => [] },
    headers: { type: Array, default: () => [] },

    // v-model: array of selected row ids
    modelValue: { type: Array, default: () => [] },

    // layout
    center: { type: Boolean, default: false },
    landscape: { type: Boolean, default: false },
    striped: { type: Boolean, default: false },
    compact: { type: Boolean, default: false },
    headerTheme: { type: String, default: 'light', validator: (v) => ['light', 'dark'].includes(v) },
    showMaxRows: { type: Number, default: 0 },

    // selection
    selection: { type: Boolean, default: false },
    singleSelect: { type: Boolean, default: false },

    // empty state
    noDataLabel: { type: String, default: 'No record found' },
    showNoData: { type: Boolean, default: false },

    // disabled
    disabled: { type: Boolean, default: false },
    disabledLayer: { type: Boolean, default: false },

    // sorting
    sortable: { type: Boolean, default: false },
    sortHeaders: { type: Object, default: undefined }, // v-model:sort-headers
    sortPending: { type: String, default: null }, // header value currently loading
});

const emit = defineEmits(['update:modelValue', 'update:sortHeaders', 'sort', 'row-click']);

const root = ref(null);
const scrollEl = ref(null);
const headEl = ref(null);
const bodyEl = ref(null);

// ---------------------------------------------------------------- selection
const selectedKeys = computed(() => new Set((props.modelValue ?? []).map(String)));

const isRowSelectable = (row) => row.isSelectable !== false;
const isSelected = (row) => selectedKeys.value.has(String(row.id));

const selectableRows = computed(() => props.rows.filter(isRowSelectable));

const allSelected = computed(
    () => selectableRows.value.length > 0 && selectableRows.value.every(isSelected)
);
const someSelected = computed(() => !allSelected.value && selectableRows.value.some(isSelected));

function toggleAll() {
    const ids = selectableRows.value.map((r) => r.id);
    const current = props.modelValue ?? [];

    if (allSelected.value) {
        const remove = new Set(ids.map(String));
        emit('update:modelValue', current.filter((id) => !remove.has(String(id))));
    } else {
        const have = new Set(current.map(String));
        emit('update:modelValue', [...current, ...ids.filter((id) => !have.has(String(id)))]);
    }
}

function toggleRow(row) {
    if (!isRowSelectable(row)) return;

    if (props.singleSelect) {
        emit('update:modelValue', isSelected(row) ? [] : [row.id]);
        return;
    }

    const current = props.modelValue ?? [];
    const key = String(row.id);
    emit(
        'update:modelValue',
        isSelected(row) ? current.filter((id) => String(id) !== key) : [...current, row.id]
    );
}

// ------------------------------------------------------------------ sorting
const headerIsSortable = computed(
    () => props.sortable && !!props.sortHeaders && Object.keys(props.sortHeaders).length > 0
);

const isSortable = (h) => headerIsSortable.value && h.value in props.sortHeaders;
const directionOf = (h) => props.sortHeaders?.[h.value]?.direction ?? DATA_SORT.DEFAULT;
const isLoading = (h) => props.sortPending != null && props.sortPending === h.value;

function sortFromHeader(h) {
    if (!isSortable(h) || props.sortPending != null) return; // ignore clicks while a sort is loading

    // reset every other field, then cycle the clicked one: default -> asc -> desc -> default
    const next = {};
    for (const [key, item] of Object.entries(props.sortHeaders)) {
        next[key] = { ...item, direction: DATA_SORT.DEFAULT };
    }
    next[h.value].direction = (directionOf(h) + 1) % 3;

    emit('update:sortHeaders', next);
    emit('sort', h.value);
}

const ariaSort = (h) => {
    if (!isSortable(h)) return undefined;
    const d = directionOf(h);
    return d === DATA_SORT.ASC ? 'ascending' : d === DATA_SORT.DESC ? 'descending' : 'none';
};

function iconClass(h) {
    if (props.headerTheme === 'dark') {
        return directionOf(h) === DATA_SORT.DEFAULT && !isLoading(h) ? 'text-white opacity-60' : 'text-white';
    }
    return directionOf(h) === DATA_SORT.DEFAULT && !isLoading(h) ? 'text-gray-400' : 'text-gray-700';
}

// ------------------------------------------------------------------- styles
// Full class names must appear literally so Tailwind can detect them
const textAlign = { left: 'text-left', center: 'text-center', right: 'text-right' };
const justify = { left: 'justify-start', center: 'justify-center', right: 'justify-end' };

// Heights on table cells act as a minimum: a cell with new lines makes its row grow.
const cellPad = computed(() => (props.compact ? 'px-2 py-1' : 'px-4 py-1.5'));
const hdrPx = computed(() => (props.compact ? 'px-2' : 'px-4'));
const rowH = computed(() => (props.compact ? 'h-[34px]' : 'h-[42px]'));
const headH = computed(() => (props.compact ? 'h-[40px]' : 'h-[50px]'));
const headFont = computed(() => (props.compact ? 'text-base' : 'text-lg')); // rows: 14px / 16px

function widthStyle(h) {
    return { minWidth: h.minWidth || undefined, width: h.width || undefined, maxWidth: h.maxWidth || undefined };
}

// ---------------------------------------------------- active (clicked) row
const activeRowId = ref(null);

function onRowClick(row) {
    activeRowId.value = String(row.id);
    emit('row-click', row);
}

onClickOutside(root, () => (activeRowId.value = null));

function rowClass(row, index) {
    if (activeRowId.value === String(row.id)) return ['group', 'bg-gray-200'];
    if (isSelected(row)) return ['group', 'bg-slate-100 hover:bg-gray-200'];
    return ['group', props.striped && index % 2 === 1 ? 'bg-gray-50' : 'bg-white', 'hover:bg-gray-100'];
}

// Row outline for hover and for the clicked row. Drawn as an inset shadow on each cell
// (a real border on <tr> doesn't render with border-separate): top + bottom on every
// cell, plus left on the first cell and right on the last.
const hoverEdge = {
    mid: 'group-hover:shadow-[inset_0_2px_0_0_#94a3b8,inset_0_-2px_0_0_#94a3b8]',
    first: 'group-hover:shadow-[inset_0_2px_0_0_#94a3b8,inset_0_-2px_0_0_#94a3b8,inset_2px_0_0_0_#94a3b8]',
    last: 'group-hover:shadow-[inset_0_2px_0_0_#94a3b8,inset_0_-2px_0_0_#94a3b8,inset_-2px_0_0_0_#94a3b8]',
    only: 'group-hover:shadow-[inset_0_2px_0_0_#94a3b8,inset_0_-2px_0_0_#94a3b8,inset_2px_0_0_0_#94a3b8,inset_-2px_0_0_0_#94a3b8]',
};
const activeEdge = {
    mid: 'shadow-[inset_0_2px_0_0_#64748b,inset_0_-2px_0_0_#64748b]',
    first: 'shadow-[inset_0_2px_0_0_#64748b,inset_0_-2px_0_0_#64748b,inset_2px_0_0_0_#64748b]',
    last: 'shadow-[inset_0_2px_0_0_#64748b,inset_0_-2px_0_0_#64748b,inset_-2px_0_0_0_#64748b]',
    only: 'shadow-[inset_0_2px_0_0_#64748b,inset_0_-2px_0_0_#64748b,inset_2px_0_0_0_#64748b,inset_-2px_0_0_0_#64748b]',
};

function edge(row, cellIndex) {
    const total = colspan.value;
    const pos = total === 1 ? 'only' : cellIndex === 0 ? 'first' : cellIndex === total - 1 ? 'last' : 'mid';
    // The cell's own right and bottom borders take the outline color too, so the
    // outline stays connected across the vertical cell borders.
    return activeRowId.value === String(row.id)
        ? [activeEdge[pos], 'border-r-[#64748b] border-b-[#64748b]']
        : [hoverEdge[pos], 'group-hover:border-r-[#94a3b8] group-hover:border-b-[#94a3b8]'];
}

// --------------------------------------------------------------- max rows
// Measures the real height of the first N rows (rows can be taller when a cell has newlines)
const maxHeight = ref(null);

// Whether the scroll container currently shows a vertical / horizontal scrollbar.
// Used to keep the last column's right border and the last row's bottom border
// when a scrollbar sits next to them.
const hasV = ref(false);
const hasH = ref(false);

function measure() {
    const el = scrollEl.value;
    if (!el) return;

    hasV.value = el.scrollHeight - el.clientHeight > 1;
    hasH.value = el.scrollWidth - el.clientWidth > 1;

    const n = props.showMaxRows;
    if (n <= 0 || props.rows.length <= n) {
        if (maxHeight.value !== null) maxHeight.value = null;
        return;
    }

    const trs = bodyEl.value?.querySelectorAll('tr[data-row]') ?? [];
    let h = headEl.value?.getBoundingClientRect().height ?? 0;
    for (let i = 0; i < Math.min(n, trs.length); i++) h += trs[i].getBoundingClientRect().height;

    const scrollbar = el.offsetHeight - el.clientHeight; // horizontal scrollbar, when present
    const next = Math.ceil(h + scrollbar);
    if (maxHeight.value !== next) maxHeight.value = next;
}

watch(
    () => [props.rows, props.showMaxRows, props.compact, props.headers],
    () => nextTick(measure)
);
onMounted(measure);
useResizeObserver(bodyEl, measure);
useResizeObserver(scrollEl, measure);

const colspan = computed(() => props.headers.length + (props.selection ? 1 : 0));
</script>

<template>
    <div
        ref="root"
        :aria-disabled="disabled"
        :inert="disabled ? '' : undefined"
        :class="[
            'font-data relative overflow-hidden rounded border border-gray-300 bg-white text-gray-800',
            compact ? 'text-sm' : 'text-base',
            landscape ? 'w-full' : 'w-fit max-w-full',
            center && !landscape ? 'mx-auto' : '',
            disabled ? 'pointer-events-none' : '',
        ]"
    >
        <div ref="scrollEl" class="overflow-auto" :style="maxHeight ? { maxHeight: maxHeight + 'px' } : undefined">
            <table :class="['border-separate border-spacing-0', landscape ? 'w-full' : '']">
                <!-- Header -->
                <thead ref="headEl" :class="hasV ? '' : '[&>tr>*:last-child]:border-r-0'">
                <tr>
                    <th
                        v-if="selection"
                        :class="[
                                'sticky top-0 z-10 w-10 min-w-10 max-w-10 border-b border-r p-0',
                                headH,
                                'border-gray-300 bg-white text-gray-700', // checkbox cell stays light in both themes
                            ]"
                    >
                        <!-- no select-all in single mode -->
                        <label
                            v-if="!singleSelect"
                            :class="[
                                    'flex w-10 cursor-pointer items-center justify-center',
                                    headH,
                                    'hover:bg-gray-100',
                                ]"
                        >
                            <input
                                type="checkbox"
                                class="peer sr-only"
                                aria-label="Select all"
                                :checked="allSelected"
                                :disabled="!selectableRows.length"
                                @change="toggleAll"
                            />
                            <span
                                :class="[
                                        'flex size-4 shrink-0 items-center justify-center rounded border',
                                        'peer-focus-visible:ring-2 peer-focus-visible:ring-slate-300',
                                        allSelected || someSelected
                                            ? 'border-slate-700 bg-slate-700 text-white'
                                            : 'border-gray-300 bg-white',
                                    ]"
                            >
                                    <svg v-if="allSelected" class="size-3" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M16.7 5.3a1 1 0 010 1.4l-8 8a1 1 0 01-1.4 0l-4-4a1 1 0 011.4-1.4L8 12.6l7.3-7.3a1 1 0 011.4 0z" clip-rule="evenodd" />
                                    </svg>
                                    <svg v-else-if="someSelected" class="size-3" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path d="M4 9.25h12a.75.75 0 010 1.5H4a.75.75 0 010-1.5z" />
                                    </svg>
                                </span>
                        </label>
                    </th>

                    <th
                        v-for="h in headers"
                        :key="h.value"
                        :aria-sort="ariaSort(h)"
                        :style="widthStyle(h)"
                        :class="[
                                'sticky top-0 z-10 whitespace-nowrap border-b border-r font-semibold',
                                headH,
                                headFont,
                                isSortable(h) ? 'p-0' : hdrPx,
                                isSortable(h) ? (headerTheme === 'dark' ? 'hover:bg-gray-600' : 'hover:bg-gray-100') : '',
                                textAlign[h.alignHeader],
                                headerTheme === 'dark' ? 'border-gray-600 bg-gray-700 text-white' : 'border-gray-300 bg-white text-gray-700',
                            ]"
                    >
                        <button
                            v-if="isSortable(h)"
                            type="button"
                            :class="[
                                    'flex w-full cursor-pointer items-center gap-1.5 font-semibold',
                                    headH,
                                    hdrPx,
                                    justify[h.alignHeader] ?? 'justify-start',
                                    isLoading(h) ? 'cursor-progress' : '',
                                ]"
                            @click="sortFromHeader(h)"
                        >
                            <span>{{ h.text }}</span>

                            <svg
                                v-if="isLoading(h)"
                                :class="['size-4 shrink-0 animate-spin', iconClass(h)]"
                                viewBox="0 0 20 20"
                                fill="none"
                                aria-label="Sorting"
                            >
                                <circle cx="10" cy="10" r="7" stroke="currentColor" stroke-width="2.5" class="opacity-25" />
                                <path d="M17 10a7 7 0 00-7-7" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" />
                            </svg>
                            <svg
                                v-else-if="directionOf(h) === DATA_SORT.ASC"
                                :class="['size-4 shrink-0', iconClass(h)]"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                                aria-hidden="true"
                            >
                                <path fill-rule="evenodd" d="M10 16a.75.75 0 01-.75-.75V5.56L6.28 8.53a.75.75 0 01-1.06-1.06l4.25-4.25a.75.75 0 011.06 0l4.25 4.25a.75.75 0 11-1.06 1.06l-2.97-2.97v9.69A.75.75 0 0110 16z" clip-rule="evenodd" />
                            </svg>
                            <svg
                                v-else-if="directionOf(h) === DATA_SORT.DESC"
                                :class="['size-4 shrink-0', iconClass(h)]"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                                aria-hidden="true"
                            >
                                <path fill-rule="evenodd" d="M10 4a.75.75 0 01.75.75v9.69l2.97-2.97a.75.75 0 111.06 1.06l-4.25 4.25a.75.75 0 01-1.06 0l-4.25-4.25a.75.75 0 111.06-1.06l2.97 2.97V4.75A.75.75 0 0110 4z" clip-rule="evenodd" />
                            </svg>
                            <svg
                                v-else
                                :class="['size-4 shrink-0', iconClass(h)]"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                                aria-hidden="true"
                            >
                                <path fill-rule="evenodd" d="M10 3a.75.75 0 01.53.22l3 3a.75.75 0 11-1.06 1.06L10 4.81 7.53 7.28a.75.75 0 01-1.06-1.06l3-3A.75.75 0 0110 3zm-3.53 9.72a.75.75 0 011.06 0L10 15.19l2.47-2.47a.75.75 0 111.06 1.06l-3 3a.75.75 0 01-1.06 0l-3-3a.75.75 0 010-1.06z" clip-rule="evenodd" />
                            </svg>
                        </button>

                        <span v-else>{{ h.text }}</span>
                    </th>
                </tr>
                </thead>

                <!-- Body -->
                <tbody
                    ref="bodyEl"
                    :class="[
                        hasH ? '' : '[&>tr:last-child>td]:border-b-0',
                        hasV ? '' : '[&>tr>*:last-child]:border-r-0',
                    ]"
                >
                <tr
                    v-for="(row, ri) in rows"
                    :key="String(row.id)"
                    data-row
                    :class="rowClass(row, ri)"
                    @click="onRowClick(row)"
                >
                    <td
                        v-if="selection"
                        :class="['w-10 min-w-10 max-w-10 border-b border-r border-gray-200 p-0', rowH, edge(row, 0)]"
                        @click.stop
                    >
                        <label
                            v-if="isRowSelectable(row)"
                            :class="['flex w-10 cursor-pointer items-center justify-center', rowH]"
                        >
                            <input
                                type="checkbox"
                                class="peer sr-only"
                                :aria-label="`Select row ${row.id}`"
                                :checked="isSelected(row)"
                                @change="toggleRow(row)"
                            />
                            <span
                                :class="[
                                        'flex size-4 shrink-0 items-center justify-center rounded border',
                                        'peer-focus-visible:ring-2 peer-focus-visible:ring-slate-300',
                                        isSelected(row)
                                            ? 'border-slate-700 bg-slate-700 text-white'
                                            : 'border-gray-300 bg-white',
                                    ]"
                            >
                                    <svg v-if="isSelected(row)" class="size-3" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M16.7 5.3a1 1 0 010 1.4l-8 8a1 1 0 01-1.4 0l-4-4a1 1 0 011.4-1.4L8 12.6l7.3-7.3a1 1 0 011.4 0z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                        </label>
                    </td>

                    <td
                        v-for="(h, hi) in headers"
                        :key="h.value"
                        :style="widthStyle(h)"
                        :class="[
                                'whitespace-pre border-b border-r border-gray-200',
                                rowH,
                                cellPad,
                                textAlign[h.alignData],
                                edge(row, (selection ? 1 : 0) + hi),
                            ]"
                    >
                        <slot :name="`cell.${h.value}`" :cell="row" :headerIndex="hi" :rowIndex="ri">{{ row[h.value] }}</slot>
                    </td>
                </tr>

                <tr v-if="!rows.length && showNoData">
                    <td :colspan="colspan" :class="['text-center text-gray-500', cellPad]">{{ noDataLabel }}</td>
                </tr>
                </tbody>
            </table>
        </div>

        <!-- Backdrop while disabled -->
        <div v-if="disabled && disabledLayer" class="absolute inset-0 z-20 bg-white/60"></div>
    </div>
</template>
