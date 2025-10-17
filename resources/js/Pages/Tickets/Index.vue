<script setup>
import { ref, computed, watch } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
//import TextInput from '@/Components/TextInput.vue';
import TextArea from '@/Components/TextArea.vue';
//import SelectInput from '@/Components/SelectInput.vue';
import InputError from '@/Components/InputError.vue';
import Pagination from '@/Components/Pagination.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import { Plus, Save, X, Edit, Trash2, CheckCheck } from 'lucide-vue-next';

const props = defineProps({
    tickets: Object,
    statusFilter: String,
    users: Array,
    leads: Array,
});

const showModal = ref(false);
const editing = ref(null);
const selectedTenantTickets = ref([]);
const resolving = ref(null);
const showDescriptionModal = ref(false);
const fullDescription = ref('');

const form = useForm({
    client_type: 'user',
    client_id: '',
    priority: 'medium',
    status: 'open',
    description: '',
    resolution_note: '',
});

const openCreate = () => {
    editing.value = null;
    form.reset();
    form.status = 'open';
    form.priority = 'medium';
    showModal.value = true;
};

const openEdit = (ticket) => {
    editing.value = ticket.id;
    form.client_type = ticket.client_type;
    form.client_id = ticket.client_id;
    form.priority = ticket.priority;
    form.status = ticket.status;
    form.description = ticket.description;
    showModal.value = true;
};

const submit = () => {
    if (editing.value) {
        form.put(route('tickets.update', editing.value), {
            onSuccess: () => (showModal.value = false),
        });
    } else {
        form.post(route('tickets.store'), {
            onSuccess: () => (showModal.value = false),
        });
    }
};

//resolve ticket
const openResolve = (ticket) => {
    resolving.value = ticket.id;
    form.resolution_note = '';
};

const resolveTicket = () => {
    form.put(route('tickets.resolve', resolving.value), {
        onSuccess: () => {
            resolving.value = null;
            form.resolution_note = '';
        },
    });
};

watch(
    () => form.client_type,
    () => {
        form.client_id = '';
    },
);

const selectAll = ref(false);

watch(selectAll, (val) => {
    if (val) {
        selectedTenantTickets.value = props.tickets.data.map((lead) => lead.id);
    } else {
        selectedTenantTickets.value = [];
    }
});

const allIds = computed(() => props.tickets.data.map((l) => l.id));

watch(selectedTenantTickets, (val) => {
    selectAll.value = val.length === allIds.value.length;
});

const remove = (id) => {
    if (confirm('Delete this ticket?')) {
        router.delete(route('tenants.tickets.destroy', id));
    }
};

const changeFilter = (status) => {
    router.visit(route('tenants.tickets.index', { status }), {
        preserveScroll: true,
    });
};

const clients = computed(() =>
    form.client_type === 'user' ? props.users : props.leads,
);

//bulk delete
//bulk delete
const bulkDelete = () => {
    if (!selectedTenantTickets.value.length) return;
    if (!confirm('Are you sure you want to delete selected Tickets?')) return;

    router.delete(route('tenants.tickets.bulk-delete'), {
        data: { ids: selectedTenantTickets.value },
        onSuccess: () => {
            selectedTenantTickets.value = [];
            router.visit(route('tenants.tickets.index'), {
                preserveScroll: true,
            });
        },
    });
};

// truncate long descriptions
function truncateWords(text, wordCount = 2) {
    if (!text) return '';
    const words = text.split(' ');
    return words.length > wordCount
        ? words.slice(0, wordCount).join(' ') + '…'
        : text;
}

function showDescription(description) {
    fullDescription.value = description;
    showDescriptionModal.value = true;
}
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold">
                    Tickets ({{ props.statusFilter }})
                </h2>
                <PrimaryButton
                    @click="openCreate"
                    class="flex items-center gap-2"
                >
                    <Plus class="h-4 w-4" /> Add Ticket
                </PrimaryButton>
            </div>
        </template>

        <div>
            <div class="flex justify-between">
                <div
                    v-if="selectedTenantTickets.length"
                    class="mb-4 flex items-center justify-between rounded p-3"
                >
                    <div class="flex gap-2">
                        <DangerButton @click="bulkDelete"
                            >Delete ({{
                                selectedTenantTickets.length
                            }})</DangerButton
                        >
                    </div>
                </div>

                <!-- Filter Buttons -->
                <div class="mt-4 flex gap-2">
                    <PrimaryButton
                        @click="changeFilter('open')"
                        :class="{
                            'bg-blue-700': props.statusFilter === 'open',
                        }"
                    >
                        Open
                    </PrimaryButton>
                    <PrimaryButton
                        @click="changeFilter('closed')"
                        :class="{
                            'bg-blue-700': props.statusFilter === 'closed',
                        }"
                    >
                        Closed
                    </PrimaryButton>
                </div>
            </div>

            <!-- Table -->
            <div
                class="mt-4 overflow-x-auto rounded-lg border border-blue-400 bg-gray-200 shadow dark:bg-black"
            >
                <table class="min-w-full table-fixed divide-y divide-blue-400">
                    <thead class="">
                        <tr>
                            <td class="w-6 px-2 py-2 text-center">
                                <input
                                    type="checkbox"
                                    v-model="selectAll"
                                    class="h-4 w-4 align-middle accent-blue-600"
                                />
                            </td>

                            <th
                                class="px-4 py-2 text-left text-xs font-semibold text-blue-600"
                            >
                                Ticket #
                            </th>
                            <th
                                class="px-4 py-2 text-left text-xs font-semibold text-blue-600"
                            >
                                Client
                            </th>
                            <th
                                class="px-4 py-2 text-left text-xs font-semibold text-blue-600"
                            >
                                Type
                            </th>
                            <th
                                class="px-4 py-2 text-left text-xs font-semibold text-blue-600"
                            >
                                Priority
                            </th>
                            <th
                                class="px-4 py-2 text-left text-xs font-semibold text-blue-600"
                            >
                                Status
                            </th>
                            <th
                                class="px-4 py-2 text-left text-xs font-semibold text-blue-600"
                            >
                                Description
                            </th>
                            <th
                                class="px-4 py-2 text-right text-xs font-semibold text-blue-600"
                            >
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        <tr
                            v-for="ticket in tickets.data"
                            :key="ticket.id"
                            class="hover:bg-gray-50 dark:hover:bg-gray-700"
                        >
                            <td class="w-6 px-2 py-2 text-center">
                                <input
                                    type="checkbox"
                                    :value="ticket.id"
                                    v-model="selectedTenantTickets"
                                    class="h-4 w-4 align-middle accent-blue-600"
                                />
                            </td>

                            <td class="px-4 py-2 font-mono text-blue-600">
                                {{ ticket.ticket_number }}
                            </td>
                            <td class="px-4 py-2">
                                {{
                                    ticket.client?.full_name ||
                                    ticket.client?.name ||
                                    '—'
                                }}
                            </td>
                            <td class="px-4 py-2 capitalize">
                                {{ ticket.client_type }}
                            </td>
                            <td class="px-4 py-2 capitalize">
                                {{ ticket.priority }}
                            </td>
                            <td class="px-4 py-2 capitalize">
                                {{ ticket.status }}
                            </td>
                            <td class="px-4 py-2">
                                <span
                                    @click="showDescription(ticket.description)"
                                    class="cursor-pointer text-green-600 hover:underline"
                                >
                                    {{
                                        truncateWords(ticket.description, 1)
                                    }}</span
                                >
                            </td>
                            <td
                                class="flex justify-end gap-2 px-4 py-2 text-right"
                            >
                                <button
                                    @click="openResolve(ticket)"
                                    class="text-green-600 hover:underline"
                                    v-if="ticket.status === 'open'"
                                >
                                    <CheckCheck class="h-4 w-4" />
                                </button>

                                <button
                                    @click="openEdit(ticket)"
                                    class="text-blue-600 hover:underline"
                                >
                                    <Edit class="h-4 w-4" />
                                </button>
                                <button
                                    @click="remove(ticket.id)"
                                    class="text-red-600 hover:underline"
                                >
                                    <Trash2 class="h-4 w-4" />
                                </button>
                            </td>
                        </tr>
                        <tr v-if="tickets.data.length === 0">
                            <td
                                colspan="7"
                                class="py-6 text-center text-sm text-gray-500"
                            >
                                No tickets found.
                            </td>
                        </tr>
                    </tbody>
                    <!-- Pagination -->
                    <Pagination
                        :links="tickets.links"
                        class="w-6 px-2 py-2 text-center"
                    />
                </table>
            </div>
        </div>

        <!--Resolving modal-->
        <Modal :show="!!resolving" @close="resolving = null">
            <div class="space-y-4 p-6">
                <h2 class="text-lg font-semibold">Resolve Ticket</h2>
                <div>
                    <InputLabel value="Resolution Note" />
                    <TextArea
                        v-model="form.resolution_note"
                        rows="4"
                        class="w-full"
                    />
                    <InputError :message="form.errors.resolution_note" />
                </div>
                <div class="flex justify-end gap-2">
                    <DangerButton @click="resolving = null"
                        >Cancel</DangerButton
                    >
                    <PrimaryButton
                        @click="resolveTicket"
                        :disabled="form.processing"
                    >
                        <Save class="mr-1 h-4 w-4" /> Mark as Resolved
                    </PrimaryButton>
                </div>
            </div>
        </Modal>

        <!-- Modal -->
        <Modal :show="showModal" @close="showModal = false">
            <div class="space-y-4 p-6">
                <h2 class="text-lg font-semibold">
                    {{ editing ? 'Edit Ticket' : 'Create Ticket' }}
                </h2>

                <!-- Client Type -->
                <div>
                    <InputLabel value="Client Type" />
                    <select
                        v-model="form.client_type"
                        class="mt-1 block w-full rounded-md border-gray-300"
                    >
                        <option value="user">User</option>
                        <option value="lead">Lead</option>
                    </select>
                    <InputError :message="form.errors.client_type" />
                </div>

                <!-- Client Name -->
                <div>
                    <InputLabel value="Client" />
                    <select
                        v-model="form.client_id"
                        class="mt-1 block w-full rounded-md border-gray-300"
                    >
                        <option disabled value="">Select Client</option>
                        <option
                            v-for="client in clients"
                            :key="client.id"
                            :value="client.id"
                        >
                            {{ client.full_name || client.name }}
                        </option>
                    </select>
                    <InputError :message="form.errors.client_id" />
                </div>

                <!-- Priority -->
                <div>
                    <InputLabel value="Priority" />
                    <select
                        v-model="form.priority"
                        class="mt-1 block w-full rounded-md border-gray-300"
                    >
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                    </select>
                    <InputError :message="form.errors.priority" />
                </div>

                <!-- Status -->
                <div>
                    <InputLabel value="Status" />
                    <select
                        v-model="form.status"
                        class="mt-1 block w-full rounded-md border-gray-300"
                    >
                        <option value="open">Open</option>
                        <option value="closed">Closed</option>
                    </select>
                    <InputError :message="form.errors.status" />
                </div>

                <!-- Description -->
                <div>
                    <InputLabel value="Description" />
                    <TextArea
                        v-model="form.description"
                        class="mt-1 block w-full"
                        rows="3"
                    />
                    <InputError :message="form.errors.description" />
                </div>

                <div class="mt-4 flex justify-end gap-3">
                    <DangerButton type="button" @click="showModal = false">
                        <X class="mr-1 h-4 w-4" /> Cancel
                    </DangerButton>
                    <PrimaryButton :disabled="form.processing" @click="submit">
                        <Save class="mr-1 h-4 w-4" />
                        {{ editing ? 'Update' : 'Save' }}
                    </PrimaryButton>
                </div>
            </div>
        </Modal>

        <Modal
            :show="showDescriptionModal"
            @close="showDescriptionModal = false"
        >
            <div class="p-6">
                <h2 class="mb-4 text-lg font-semibold">Description</h2>
                <p class="text-gray-700">{{ fullDescription }}</p>
                <div class="mt-4 text-right">
                    <PrimaryButton @click="showDescriptionModal = false"
                        >Close</PrimaryButton
                    >
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
