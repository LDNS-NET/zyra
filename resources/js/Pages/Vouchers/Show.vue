<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    voucher: Object,
});

// Helper to format date nicely
const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

// Voucher info fields
const voucherInfo = computed(() => [
    { label: 'Code', value: props.voucher.code },
    { label: 'Name', value: props.voucher.name },
    {
        label: 'Value',
        value:
            props.voucher.value +
            (props.voucher.type === 'percentage' ? '%' : ' KES'),
    },
    { label: 'Type', value: props.voucher.type },
    { label: 'Usage Limit', value: props.voucher.usage_limit || 'Unlimited' },
    { label: 'Expires At', value: formatDate(props.voucher.expires_at) },
    { label: 'Active', value: props.voucher.is_active ? 'Yes' : 'No' },
    { label: 'Status', value: props.voucher.status },
]);

// Metadata fields
const metadataFields = computed(() =>
    [
        {
            label: 'Created By',
            value: props.voucher.creator?.name || 'Unknown',
        },
        { label: 'Created At', value: formatDate(props.voucher.created_at) },
        props.voucher.recipient?.name
            ? { label: 'Sent To', value: props.voucher.recipient.name }
            : null,
        props.voucher.sent_at
            ? { label: 'Sent At', value: formatDate(props.voucher.sent_at) }
            : null,
    ].filter(Boolean),
);
</script>

<template>
    <Head :title="`Voucher: ${voucher.code}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800 dark:text-blue-50"
            >
                Voucher Details — {{ voucher.code }}
            </h2>
        </template>

        <div class="px-4 py-10 sm:px-6 lg:px-8">
            <div
                class="mx-auto max-w-5xl rounded-2xl border border-black bg-gray-200 p-6 shadow-sm transition duration-300 hover:shadow-md dark:border-blue-700 dark:bg-gray-800"
            >
                <h3
                    class="mb-6 text-lg font-semibold text-gray-800 dark:text-gray-100"
                >
                    Voucher Information
                </h3>

                <!-- Info Grid -->
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div
                        v-for="(item, index) in voucherInfo"
                        :key="index"
                        class="rounded-xl bg-blue-200 p-4 transition hover:bg-gray-100 dark:bg-gray-700 dark:hover:bg-gray-600"
                    >
                        <p
                            class="text-sm font-medium text-gray-500 dark:text-gray-400"
                        >
                            {{ item.label }}
                        </p>
                        <p
                            class="mt-1 text-base font-semibold text-gray-800 dark:text-gray-100"
                        >
                            {{ item.value }}
                        </p>
                    </div>

                    <!-- Note -->
                    <div
                        class="rounded-xl bg-blue-200 p-4 transition hover:bg-gray-100 sm:col-span-2 dark:bg-blue-900 dark:hover:bg-gray-600"
                    >
                        <p
                            class="text-sm font-medium text-gray-500 dark:text-gray-400"
                        >
                            Note
                        </p>
                        <p
                            class="mt-1 text-base text-gray-800 dark:text-gray-100"
                        >
                            {{ voucher.note || 'N/A' }}
                        </p>
                    </div>

                    <!-- Metadata -->
                    <div
                        v-for="(item, index) in metadataFields"
                        :key="index"
                        class="rounded-xl bg-blue-200 p-4 transition hover:bg-gray-100 dark:bg-gray-700 dark:hover:bg-gray-600"
                    >
                        <p
                            class="text-sm font-medium text-gray-500 dark:text-gray-400"
                        >
                            {{ item.label }}
                        </p>
                        <p
                            class="mt-1 text-base font-semibold text-gray-800 dark:text-gray-100"
                        >
                            {{ item.value }}
                        </p>
                    </div>
                </div>

                <!-- Actions -->
                <div
                    class="mt-8 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end"
                >
                    <Link
                        :href="route('vouchers.index')"
                        class="inline-flex items-center justify-center rounded-xl bg-gray-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-400 dark:bg-gray-700 dark:hover:bg-gray-600"
                    >
                        ← Back to List
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
