<template>
    <div
        class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/50 p-4"
        @mousedown.self="onBackdrop"
    >
        <div
            role="dialog"
            aria-modal="true"
            :aria-labelledby="`modal-title-${modal.id}`"
            :class="['w-full rounded-md bg-white shadow-xl', sizeClass, modal.shake && 'modal-shake']"
        >
            <!-- Header -->
            <div class="flex items-start justify-between border-b border-gray-200 px-5 py-4">
                <div>
                    <h2 :id="`modal-title-${modal.id}`" class="text-lg font-semibold text-gray-900">
                        {{ modal.title }}
                    </h2>
                    <h2 v-if="modal.subTitle" :id="`modal-sub-title-${modal.id}`" class="text-sm text-gray-500">
                        {{ modal.subTitle }}
                    </h2>
                </div>
                <button
                    v-if="showClose"
                    type="button"
                    aria-label="Close"
                    class="-mr-1 cursor-pointer rounded px-2 text-2xl leading-none text-gray-400 hover:text-gray-700"
                    @click="emit('dismiss', 'button')"
                >
                    &times;
                </button>
            </div>

            <!-- Custom component (forms etc.) -->
            <div v-if="modal.type === 'custom'">
                <component :is="modal.component" v-bind="modal.props" @close="emit('close', $event)" />
            </div>

            <!-- Prompt -->
            <form v-else-if="modal.type === 'prompt'" @submit.prevent="submitPrompt">
                <div class="px-5 py-4">
                    <label :for="`modal-input-${modal.id}`" class="mb-2 block whitespace-pre-line text-gray-600">
                        {{ modal.message }}
                    </label>
                    <input
                        :id="`modal-input-${modal.id}`"
                        ref="inputEl"
                        v-model="value"
                        :type="modal.inputType ?? 'text'"
                        :placeholder="modal.placeholder"
                        :class="[
                            'w-full rounded border px-3 py-2 text-gray-900 shadow-sm focus:outline-none focus:ring-2',
                            error ? 'border-red-500 focus:ring-red-200' : 'border-gray-300 focus:border-slate-500 focus:ring-slate-300',
                        ]"
                        @input="error = ''"
                    />
                    <p v-if="error" class="mt-1 text-sm text-red-600">{{ error }}</p>
                </div>
                <div class="flex justify-end gap-2 border-t border-gray-200 px-5 py-3">
                    <BaseButton :variant="'clear'" @click="emit('close', modal.dismissValue)">
                        {{ modal.cancelText }}
                    </BaseButton>
                    <BaseButton :type="'submit'" :variant="modal.danger ? 'danger' : 'default'">
                        {{ modal.okText }}
                    </BaseButton>
                </div>
            </form>

            <!-- Alert / Confirm -->
            <template v-else>
                <div v-if="modal.message" class="border-b border-gray-200">
                    <p class="whitespace-pre-line px-5 py-4 text-gray-600">{{ modal.message }}</p>
                </div>
                <div class="flex justify-end gap-2 px-5 py-3">
                    <BaseButton
                        v-if="modal.type === 'confirm'"
                        ref="cancelBtn"
                        :variant="'clear'"
                        @click="emit('close', false)">
                        {{ modal.cancelText }}
                    </BaseButton>

                    <BaseButton ref="okBtn" :variant="modal.danger ? 'danger' : 'default'" @click="emit('close', true)">
                        {{ modal.okText }}
                    </BaseButton>
                </div>
            </template>
        </div>
    </div>
</template>

<script setup>
import { computed, nextTick, onMounted, ref } from 'vue';
import {useModalStore} from "@/stores/modal";
import BaseButton from "@/components/BaseButton.vue";

const store = useModalStore();
const props = defineProps({ modal: { type: Object, required: true } });
const emit = defineEmits(['close', 'dismiss']);

const value = ref(props.modal.defaultValue ?? '');
const error = ref('');
const inputEl = ref(null);
const okBtn = ref(null);
const cancelBtn = ref(null);

const sizeClass = computed(() => ({ sm: 'max-w-sm', md: 'max-w-md', lg: 'max-w-2xl' }[props.modal.size] ?? 'max-w-md'));

const showClose = computed(() => store.canDismiss(props.modal, 'button'));

function onBackdrop() {
    emit('dismiss', 'backdrop');
}

function submitPrompt() {
    const text = value.value;

    if (props.modal.required !== false && !text.trim()) {
        error.value = 'This field is required.';
        return;
    }

    const message = props.modal.validate?.(text);
    if (message) {
        error.value = message;
        return;
    }

    emit('close', text);
}

onMounted(() => {
    nextTick(() => {
        if (inputEl.value) {
            inputEl.value.focus();
            inputEl.value.select();
        } else if (props.modal.danger) {
            cancelBtn.value?.focus();
        } else {
            okBtn.value?.focus();
        }
    });
});
</script>

<style>
@keyframes modal-shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-6px); }
    75% { transform: translateX(6px); }
}
.modal-shake {
    animation: modal-shake 0.2s ease;
}
</style>
