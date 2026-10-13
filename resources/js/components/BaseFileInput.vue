<script setup>
import { computed, ref } from 'vue';
import { useDropZone } from '@vueuse/core';

const props = defineProps({
    modelValue: { type: Array, default: () => [] }, // array of File objects (single mode: 0 or 1)
    multiple: { type: Boolean, default: false },
    accept: { type: String, default: '' }, // 'image/*,.pdf'
    maxSize: { type: Number, default: 0 }, // bytes per file, 0 = no limit
    maxFiles: { type: Number, default: 0 }, // multiple mode only, 0 = no limit
    placeholder: { type: String, default: '' },
    disabled: { type: Boolean, default: false },
    error: { type: Boolean, default: false },
});

// change: { action: 'add' | 'remove', files, added, removed }
//   files   = the full list after the change
//   added   = files that were added (empty on remove)
//   removed = files that were removed (on add, only set when single mode replaced a file)
const emit = defineEmits(['update:modelValue', 'reject', 'change']);

const inputEl = ref(null);
const zoneEl = ref(null);
const messages = ref([]);

const files = computed(() => (Array.isArray(props.modelValue) ? props.modelValue : []));

// ---- validation ----
const acceptTokens = computed(() =>
    props.accept
        .split(',')
        .map((t) => t.trim().toLowerCase())
        .filter(Boolean)
);

function matchesAccept(file) {
    if (!acceptTokens.value.length) return true;
    const name = file.name.toLowerCase();
    const type = (file.type || '').toLowerCase();

    return acceptTokens.value.some((t) => {
        if (t.startsWith('.')) return name.endsWith(t);
        if (t.endsWith('/*')) return type.startsWith(t.slice(0, -1));
        return type === t;
    });
}

const sameFile = (a, b) => a.name === b.name && a.size === b.size && a.lastModified === b.lastModified;

function formatSize(bytes) {
    if (bytes < 1024) return `${bytes} B`;
    if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`;
    return `${(bytes / 1024 / 1024).toFixed(1)} MB`;
}

// ---- add / remove ----
function addFiles(list) {
    if (props.disabled) return;

    const incoming = Array.from(list ?? []);
    if (!incoming.length) return;

    const rejected = [];
    const accepted = [];

    for (const f of incoming) {
        if (!matchesAccept(f)) rejected.push({ file: f, reason: 'type' });
        else if (props.maxSize && f.size > props.maxSize) rejected.push({ file: f, reason: 'size' });
        else if ([...files.value, ...accepted].some((x) => sameFile(x, f))) continue; // duplicate: ignore
        else accepted.push(f);
    }

    let next = files.value;

    if (!props.multiple) {
        if (accepted.length) next = [accepted[0]]; // a new file replaces the old one
    } else {
        next = [...files.value, ...accepted];
        if (props.maxFiles && next.length > props.maxFiles) {
            for (const f of next.slice(props.maxFiles)) {
                if (accepted.includes(f)) rejected.push({ file: f, reason: 'count' });
            }
            next = next.slice(0, props.maxFiles);
        }
    }

    messages.value = rejected.map(({ file, reason }) => {
        if (reason === 'type') return `${file.name}: file type not allowed`;
        if (reason === 'size') return `${file.name}: larger than ${formatSize(props.maxSize)}`;
        return `${file.name}: maximum of ${props.maxFiles} files reached`;
    });

    if (next !== files.value) {
        emit('update:modelValue', next);
        emit('change', {
            action: 'add',
            files: next,
            added: accepted.filter((f) => next.includes(f)),
            removed: files.value.filter((f) => !next.includes(f)),
        });
    }
    if (rejected.length) emit('reject', rejected);
}

function removeAt(index) {
    if (props.disabled) return;
    messages.value = [];

    const removed = files.value[index];
    const next = files.value.filter((_, i) => i !== index);

    emit('update:modelValue', next);
    emit('change', { action: 'remove', files: next, added: [], removed: removed ? [removed] : [] });
}

function openDialog() {
    if (!props.disabled) inputEl.value?.click();
}

function onChange(e) {
    addFiles(e.target.files);
    e.target.value = ''; // allows picking the same file again after removing it
}

const { isOverDropZone } = useDropZone(zoneEl, {
    onDrop: (dropped) => addFiles(dropped),
});

const label = computed(
    () => props.placeholder || (props.multiple ? 'Click to upload or drag and drop files' : 'Click to upload or drag and drop a file')
);

const hint = computed(() => {
    const parts = [];
    if (props.accept) parts.push(props.accept.replace(/,\s*/g, ', '));
    if (props.maxSize) parts.push(`max ${formatSize(props.maxSize)}${props.multiple ? ' each' : ''}`);
    if (props.multiple && props.maxFiles) parts.push(`up to ${props.maxFiles} files`);
    return parts.join(' · ');
});
</script>

<template>
    <div>
        <!-- Drop zone -->
        <div
            ref="zoneEl"
            role="button"
            :tabindex="disabled ? -1 : 0"
            :aria-disabled="disabled"
            :class="[
                'flex flex-col items-center justify-center gap-1 rounded border border-dashed px-4 py-5 text-center text-sm',
                'focus:outline-none focus:ring-2',
                error
                    ? 'border-red-500 focus:ring-red-200'
                    : isOverDropZone
                      ? 'border-slate-500 bg-gray-100 focus:ring-slate-300'
                      : 'border-gray-300 bg-white hover:bg-gray-50 focus:border-slate-500 focus:ring-slate-300',
                disabled ? 'cursor-not-allowed opacity-50' : 'cursor-pointer',
            ]"
            @click="openDialog"
            @keydown.enter.prevent="openDialog"
            @keydown.space.prevent="openDialog"
        >
            <svg class="size-6 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path d="M9.25 13.25a.75.75 0 001.5 0V4.636l2.955 3.129a.75.75 0 001.09-1.03l-4.25-4.5a.75.75 0 00-1.09 0l-4.25 4.5a.75.75 0 101.09 1.03L9.25 4.636v8.614z" />
                <path d="M3.5 12.75a.75.75 0 00-1.5 0v2.5A2.75 2.75 0 004.75 18h10.5A2.75 2.75 0 0018 15.25v-2.5a.75.75 0 00-1.5 0v2.5c0 .69-.56 1.25-1.25 1.25H4.75c-.69 0-1.25-.56-1.25-1.25v-2.5z" />
            </svg>
            <span class="text-gray-700">{{ label }}</span>
            <span v-if="hint" class="text-xs text-gray-400">{{ hint }}</span>

            <input
                ref="inputEl"
                type="file"
                class="sr-only"
                tabindex="-1"
                :multiple="multiple"
                :accept="accept || undefined"
                :disabled="disabled"
                @click.stop
                @change="onChange"
            />
        </div>

        <!-- Rejected files -->
        <ul v-if="messages.length" class="mt-1 space-y-0.5" role="alert">
            <li v-for="(m, i) in messages" :key="i" class="text-sm text-red-600">{{ m }}</li>
        </ul>

        <!-- Selected files -->
        <ul v-if="files.length" class="mt-2 space-y-1">
            <li
                v-for="(f, i) in files"
                :key="`${f.name}-${f.size}-${f.lastModified}`"
                class="flex items-center gap-2 rounded border border-gray-300 bg-white px-3 py-1.5 text-sm"
            >
                <svg class="size-4 shrink-0 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path d="M4 2.75A1.75 1.75 0 015.75 1h5.5c.46 0 .9.18 1.23.51l3.01 3.01c.33.33.51.77.51 1.23v10.5A1.75 1.75 0 0114.25 18h-8.5A1.75 1.75 0 014 16.25V2.75z" />
                </svg>

                <span class="min-w-0 flex-1 truncate text-gray-800" :title="f.name">{{ f.name }}</span>
                <span class="shrink-0 text-xs text-gray-500">{{ formatSize(f.size) }}</span>

                <button
                    v-if="!disabled"
                    type="button"
                    :aria-label="`Remove ${f.name}`"
                    class="flex shrink-0 cursor-pointer items-center justify-center text-gray-400 hover:text-gray-700"
                    @click="removeAt(i)"
                >
                    <svg class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 6.28z" />
                    </svg>
                </button>
            </li>
        </ul>
    </div>
</template>
