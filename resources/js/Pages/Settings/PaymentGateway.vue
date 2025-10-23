<script setup>
import { ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
const props = defineProps({
    gateways: { type: Array, default: () => [] },
    phone_number: { type: String, default: '' },
});

const initial =
    props.gateways && props.gateways.length > 0 ? props.gateways[0] : {};

const form = useForm({
    collection_method: initial.collection_method || 'phone',
    phone_number: initial.phone_number || props.phone_number || '',
    bank_name: initial.bank_name || '',
    bank_account: initial.bank_account || '',
    till_number: initial.till_number || '',
    paybill_business_number: initial.paybill_business_number || '',
    paybill_account_number: initial.paybill_account_number || '',
});

const save = () => {
    // Set provider and payout_method based on collection_method
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
    form.post(route('payment_gateway.update'), {
        preserveScroll: true,
    });
};
function readableMethod(gateway) {
    if (!gateway) return '';
    switch (gateway.payout_method) {
        case 'mpesa_phone':
            return 'Mpesa Phone Number';
        case 'bank':
            return 'Bank';
        case 'till':
            return 'Mpesa Till';
        case 'paybill':
            return 'Mpesa Paybill';
        default:
            return (
                (gateway.provider || '').charAt(0).toUpperCase() +
                (gateway.provider || '').slice(1)
            );
    }
}
</script>

<template>
    <div
        class="mx-auto max-w-2xl rounded-xl border border-blue-400 bg-gray-200 p-6 shadow dark:bg-gray-800"
    >
        <h3 class="font-bol mb-4 text-xl text-blue-700 dark:text-blue-300">
            Tenant Payment Collection Details
        </h3>
        <div
            v-if="props.gateways && props.gateways.length > 0"
            class="mb-6 rounded border border-gray-200 bg-gray-50 p-4 dark:bg-gray-900"
        >
            <div class="mb-2 font-semibold">Current Saved Gateway:</div>
            <div>
                <span class="font-medium">Method:</span>
                {{ readableMethod(props.gateways[0]) }}
            </div>
            <div v-if="props.gateways[0].phone_number">
                <span class="font-medium">Phone Number:</span>
                {{ props.gateways[0].phone_number }}
            </div>
            <div v-if="props.gateways[0].bank_name">
                <span class="font-medium">Bank:</span>
                {{ props.gateways[0].bank_name }}
            </div>
            <div v-if="props.gateways[0].bank_account">
                <span class="font-medium">Bank Account:</span>
                {{ props.gateways[0].bank_account }}
            </div>
            <div v-if="props.gateways[0].till_number">
                <span class="font-medium">Mpesa Till:</span>
                {{ props.gateways[0].till_number }}
            </div>
            <div v-if="props.gateways[0].paybill_business_number">
                <span class="font-medium">Paybill Business #:</span>
                {{ props.gateways[0].paybill_business_number }}
            </div>
            <div v-if="props.gateways[0].paybill_account_number">
                <span class="font-medium">Paybill Account #:</span>
                {{ props.gateways[0].paybill_account_number }}
            </div>
        </div>
        <form @submit.prevent="save">
            <div class="mb-4">
                <label class="mb-1 block font-medium">Collection Method</label>
                <select
                    v-model="form.collection_method"
                    class="input input-bordered w-full dark:bg-gray-700"
                >
                    <option value="phone">Phone Number</option>
                    <option value="bank">Bank</option>
                    <option value="mpesa_till">Mpesa Till</option>
                    <option value="mpesa_paybill">Mpesa Paybill</option>
                </select>
            </div>
            <div v-if="form.collection_method === 'phone'" class="mb-4">
                <label class="mb-1 block font-medium">Phone Number</label>
                <input
                    v-model="form.phone_number"
                    class="input input-bordered w-full dark:bg-gray-700"
                />
            </div>
            <div v-if="form.collection_method === 'bank'" class="mb-4">
                <label class="mb-1 block font-medium">Bank Name</label>
                <select
                    v-model="form.bank_name"
                    class="input input-bordered w-full dark:bg-gray-700"
                >
                    <option value="">Select Bank</option>
                    <option value="equity">Equity</option>
                    <option value="cooperative">Cooperative</option>
                    <option value="kcb">KCB</option>
                </select>
                <label class="mb-1 mt-2 block font-medium">Bank Account</label>
                <input
                    v-model="form.bank_account"
                    class="input input-bordered w-full dark:bg-gray-700"
                />
            </div>
            <div v-if="form.collection_method === 'mpesa_till'" class="mb-4">
                <label class="mb-1 block font-medium">Mpesa Till Number</label>
                <input
                    v-model="form.till_number"
                    class="input input-bordered w-full dark:bg-gray-700"
                />
            </div>
            <div v-if="form.collection_method === 'mpesa_paybill'" class="mb-4">
                <label class="mb-1 block font-medium"
                    >Paybill Business Number</label
                >
                <input
                    v-model="form.paybill_business_number"
                    class="input input-bordered w-full dark:bg-gray-700"
                />
                <label class="mb-1 mt-2 block font-medium"
                    >Paybill Account Number</label
                >
                <input
                    v-model="form.paybill_account_number"
                    class="input input-bordered w-full dark:bg-gray-700"
                />
            </div>
            <div class="mt-6 flex justify-end">
                <PrimaryButton class="btn btn-primary" type="submit">Save</PrimaryButton>
            </div>
        </form>
    </div>
</template>
