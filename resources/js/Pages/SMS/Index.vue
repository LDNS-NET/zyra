<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Eye, Trash} from 'lucide-vue-next';
import { useToast } from 'vue-toastification';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Pagination from '@/Components/Pagination.vue';

const toast = useToast();

defineProps({
    smsLogs: Object,
    perPage: Number,
});

const editing = ref(null);
const showModal = ref(false);
const viewing = ref(null);

const form = useForm({
    recipient_name: '',
    phone_number: '',
    message: '',
    status: 'pending',
    sent_at: '',
});

function openEdit(sms) {
    editing.value = sms;
    form.recipient_name = sms.recipient_name;
    form.phone_number = sms.phone_number;
    form.message = sms.message;
    form.status = sms.status;
    form.sent_at = sms.sent_at;
    showModal.value = true;
}

function saveEdit() {
    form.put(route('sms.update', editing.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('SMS log updated successfully');
            showModal.value = false;
            editing.value = null;
        },
        onError: () => toast.error('Failed to update SMS log'),
    });
}

function remove(id) {
    if (confirm('Are you sure you want to delete this SMS log?')) {
        router.delete(route('sms.destroy', id), {
            onSuccess: () => toast.success('SMS log deleted successfully'),
            onError: () => toast.error('Failed to delete SMS log'),
        });
    }
}

function view(sms) {
    viewing.value = sms;
}
</script>

<template>
    <Head title="SMS Logs" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight">SMS Logs</h2>
                <Link
                    :href="route('sms.create')"
                    class="rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700"
                >
                    Send SMS
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div
                    class="overflow-hidden border bg-white shadow-sm sm:rounded-lg dark:border dark:border-gray-700 dark:bg-gray-800"
                >
                    <div
                        class="border border-blue-400 p-6 dark:border-x-blue-400"
                    >
                        <div class="overflow-x-auto">
                            <table
                                class="min-w-full divide-y border dark:divide-gray-700 dark:border-gray-700"
                            >
                                <thead class="dark:bg-gray-700">
                                    <tr>
                                        <th
                                            class="border-b border-r px-6 py-3 text-left text-xs font-medium uppercase tracking-wider dark:border-gray-600 dark:text-gray-300"
                                        >
                                            Recipient
                                        </th>
                                        <th
                                            class="border-b border-r px-6 py-3 text-left text-xs font-medium uppercase tracking-wider dark:border-gray-600 dark:text-gray-300"
                                        >
                                            Phone
                                        </th>
                                        <th
                                            class="border-b border-r px-6 py-3 text-left text-xs font-medium uppercase tracking-wider dark:border-gray-600 dark:text-gray-300"
                                        >
                                            Message
                                        </th>
                                        <th
                                            class="border-b border-r px-6 py-3 text-left text-xs font-medium uppercase tracking-wider dark:border-gray-600 dark:text-gray-300"
                                        >
                                            Status
                                        </th>
                                        <th
                                            class="border-b border-r px-6 py-3 text-left text-xs font-medium uppercase tracking-wider dark:border-gray-600 dark:text-gray-300"
                                        >
                                            Sent At
                                        </th>
                                        <th
                                            class="border-b px-6 py-3 text-left text-xs font-medium uppercase tracking-wider dark:border-gray-600 dark:text-gray-300"
                                        >
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody
                                    class="divide-y dark:divide-gray-700 dark:bg-gray-800"
                                >
                                    <tr
                                        v-for="sms in smsLogs.data"
                                        :key="sms.id"
                                    >
                                        <td
                                            class="whitespace-nowrap border-r px-6 py-4 dark:border-gray-700 dark:text-gray-300"
                                        >
                                            {{ sms.recipient_name }}
                                        </td>
                                        <td
                                            class="whitespace-nowrap border-r px-6 py-4 dark:border-gray-700 dark:text-gray-300"
                                        >
                                            <div
                                                class="max-w-xs truncate"
                                                :title="sms.phone_number"
                                            >
                                                {{ sms.phone_number }}
                                            </div>
                                        </td>
                                        <td
                                            class="border-r px-6 py-4 dark:border-gray-700 dark:text-gray-300"
                                        >
                                            <div
                                                class="max-w-xs truncate"
                                                :title="sms.message"
                                            >
                                                {{ sms.message }}
                                            </div>
                                        </td>
                                        <td
                                            class="whitespace-nowrap border-r px-6 py-4 dark:border-gray-700"
                                        >
                                            <span
                                                :class="{
                                                    'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300':
                                                        sms.status === 'sent',
                                                    'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300':
                                                        sms.status ===
                                                        'pending',
                                                    'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300':
                                                        sms.status === 'failed',
                                                }"
                                                class="rounded-full px-2 py-1 text-xs font-semibold"
                                                :title="
                                                    sms.status === 'failed'
                                                        ? sms.error_message
                                                        : ''
                                                "
                                            >
                                                {{ sms.status }}
                                            </span>
                                        </td>
                                        <td
                                            class="whitespace-nowrap border-r px-6 py-4 dark:border-gray-700 dark:text-gray-300"
                                        >
                                            {{
                                                sms.sent_at
                                                    ? new Date(
                                                          sms.sent_at,
                                                      ).toLocaleString()
                                                    : 'N/A'
                                            }}
                                        </td>
                                        <td
                                            class="flex gap-2 whitespace-nowrap px-6 py-4"
                                        >
                                            <button
                                                @click="view(sms)"
                                                class="text-blue-400 hover:text-blue-300"
                                                title="View"
                                            >
                                                <Eye class="h-5 w-5" />
                                            </button>

                                            <button
                                                @click="remove(sms.id)"
                                                class="text-red-400 hover:text-red-300"
                                                title="Delete"
                                            >
                                                <Trash class="h-5 w-5" />
                                            </button>
                                        </td>
                                    </tr>

                                    <tr v-if="smsLogs.data.length === 0">
                                        <td
                                            colspan="6"
                                            class="border-r px-6 py-4 text-center dark:border-gray-700 dark:text-gray-400"
                                        >
                                            No SMS logs found
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4">
                            <Pagination
                                :links="smsLogs.links"
                                :per-page="perPage"
                            />
                        </div>

                        <!-- View Modal -->
                        <Modal :show="!!viewing" @close="viewing = null">
                            <div
                                class="p-4 dark:bg-gray-800 dark:text-gray-100"
                                v-if="viewing"
                            >
                                <h3 class="mb-4 text-xl font-bold">
                                    SMS Details
                                </h3>
                                <p>
                                    <strong>Recipient:</strong>
                                    {{ viewing.recipient_name }}
                                </p>
                                <p>
                                    <strong>Phone:</strong>
                                    {{ viewing.phone_number }}
                                </p>
                                <p>
                                    <strong>Message:</strong>
                                    {{ viewing.message }}
                                </p>
                                <p>
                                    <strong>Status:</strong>
                                    {{ viewing.status }}
                                </p>
                                <p>
                                    <strong>Sent At:</strong>
                                    {{
                                        viewing.sent_at
                                            ? new Date(
                                                  viewing.sent_at,
                                              ).toLocaleString()
                                            : 'N/A'
                                    }}
                                </p>
                                <div class="mt-4">
                                    <PrimaryButton @click="viewing = null"
                                        >Close</PrimaryButton
                                    >
                                </div>
                            </div>
                        </Modal>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
