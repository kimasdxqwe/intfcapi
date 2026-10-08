<template>
    <form class="space-y-4" @submit.prevent="save">

        <div class="px-5 py-4 space-y-4">
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700" for="name">Name</label>
                <input
                    id="name"
                    ref="nameInput"
                    v-model="form.name"
                    type="text"
                    class="w-full rounded border border-gray-300 px-3 py-1 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-300"
                />
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700" for="email">Email</label>
                <input
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="w-full rounded border border-gray-300 px-3 py-1 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-300"
                />
            </div>
        </div>

        <div class="flex justify-end gap-2 border-t border-gray-200 px-5 py-3">
            <BaseButton :variant="'clear'" @click="emit('close')">
                Cancel
            </BaseButton>
            <BaseButton :type="'submit'">
                Save
            </BaseButton>
        </div>
    </form>
</template>

<script setup>
import { reactive, onMounted, ref } from 'vue';
import BaseButton from "@/components/BaseButton.vue";

const props = defineProps({ user: { type: Object, required: true } });
const emit = defineEmits(['close']);

const form = reactive({ name: props.user.name, email: props.user.email });
const nameInput = ref(null);

onMounted(() => nameInput.value?.focus());

function save() {
    // You could call your API here and only emit on success,
    // so server validation errors can be shown inside the modal.
    emit('close', { ...form });
}
</script>
