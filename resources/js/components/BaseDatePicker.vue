<template>
    <div ref="root" class="relative" @keydown="onKeydown">
        <!-- Trigger -->
        <div
            role="combobox"
            aria-haspopup="dialog"
            :aria-expanded="open"
            :aria-disabled="disabled"
            :tabindex="disabled ? -1 : 0"
            :class="[
                'flex min-h-[2.125rem] w-full items-center gap-2 rounded border bg-white px-3 py-1',
                'focus:outline-none focus:ring-2',
                error ? 'border-red-500 focus:ring-red-200' : 'border-gray-300 focus:border-slate-500 focus:ring-slate-300',
                disabled ? 'cursor-not-allowed opacity-50' : 'cursor-pointer',
            ]"
            @click="toggle"
            @keydown.enter.prevent="toggle"
            @keydown.space.prevent="toggle"
        >
            <svg class="size-4 shrink-0 text-gray-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path
                    fill-rule="evenodd"
                    d="M5.75 2a.75.75 0 01.75.75V4h7V2.75a.75.75 0 011.5 0V4h.25A2.75 2.75 0 0118 6.75v8.5A2.75 2.75 0 0115.25 18H4.75A2.75 2.75 0 012 15.25v-8.5A2.75 2.75 0 014.75 4H5V2.75A.75.75 0 015.75 2zm-1 5.5c-.69 0-1.25.56-1.25 1.25v6.5c0 .69.56 1.25 1.25 1.25h10.5c.69 0 1.25-.56 1.25-1.25v-6.5c0-.69-.56-1.25-1.25-1.25H4.75z"
                    clip-rule="evenodd"
                />
            </svg>

            <span v-if="displayText" class="min-w-0 flex-1 truncate text-gray-900 font-data">{{ displayText }}</span>
            <span v-else class="min-w-0 flex-1 truncate text-gray-400">{{ effectivePlaceholder }}</span>

            <button
                v-if="clearable && modelValue && !disabled"
                type="button"
                aria-label="Clear date"
                class="flex shrink-0 cursor-pointer items-center justify-center text-gray-400 hover:text-gray-700"
                @click.stop="clear"
            >
                <svg class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path
                        d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 6.28z"
                    />
                </svg>
            </button>
        </div>

        <!-- Dropdown -->
        <div
            v-if="open"
            role="dialog"
            aria-label="Choose date"
            class="absolute z-40 mt-1 w-72 rounded border border-gray-300 bg-white p-3 shadow-lg font-data"
        >
            <!-- Month navigation -->
            <div class="mb-2 flex items-center justify-between">
                <button
                    type="button"
                    aria-label="Previous month"
                    class="flex size-7 cursor-pointer items-center justify-center rounded text-gray-900 hover:bg-gray-100 border border-transparent focus:outline-none focus:ring-2 focus:border-slate-500 focus:ring-slate-300"
                    @click="shiftMonth(-1)"
                >
                    <svg class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L9.06 10l3.71 3.71a.75.75 0 11-1.06 1.06l-4.25-4.25a.75.75 0 010-1.06l4.25-4.25a.75.75 0 011.08.02z" clip-rule="evenodd" />
                    </svg>
                </button>

                <div class="flex items-center gap-1">
                    <select
                        v-model.number="viewMonth"
                        aria-label="Month"
                        class="cursor-pointer rounded border border-transparent bg-transparent px-1 py-0.5 text-sm font-semibold text-gray-900 hover:border-gray-300 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-300"
                    >
                        <option v-for="m in monthOptions" :key="m.value" :value="m.value">{{ m.label }}</option>
                    </select>

                    <select
                        v-model.number="viewYear"
                        aria-label="Year"
                        class="cursor-pointer rounded border border-transparent bg-transparent px-1 py-0.5 text-sm font-semibold text-gray-900 hover:border-gray-300 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-300"
                    >
                        <option v-for="y in yearOptions" :key="y" :value="y">{{ y }}</option>
                    </select>
                </div>

                <button
                    type="button"
                    aria-label="Next month"
                    class="flex size-7 cursor-pointer items-center justify-center rounded text-gray-900 hover:bg-gray-100 border border-transparent focus:outline-none focus:ring-2 focus:border-slate-500 focus:ring-slate-300"
                    @click="shiftMonth(1)"
                >
                    <svg class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L10.94 10 7.23 6.29a.75.75 0 111.06-1.06l4.25 4.25a.75.75 0 010 1.06l-4.25 4.25a.75.75 0 01-1.08-.02z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>

            <!-- Weekday header -->
            <div class="mb-1 grid grid-cols-7 text-center text-xs font-medium text-gray-500">
                <span v-for="(w, i) in weekdays" :key="i" class="py-1">{{ w }}</span>
            </div>

            <!-- Days -->
            <div class="grid grid-cols-7 gap-y-0.5">
                <button
                    v-for="day in days"
                    :key="day.iso"
                    type="button"
                    :disabled="day.disabled"
                    :aria-label="day.iso"
                    :aria-pressed="day.isSelected"
                    :class="[
                        'mx-auto flex size-8 items-center justify-center rounded text-sm',
                        day.isSelected
                            ? 'bg-slate-700 font-semibold text-white'
                            : day.disabled
                              ? 'cursor-not-allowed text-gray-300'
                              : day.inMonth
                                ? 'cursor-pointer text-gray-800 hover:bg-gray-100'
                                : 'cursor-pointer text-gray-400 hover:bg-gray-100',
                        day.isToday && !day.isSelected ? 'border border-slate-400' : '',
                    ]"
                    @click="pickDay(day)"
                >
                    {{ day.d }}
                </button>
            </div>

            <!-- Time (24-hour) -->
            <div v-if="isDateTime" class="mt-3 flex items-center justify-center gap-1.5 border-t border-gray-200 pt-3">
                <select :value="hour" :class="selectClass" aria-label="Hour" @change="onHour">
                    <option v-for="h in hourOptions" :key="h" :value="h">{{ pad(h) }}</option>
                </select>
                <span class="text-gray-500">:</span>
                <select :value="minute" :class="selectClass" aria-label="Minute" @change="onMinute">
                    <option v-for="m in minuteOptions" :key="m" :value="m">{{ pad(m) }}</option>
                </select>
                <span class="text-gray-500">:</span>
                <select :value="second" :class="selectClass" aria-label="Second" @change="onSecond">
                    <option v-for="s in secondOptions" :key="s" :value="s">{{ pad(s) }}</option>
                </select>
            </div>

            <!-- Footer -->
            <div class="mt-3 flex items-center justify-between border-t border-gray-200 pt-3">
                <button
                    type="button"
                    :disabled="isOutOfRange(todayIso)"
                    class="cursor-pointer text-sm font-semibold text-slate-700 hover:underline disabled:cursor-not-allowed disabled:opacity-50"
                    @click="pickToday"
                >
                    {{ isDateTime ? 'Now' : 'Today' }}
                </button>

                <button
                    v-if="isDateTime"
                    type="button"
                    class="cursor-pointer rounded border border-gray-300 bg-gray-700 px-3 py-1 text-sm text-white hover:bg-gray-600"
                    @click="close"
                >
                    Done
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue';
import { onClickOutside } from '@vueuse/core';

const props = defineProps({
    modelValue: { type: String, default: null },
    mode: { type: String, default: 'date', validator: (v) => ['date', 'datetime'].includes(v) },
    placeholder: { type: String, default: '' },
    min: { type: String, default: null },
    max: { type: String, default: null },
    clearable: { type: Boolean, default: false },
    disabled: { type: Boolean, default: false },
    error: { type: Boolean, default: false },
    weekStart: { type: Number, default: 0 },
    minuteStep: { type: Number, default: 1 },
    secondStep: { type: Number, default: 1 },
    yearsBefore: { type: Number, default: 100 }, // years listed before the current year
    yearsAfter: { type: Number, default: 20 },   // years listed after the current year
});

const emit = defineEmits(['update:modelValue']);

const root = ref(null);
const open = ref(false);
const viewYear = ref(0);
const viewMonth = ref(0); // 0-11

const isDateTime = computed(() => props.mode === 'datetime');

// ---- parse / format (local time, no timezone conversion) ----
const pad = (n) => String(n).padStart(2, '0');

function parse(str) {
    const m = /^(\d{4})-(\d{2})-(\d{2})(?:[ T](\d{2}):(\d{2})(?::(\d{2}))?)?/.exec(str ?? '');
    if (!m) return null;
    return { y: +m[1], m: +m[2] - 1, d: +m[3], h: +(m[4] ?? 0), mi: +(m[5] ?? 0), s: +(m[6] ?? 0) };
}

function toIsoDate(y, m, d) {
    return `${y}-${pad(m + 1)}-${pad(d)}`;
}

function format({ y, m, d, h, mi, s }) {
    const date = toIsoDate(y, m, d);
    return isDateTime.value ? `${date} ${pad(h)}:${pad(mi)}:${pad(s)}` : date;
}

// ---- selection ----
const selected = computed(() => parse(props.modelValue));

const displayText = computed(() => {
    const s = selected.value;
    if (!s) return '';
    const date = new Date(s.y, s.m, s.d, s.h, s.mi, s.s);
    const dateText = date.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
    if (!isDateTime.value) return dateText;
    const timeText = date.toLocaleTimeString(undefined, {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hourCycle: 'h23',
    });
    return `${dateText}, ${timeText}`;
});

const effectivePlaceholder = computed(
    () => props.placeholder || (isDateTime.value ? 'Select date and time' : 'Select date')
);

// ---- calendar ----
const today = new Date();
const todayIso = toIsoDate(today.getFullYear(), today.getMonth(), today.getDate());

const monthOptions = Array.from({ length: 12 }, (_, i) => ({
    value: i,
    label: new Date(2024, i, 1).toLocaleDateString(undefined, { month: 'long' }),
}));

const yearOptions = computed(() => {
    const thisYear = today.getFullYear();
    let start = props.min ? +props.min.slice(0, 4) : thisYear - props.yearsBefore;
    let end = props.max ? +props.max.slice(0, 4) : thisYear + props.yearsAfter;

    // always include the year being viewed, so the select never shows blank
    start = Math.min(start, viewYear.value);
    end = Math.max(end, viewYear.value);

    return Array.from({ length: end - start + 1 }, (_, i) => start + i);
});

const weekdays = computed(() => {
    // 2024-01-07 is a Sunday
    return Array.from({ length: 7 }, (_, i) =>
        new Date(2024, 0, 7 + ((i + props.weekStart) % 7)).toLocaleDateString(undefined, { weekday: 'short' }).slice(0, 2)
    );
});

function isOutOfRange(iso) {
    return (props.min && iso < props.min) || (props.max && iso > props.max);
}

const days = computed(() => {
    const first = new Date(viewYear.value, viewMonth.value, 1);
    const offset = (first.getDay() - props.weekStart + 7) % 7;
    const selectedIso = selected.value ? toIsoDate(selected.value.y, selected.value.m, selected.value.d) : null;

    return Array.from({ length: 42 }, (_, i) => {
        const date = new Date(viewYear.value, viewMonth.value, 1 - offset + i);
        const iso = toIsoDate(date.getFullYear(), date.getMonth(), date.getDate());
        return {
            iso,
            y: date.getFullYear(),
            m: date.getMonth(),
            d: date.getDate(),
            inMonth: date.getMonth() === viewMonth.value,
            disabled: !!isOutOfRange(iso),
            isToday: iso === todayIso,
            isSelected: iso === selectedIso,
        };
    });
});

function shiftMonth(delta) {
    const d = new Date(viewYear.value, viewMonth.value + delta, 1);
    viewYear.value = d.getFullYear();
    viewMonth.value = d.getMonth();
}

function syncView() {
    const base = selected.value ?? { y: today.getFullYear(), m: today.getMonth() };
    viewYear.value = base.y;
    viewMonth.value = base.m;
}

function pickDay(day) {
    if (day.disabled) return;
    const keep = selected.value ?? { h: 0, mi: 0, s: 0 };
    emit('update:modelValue', format({ y: day.y, m: day.m, d: day.d, h: keep.h, mi: keep.mi, s: keep.s }));

    if (!isDateTime.value) close();
    else if (!day.inMonth) syncViewTo(day);
}

function syncViewTo(day) {
    viewYear.value = day.y;
    viewMonth.value = day.m;
}

function pickToday() {
    if (isOutOfRange(todayIso)) return;
    const now = new Date();
    emit(
        'update:modelValue',
        format({
            y: now.getFullYear(), m: now.getMonth(), d: now.getDate(),
            h: isDateTime.value ? now.getHours() : 0,
            mi: isDateTime.value ? now.getMinutes() : 0,
            s: isDateTime.value ? now.getSeconds() : 0,
        })
    );
    syncView();
    if (!isDateTime.value) close();
}

function clear() {
    emit('update:modelValue', null);
}

// ---- time (datetime mode, 24-hour) ----
const hour = computed(() => selected.value?.h ?? 0);
const minute = computed(() => selected.value?.mi ?? 0);
const second = computed(() => selected.value?.s ?? 0);

const hourOptions = Array.from({ length: 24 }, (_, i) => i);

const minuteOptions = computed(() => {
    const step = Math.max(1, props.minuteStep);
    return Array.from({ length: Math.ceil(60 / step) }, (_, i) => i * step);
});

const secondOptions = computed(() => {
    const step = Math.max(1, props.secondStep);
    return Array.from({ length: Math.ceil(60 / step) }, (_, i) => i * step);
});

function setTime(h, mi, s) {
    // With no date chosen yet, time edits fall back to today
    const base = selected.value ?? { y: today.getFullYear(), m: today.getMonth(), d: today.getDate() };
    emit('update:modelValue', format({ y: base.y, m: base.m, d: base.d, h, mi, s }));
}

const onHour = (e) => setTime(+e.target.value, minute.value, second.value);
const onMinute = (e) => setTime(hour.value, +e.target.value, second.value);
const onSecond = (e) => setTime(hour.value, minute.value, +e.target.value);

// ---- open / close ----
function toggle() {
    if (props.disabled) return;
    if (open.value) close();
    else {
        syncView();
        open.value = true;
    }
}

function close() {
    open.value = false;
}

onClickOutside(root, () => (open.value = false));

watch(() => props.disabled, (d) => d && close());

function onKeydown(e) {
    if (e.key === 'Escape' && open.value) {
        e.preventDefault();
        e.stopPropagation(); // don't also dismiss a parent modal
        close();
    }
}

const selectClass =
    'rounded border border-gray-300 bg-white px-2 py-1 text-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-300';
</script>
