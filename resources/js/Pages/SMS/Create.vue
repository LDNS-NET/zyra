<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useToast } from 'vue-toastification';

const toast = useToast();

const props = defineProps({
    renters: Array,
});

const form = useForm({
    recipients: [],
    message: '',
});

const selectAll = ref(false);

const toggleSelectAll = () => {
    if (selectAll.value) {
        form.recipients = props.renters.map((r) => r.id);
    } else {
        form.recipients = [];
    }
};

const submit = () => {
    console.log('SUBMITTING', {
        recipients: form.recipients,
        message: form.message,
    });

    form.post(route('sms.store'), {
        onStart: () => console.log('request started'),
        onSuccess: (page) => {
            console.log('success', page);
            toast.success('SMS queued for sending');
            form.reset();
            selectAll.value = false;
        },
        onError: (errors) => {
            console.log('validation errors', errors);
            toast.error(
                'Failed to send SMS. Please check the form for errors.',
            );
        },
        onFinish: () => console.log('request finished'),
    });
};
</script>

<template>
    <Head title="Send SMS" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight">Send SMS</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <div
                    class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:border dark:border-gray-700 dark:bg-gray-800"
                >
                    <div class="p-6">
                        <form @submit.prevent="submit">
                            <div class="mb-4">
                                <div
                                    class="mb-2 flex items-center justify-between"
                                >
                                    <label
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                                        >Recipients</label
                                    >
                                    <label class="flex items-center">
                                        <input
                                            type="checkbox"
                                            v-model="selectAll"
                                            @change="toggleSelectAll"
                                            class="mr-2 rounded border-gray-300 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:checked:bg-blue-600"
                                        />
                                        <span
                                            class="text-sm text-gray-600 dark:text-gray-400"
                                            >Select All</span
                                        >
                                    </label>
                                </div>
                                <div
                                    class="max-h-64 space-y-2 overflow-y-auto rounded-md border border-gray-300 p-4 text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                >
                                    <label
                                        v-for="renter in renters"
                                        :key="renter.id"
                                        class="flex items-center"
                                    >
                                        <input
                                            type="checkbox"
                                            :value="renter.id"
                                            v-model="form.recipients"
                                            class="mr-2 rounded border-gray-300 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:checked:bg-blue-600"
                                        />
                                        <span class="text-sm">{{ renter.full_name }} ({{ renter.phone }})</span>
                                    </label>
                                    <div
                                        v-if="renters.length === 0"
                                        class="text-sm text-gray-500 dark:text-gray-400"
                                    >
                                        No renters available. Please add renters
                                        first.
                                    </div>
                                </div>
                                <div
                                    v-if="form.errors.recipients"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.recipients }}
                                </div>
                            </div>

                            <div class="mb-4 text-gray-700">
                                <label
                                    for="message"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                                    >Message</label
                                >
                                <textarea
                                    id="message"
                                    v-model="form.message"
                                    rows="6"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200"
                                    placeholder="Type your message here..."
                                    required
                                ></textarea>
                                <div
                                    class="mt-1 text-sm text-gray-500 dark:text-gray-400"
                                >
                                    {{ form.message.length }} / 500 characters
                                </div>
                                <div
                                    v-if="form.errors.message"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.message }}
                                </div>
                            </div>

                            <div class="flex justify-end space-x-3">
                                <a
                                    :href="route('sms.index')"
                                    class="rounded-md bg-gray-200 px-4 py-2 text-gray-700 hover:bg-gray-300 dark:bg-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
                                >
                                    Cancel
                                </a>
                                <button
                                    type="submit"
                                    :disabled="
                                        form.processing ||
                                        form.recipients.length === 0
                                    "
                                    class="rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 disabled:opacity-50"
                                >
                                    Send SMS
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
