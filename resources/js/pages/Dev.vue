<template>
    <div class="mt-4">

        <div class="mt-4 flex flex-col gap-2">

            <BaseButton class="self-start" :variant="'default'" @click="getUser()" :disabled="getUserPending">
                {{ getUserPending ? 'Fetching...' : 'GET Authenticated' }}
            </BaseButton>

            <div v-if="!getUserPending" class="p-2 border border-dashed border-slate-300 text-sm text-gray-700">
                <div>
                    Data: {{getUserData}}
                </div>
                <div>
                    Error: {{getUserError?.message}}
                </div>
            </div>

            <BaseButton class="self-start" :variant="'default'" @click="postUser()" :disabled="postUserPending">
                {{ postUserPending ? 'Fetching...' : 'POST Authenticated' }}
            </BaseButton>

            <div v-if="!postUserPending" class="p-2 border border-dashed border-slate-300 text-sm text-gray-700">
                <div>
                    Data: {{postUserData}}
                </div>
                <div>
                    Error: {{postUserError?.message}}
                </div>
            </div>
        </div>

        <div class="mt-4 flex gap-2">
            <BaseButton :variant="'clear'" @click="showAlert" :label="'Alert'" />
            <BaseButton :variant="'clear'" @click="showNonMessageAlert" :label="'Non-message Alert'" />

            <BaseButton @click="askDelete" :label="'Confirm'" />

            <BaseButton :variant="'danger'" @click="askName" :label="'Prompt'" />

            <BaseButton :variant="'clear'" @click="editProfile" :label="'Form'" />
        </div>

        <div class="mt-4 p-2 border border-dashed border-slate-300 text-sm text-gray-700">
            useEnv: {{useEnv()}}<br>
            isProduction: {{isProduction}}<br>
            metaEnv: {{metaEnv}}<br>
        </div>
    </div>
</template>

<script setup>

import BaseButton from "@/components/BaseButton.vue";
import PrototypeEditForm from '@/components/PrototypeEditForm.vue';
import { useLaraFetch } from '@/composables/useLaraFetch.js';
import { useAuthStore } from '@/stores/auth';
import { useEnv } from '@/composables/useEnv';
import { useModalStore } from '@/stores/modal';
import {onMounted} from "vue";

const { isProduction } = useEnv();
const metaEnv = import.meta.env;

const modal = useModalStore();
const auth = useAuthStore();

onMounted(() => {
    console.log('Dev mounted');
});

const {
    data: getUserData,
    error: getUserError,
    isFetching: getUserPending,
    statusCode: getUserStatusCode,
    execute: getUser
} = useLaraFetch('/user', { immediate: false }).get().json();

const {
    data: postUserData,
    error: postUserError,
    isFetching: postUserPending,
    statusCode: postUserStatusCode,
    execute: postUser
} = useLaraFetch('/user', { immediate: false }).post().json();

async function showAlert() {

    await modal.alert('Saved', {
        title: 'Success',
        showCloseButton: false,
        closeOnEscape: true,
        dismissible: false
    });
}

async function showNonMessageAlert() {

    await modal.alert(null, {
        title: 'Something Went Wrong',
        subTitle: 'Internal server error',
        showCloseButton: true,
        dismissible: false
    });
}

async function askDelete() {
    const ok = await modal.confirm('This cannot be undone.', {
        title: 'Delete post?',
        okText: 'Delete',
        danger: true,
        closeOnEscape: false,
        closeOnBackdrop: false,
    });

    if (ok) console.log('deleting...');
}

async function askName() {
    const name = await modal.prompt('What should we call it?', {
        title: 'Rename',
        subTitle: 'This is a test prompt',
        defaultValue: 'Untitled',
        validate: (text) => (text.length > 30 ? 'Maximum 30 characters.' : ''),
    });

    if (name !== null) console.log('new name:', name);
}

async function editProfile() {

    const result = await modal.open(PrototypeEditForm, { user: auth.user }, {
        title: 'Edit form',
        subTitle: 'This is a test prompt',
        size: 'lg',
        closeOnEscape: false,
        closeOnBackdrop: false,
    });

    if (result) console.log('saved values:', result);
}
</script>

<style scoped>

</style>
