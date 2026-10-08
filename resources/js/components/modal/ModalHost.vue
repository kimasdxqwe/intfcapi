<template>
    <Teleport to="body">
        <TransitionGroup name="modal-fade">
            <ModalItem
                v-for="m in stack"
                :key="m.id"
                :modal="m"
                @close="store.close(m.id, $event)"
                @dismiss="store.dismiss(m.id, $event)"
            />
        </TransitionGroup>
    </Teleport>
</template>

<script setup>
import { watch } from 'vue';
import { storeToRefs } from 'pinia';
import { onKeyStroke, useScrollLock } from '@vueuse/core';
import { useModalStore } from '@/stores/modal';
import ModalItem from '@/components/modal/ModalItem.vue';

const store = useModalStore();
const { stack } = storeToRefs(store);

// Lock page scroll while any modal is open
const locked = useScrollLock(document.body);
watch(() => stack.value.length, (count) => (locked.value = count > 0));

// Escape closes only the top modal
onKeyStroke('Escape', () => {
    const top = stack.value.at(-1);
    if (top) store.dismiss(top.id, 'escape');
});
</script>

<style>
.modal-fade-enter-active,
.modal-fade-leave-active {
    transition: opacity 0.15s ease;
}
.modal-fade-enter-from,
.modal-fade-leave-to {
    opacity: 0;
}
</style>
