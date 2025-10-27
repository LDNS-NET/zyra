<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import Layout from '../Layout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { useToast } from 'vue-toastification';

const toast = useToast();

const props = defineProps({
    gateways: { type: Array, default: () => [] },
    phone_number: { type: String, default: '' },
});

// Use the first gateway record if any
const existing = props.gateways[0] || {};

const form = useForm({
    provider: existing.provider || 'mpesa',
    payout_method: existing.payout_method || 'mpesa_phone',
    collection_method: existing.collection_method || 'phone',
    phone_number: existing.phone_number || props.phone_number || '',
    bank_name: existing.bank_name || '',
    bank_account: existing.bank_account || '',
    till_number: existing.till_number || '',
    paybill_business_number: existing.paybill_business_number || '',
    paybill_account_number: existing.paybill_account_number || '',
});


/**
 * Normalize and submit the payment gateway form
 */
const save = () => {
    switch (form.collection_method) {
        case 'phone':
            form.provider = 'mpesa';
            form.payout_method = 'mpesa_phone';
            break;
        case 'bank':
            form.provider = 'bank';
            form.payout_method = 'bank';
            break;
        case 'mpesa_till':
            form.provider = 'mpesa';
            form.payout_method = 'till';
            break;
        case 'mpesa_paybill':
            form.provider = 'mpesa';
            form.payout_method = 'paybill';
            break;
        default:
            form.provider = 'custom';
            form.payout_method = '';
    }

    form.post(route('settings.payment.update'), {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Payment settings saved successfully.');
        },
        onError: () => {
            toast.error('Failed to save payment settings.');
        },
    });
};
</script>

<template>
    <Layout>
        <Head title="Payment Settings" />

        <section
            class="rounded-xl border border-blue-400 bg-gray-200 p-6 shadow-sm dark:bg-gray-900"
        >
            <header>
                <h2 class="font-extrabold text-blue-700 dark:text-blue-400">
                    Payment Gateway
                </h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Configure how you collect payments from customers.
                </p>
            </header>

            <form @submit.prevent="save" class="mt-6 space-y-6">
                <!-- Collection Method -->
                <div>
                    <InputLabel value="Collection Method" />
                    <select
                        v-model="form.collection_method"
                        class="mt-1 block w-full rounded border-gray-300 dark:bg-gray-800"
                    >
                        <option value="phone">M-Pesa Phone</option>
                        <option value="bank">Bank</option>
                        <option value="mpesa_till">M-Pesa Till</option>
                        <option value="mpesa_paybill">M-Pesa Paybill</option>
                    </select>
                </div>

                <!-- Phone Collection -->
                <div v-if="form.collection_method === 'phone'">
                    <InputLabel value="Phone Number" />
                    <TextInput
                        v-model="form.phone_number"
                        class="mt-1 block w-full"
                        placeholder="e.g., 2547XXXXXXXX"
                    />
                </div>

                <!-- Bank Collection -->
                <div v-if="form.collection_method === 'bank'">
                    <InputLabel value="Bank Name" />
                    <TextInput
                        v-model="form.bank_name"
                        class="mt-1 block w-full"
                        placeholder="Enter bank name"
                    />
                    <InputLabel class="mt-3" value="Bank Account" />
                    <TextInput
                        v-model="form.bank_account"
                        class="mt-1 block w-full"
                        placeholder="Enter account number"
                    />
                </div>

                <!-- M-Pesa Till -->
                <div v-if="form.collection_method === 'mpesa_till'">
                    <InputLabel value="M-Pesa Till Number" />
                    <TextInput
                        v-model="form.till_number"
                        class="mt-1 block w-full"
                        placeholder="Enter till number"
                    />
                </div>

                <!-- M-Pesa Paybill -->
                <div v-if="form.collection_method === 'mpesa_paybill'">
                    <InputLabel value="Paybill Business Number" />
                    <TextInput
                        v-model="form.paybill_business_number"
                        class="mt-1 block w-full"
                        placeholder="Enter business number"
                    />
                    <InputLabel class="mt-3" value="Paybill Account Number" />
                    <TextInput
                        v-model="form.paybill_account_number"
                        class="mt-1 block w-full"
                        placeholder="Enter account number"
                    />
                </div>

                <!-- Footer -->
                <div class="flex items-center justify-between pt-4">
                    <Transition
                        enter-active-class="transition ease-in-out"
                        enter-from-class="opacity-0"
                        leave-active-class="transition ease-in-out"
                        leave-to-class="opacity-0"
                    >
                        <p
                            v-if="form.recentlySuccessful"
                            class="text-sm text-green-600 dark:text-green-400"
                        >
                            Saved successfully.
                        </p>
                    </Transition>

                    <PrimaryButton :disabled="form.processing"
                        >Save</PrimaryButton
                    >
                </div>
            </form>
        </section>
    </Layout>
</template>
