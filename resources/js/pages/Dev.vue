<template>
    <div class="mt-4">

        <div class="mt-4 p-2 border border-dashed border-slate-300 text-sm text-gray-700">
            useEnv: {{useEnv()}}<br>
            isProduction: {{isProduction}}<br>
            metaEnv: {{metaEnv}}<br>
        </div>

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

        <div class="mt-4 flex flex-wrap gap-2">
            <BaseButton :variant="'clear'" @click="showAlert" :label="'Alert'" />
            <BaseButton :variant="'clear'" @click="showNonMessageAlert" :label="'Non-message Alert'" />

            <BaseButton @click="askDelete" :label="'Confirm'" />

            <BaseButton :variant="'danger'" @click="askName" :label="'Prompt'" />

            <BaseButton :variant="'clear'" @click="editProfile" :label="'Form'" />
        </div>

        <div class="mt-4 flex flex-wrap gap-2">
            <div class="flex-1 flex flex-col gap-2">
                <div>
                    <div class="text-sm text-gray-700">Date only</div>
                    <BaseDatePicker class="self-start" v-model="birthday" clearable />
                </div>
                <div class="p-2 border border-dashed border-slate-300 text-sm text-gray-700">{{birthday}}</div>
            </div>

            <div class="flex-1 flex flex-col gap-2">
                <div>
                    <div class="text-sm text-gray-700">Restricted range, week starts Monday</div>
                    <BaseDatePicker class="self-start" v-model="birthday" min="2026-10-01" max="2026-12-31" :week-start="1" />
                </div>
                <div class="p-2 border border-dashed border-slate-300 text-sm text-gray-700">{{birthday}}</div>
            </div>

            <div class="flex-1 flex flex-col gap-2">
                <div>
                    <div class="text-sm text-gray-700">Error state</div>
                    <BaseDatePicker class="self-start" v-model="birthday" :error="!!errors.birthday" />
                </div>
                <div class="p-2 border border-dashed border-slate-300 text-sm text-gray-700">{{birthday}}</div>
            </div>
        </div>

        <div class="mt-4 flex flex-wrap gap-2">
            <div class="flex-1 flex flex-col gap-2">
                <div>
                    <div class="text-sm text-gray-700">24-hour time with seconds</div>
                    <BaseDatePicker class="self-start" v-model="appointment" mode="datetime" clearable :years-after="10" />
                </div>
                <div class="p-2 border border-dashed border-slate-300 text-sm text-gray-700">{{appointment}}</div>
            </div>

            <div class="flex-1 flex flex-col gap-2">
                <div>
                    <div class="text-sm text-gray-700">Longer history, no future years</div>
                    <BaseDatePicker class="self-start" v-model="historyAppointment" mode="datetime" :years-before="120" :years-after="0" />
                </div>
                <div class="p-2 border border-dashed border-slate-300 text-sm text-gray-700">{{historyAppointment}}</div>
            </div>

            <div class="flex-1 flex flex-col gap-2">
                <div>
                    <div class="text-sm text-gray-700">Min/max override the year range</div>
                    <BaseDatePicker class="self-start" v-model="anotherAppointment" mode="datetime" min="2026-01-01" max="2028-12-31" />
                </div>
                <div class="p-2 border border-dashed border-slate-300 text-sm text-gray-700">{{anotherAppointment}}</div>
            </div>

        </div>

        <div class="mt-4 flex flex-wrap gap-2">

            <div class="flex-1 flex flex-col gap-2">

                <BaseSelect class="self-start" v-model="role" :options="roles" placeholder="Pick a role" clearable />

                <div class="p-2 border border-dashed border-slate-300 text-sm text-gray-700">{{role}}</div>
            </div>

            <div class="flex-1 flex flex-col gap-2">

                <BaseSelect class="self-start min-w-[200px]" v-model="tags" :options="tagOptions" :max-visible="2" multiple searchable clearable />

                <div class="p-2 border border-dashed border-slate-300 text-sm text-gray-700">{{tags}}</div>
            </div>


        </div>

        <div class="mt-4 flex gap-2">

            <BaseRadioGroup class="flex-1" v-model="role" :options="roles" legend="Role" direction="horizontal" />

            <BaseCheckBoxGroup class="flex-1" v-model="tags" :options="tagOptions" legend="Tags" direction="horizontal" />
        </div>

        <div class="font-data mt-4 p-2 border border-dashed border-slate-300 text-sm text-gray-700">

            <h1 class="text-2xl font-semibold mb-4">Typography</h1>

            <p class="mb-4">OAuth 2.1 is an in-progress effort to consolidate and simplify the most commonly used features of OAuth 2.0.</p>
            <p class="mb-4">Since the original publication of OAuth 2.0 (RFC 6749) in 2012, several new RFCs and Internet-Drafts have been published that either add or remove functionality from the core spec. These include <a href="https://oauth.net/2/native-apps/" target="_blank" class="text-blue-600 font-semibold after:content-['_↗'] hover:underline">OAuth 2.0 for Native Apps</a> (RFC 8252), Proof Key for Code Exchange (RFC 7636), OAuth for Browser-Based Apps (RFC 10017), and OAuth 2.0 Security Best Current Practice (RFC 9700).</p>
            <p class="mb-4">OAuth 2.1 consolidates the changes published in later specs to simplify the core document.</p>
            <p class="mb-4">The major differences from OAuth 2.0 are listed below.</p>

            <ul class="list-disc list-inside">
                <li>PKCE is required for all OAuth clients using the authorization code flow</li>
                <li>Redirect URIs must be compared using exact string matching</li>
                <li>The Implicit grant (response_type=token) is omitted from this specification</li>
                <li>The Resource Owner Password Credentials grant is omitted from this specification</li>
                <li>Bearer token usage omits the use of bearer tokens in the query string of URIs</li>
                <li>Refresh tokens for public clients must either be sender-constrained or rotated</li>
                <li>The definitions of public and confidential clients have been simplified to only refer to whether the client has credentials</li>
            </ul>
        </div>
    </div>
</template>

<script setup>

import BaseButton from "@/components/BaseButton.vue";
import BaseSelect from '@/components/BaseSelect.vue';
import PrototypeEditForm from '@/components/PrototypeEditForm.vue';
import { useAuthStore } from '@/stores/auth';
import { useEnv } from '@/composables/useEnv';
import { useLaraFetch } from '@/composables/useLaraFetch.js';
import { useModalStore } from '@/stores/modal';
import {onMounted, ref } from "vue";
import BaseCheckBoxGroup from "@/components/BaseCheckBoxGroup.vue";
import BaseRadioGroup from "@/components/BaseRadioGroup.vue";
import BaseDatePicker from "@/components/BaseDatePicker.vue";

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

const role = ref([{ label: 'Admin', value: '1' }]);
const tags = ref([{ label: 'React', value: 5 }, { label: 'Svelt', value: 6 }]);

// plain values
const roles = [
    { label: 'Admin', value: '1' },
    { label: 'Editor', value: '2' },
    { label: 'Viewer', value: '3' },
];

// objects (custom value/label keys supported via option-label / option-value)
const tagOptions = [
    { label: 'Vue', value: 1 },
    { label: 'Laravel', value: 2 },
    { label: 'Tailwind', value: 3 },
    { label: 'Legacy (unavailable)', value: 4, disabled: true },
    { label: 'React', value: 5 },
    { label: 'Svelt', value: 6 },
];

const errors = ref({
    'birthday': 'Birthday error'
});

const birthday = ref(null);                 // '2026-10-09'
const appointment = ref('2026-10-09 14:30:26'); // '2026-10-09 14:30:26'

const historyAppointment = ref('1908-12-10 15:10:18'); // '1908-12-10 15:10:18'
const anotherAppointment = ref('2026-12-10 15:10:18'); // '2026-12-10 15:10:18'
</script>

<style scoped>

</style>
