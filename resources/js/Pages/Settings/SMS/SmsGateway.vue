<script setup>
import { ref, watch, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import { router, useForm } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Layout from '@/Pages/Settings/Layout.vue';

const toast = useToast();
const toastOptions = {
    position: 'top-right',
    timeout: 4000,
    closeOnClick: true,
    pauseOnHover: true,
    maxToasts: 1,
};

const props = defineProps({
    gateways: { type: Array, default: () => [] },
});

const showModal = ref(false);
const showDetailsModal = ref(false);
const editing = ref(false);
const detailsGateway = ref({});

const defaultGateway = {
    id: 'default-talksasa',
    provider: 'talksasa',
    label: 'TALKSASA (Default)',
    is_active: true,
    username: '',
    sender_id: '',
    webhook_url: '',
    status_callback_url: '',
    region: '',
    custom_parameters: {},
    api_key: '',
    api_secret: '',
    is_default: true,
};

const form = useForm({
    id: '',
    provider: '',
    api_key: '',
    api_secret: '',
    username: '',
    sender_id: '',
    webhook_url: '',
    status_callback_url: '',
    region: '',
    custom_parameters: {},
    label: '',
    is_active: false,
});

// --- Utility: Fill form fields from a gateway ---
function setFormFromGateway(gateway) {
    Object.assign(form, {
        id: gateway.id || '',
        provider: gateway.provider || '',
        api_key: gateway.api_key || '',
        api_secret: gateway.api_secret || '',
        username: gateway.username || '',
        sender_id: gateway.sender_id || '',
        webhook_url: gateway.webhook_url || '',
        status_callback_url: gateway.status_callback_url || '',
        region: gateway.region || '',
        custom_parameters: gateway.custom_parameters || {},
        label: gateway.label || '',
        is_active: !!gateway.is_active,
    });
}

// --- Lifecycle ---
onMounted(() => {
    const gateway = props.gateways[0] || defaultGateway;
    setFormFromGateway(gateway);
});

// --- Watchers ---
watch(
    () => form.provider,
    (provider) => {
        const gw = props.gateways.find((g) => g.provider === provider);
        if (gw) setFormFromGateway(gw);
    },
);

// --- Actions ---
async function openDetails() {
    try {
        const response = await fetch('settings/sms-gateway/json', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        });
        const data = await response.json();
        detailsGateway.value = data.props?.gateways?.[0] || defaultGateway;
    } catch {
        detailsGateway.value = props.gateways[0] || defaultGateway;
    }
    showDetailsModal.value = true;
}

function openAdd() {
    editing.value = false;
    form.reset();
    showModal.value = true;
}

function openEdit(gateway) {
    editing.value = true;
    form.reset();
    setFormFromGateway(gateway);
    form.api_key = '';
    form.api_secret = '';
    showModal.value = true;
}

function save() {
    const providerLabel = {
        talksasa: 'TALKSASA',
        africastalking: "Africa's Talking",
        twilio: 'Twilio',
        custom: 'Custom',
    };
    const providerName = providerLabel[form.provider] || form.provider;

    const loadingToastId = toast.info(
        `Saving SMS gateway: ${providerName}...`,
        toastOptions,
    );

    const successHandler = () => {
        showModal.value = false;
        toast.dismiss(loadingToastId);
        toast.success(
            `${editing.value ? 'Updated' : 'Added'} SMS gateway: ${providerName}`,
            toastOptions,
        );
    };

    const errorHandler = (errors) => {
        toast.dismiss(loadingToastId);
        toast.error(
            Object.values(errors).flat().join(' ') || 'Failed to save gateway.',
            {
                ...toastOptions,
                timeout: 7000,
            },
        );
    };

    const method = editing.value ? router.put : router.post;
    method(route('settings.sms.update'), form, {
        onSuccess: successHandler,
        onError: errorHandler,
    });
}
</script>

<template>
    <Layout>
        <Head title="SMS Gateway" />
        <div
            class="w-full max-w-lg rounded-xl border border-indigo-100 bg-white p-8 shadow-lg dark:border-blue-700 dark:bg-gray-800"
        >
            <header class="mb-6 flex items-center">
                <svg
                    class="mr-3 h-8 w-8 text-indigo-500"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M17 8h2a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2v-8a2 2 0 012-2h2"
                    />
                    <rect width="12" height="8" x="6" y="4" rx="2" />
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M6 12h.01M6 16h.01"
                    />
                </svg>
                <h3 class="text-2xl font-bold text-indigo-700">
                    SMS Gateway Settings
                </h3>
            </header>

            <label class="mb-2 block font-semibold text-indigo-600"
                >Select SMS Gateway</label
            >
            <select
                v-model="form.provider"
                class="input input-bordered mb-6 w-full focus:ring-2 focus:ring-indigo-400 dark:bg-gray-700"
            >
                <option value="talksasa">TALKSASA (Default)</option>
                <option value="bytewave">Bytewave</option>
                <option value="africastalking">Africa's Talking</option>
                <option value="textsms">TextSMS</option>
                <option value="mobitech">Mobitech</option>
                <option value="twilio">Twilio</option>
                <option value="custom">Custom</option>
            </select>

            <!-- Dynamic Input Fields -->
            <transition name="fade">
                <div v-if="form.provider" class="space-y-4">
                    <template
                        v-if="['talksasa', 'bytewave'].includes(form.provider)"
                    >
                        <InputField
                            label="API Key"
                            v-model="form.api_key"
                            type="password"
                        />
                        <InputField
                            label="Sender ID"
                            v-model="form.sender_id"
                        />
                    </template>

                    <template
                        v-else-if="
                            ['africastalking', 'textsms', 'mobitech'].includes(
                                form.provider,
                            )
                        "
                    >
                        <InputField label="Username" v-model="form.username" />
                        <InputField
                            label="API Key"
                            v-model="form.api_key"
                            type="password"
                        />
                        <InputField
                            label="Sender ID"
                            v-model="form.sender_id"
                        />
                    </template>

                    <template v-else>
                        <InputField
                            label="API Key"
                            v-model="form.api_key"
                            type="password"
                        />
                        <InputField
                            label="API Secret"
                            v-model="form.api_secret"
                            type="password"
                        />
                    </template>

                    <div class="mt-6 flex justify-between">
                        <PrimaryButton
                            class="btn btn-outline btn-info"
                            @click="openDetails"
                        >
                            Show Details
                        </PrimaryButton>
                        <PrimaryButton
                            class="btn btn-indigo btn-lg shadow hover:scale-105"
                            @click="save"
                        >
                            Save Gateway
                        </PrimaryButton>
                    </div>

                    <!-- Modal: Gateway Details -->
                    <Modal
                        :show="showDetailsModal"
                        @close="showDetailsModal = false"
                    >
                        <template #header>
                            <h3 class="text-xl font-bold text-indigo-700">
                                Current SMS Gateway Details
                            </h3>
                        </template>

                        <div
                            class="space-y-3 rounded-lg bg-gradient-to-br from-blue-50 to-indigo-100 p-6 shadow"
                        >
                            <Detail
                                label="Provider"
                                :value="detailsGateway.provider?.toUpperCase()"
                            />
                            <Detail
                                label="Label"
                                :value="detailsGateway.label"
                            />
                            <Detail
                                label="Username"
                                :value="detailsGateway.username"
                            />
                            <Detail
                                label="API Key"
                                :value="detailsGateway.api_key"
                            />
                            <Detail
                                label="Sender ID"
                                :value="detailsGateway.sender_id"
                            />
                            <Detail
                                label="API Secret"
                                :value="detailsGateway.api_secret"
                            />
                            <Detail
                                label="Active"
                                :value="
                                    detailsGateway.is_active
                                        ? 'Active'
                                        : 'Inactive'
                                "
                            />
                        </div>

                        <template #footer>
                            <button
                                class="btn btn-outline btn-lg"
                                @click="showDetailsModal = false"
                            >
                                Close
                            </button>
                        </template>
                    </Modal>
                </div>
            </transition>
        </div>
    </Layout>
</template>

<!-- Simple Reusable Inputs -->
<script>
export default {
    components: {
        InputField: {
            props: ['label', 'modelValue', 'type'],
            emits: ['update:modelValue'],
            template: `
              <div>
                <label class="block font-semibold text-gray-700">{{ label }}</label>
                <input
                    :type="type || 'text'"
                    :value="modelValue"
                    @input="$emit('update:modelValue', $event.target.value)"
                    class="input input-bordered w-full dark:bg-gray-700"
                />
              </div>
            `,
        },
        Detail: {
            props: ['label', 'value'],
            template: `
              <div v-if="value" class="flex items-center gap-2">
                <span class="font-semibold text-gray-700">{{ label }}:</span>
                <span class="rounded bg-indigo-100 px-2 py-1 text-sm text-indigo-700">{{ value }}</span>
              </div>
            `,
        },
    },
};
</script>
