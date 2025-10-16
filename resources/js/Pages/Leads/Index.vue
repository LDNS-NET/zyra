<script setup>
import { ref, watch, computed } from 'vue';

import { useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import Pagination from '@/Components/Pagination.vue';
import TextArea from '@/Components/TextArea.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import { Plus, Edit, Trash2, Save, X } from 'lucide-vue-next';

const props = defineProps({
    leads: Object,
    Count: Object,
    filters: Object,
    Pagination: Object,
});

const showModal = ref(false);
const editing = ref(null);
const selectedTenantLeads = ref([]);
const showAddressModal = ref(false);
const fullAddress = ref('');

const form = useForm({
    name: '',
    phone_number: '',
    address: '',
    email_address: '',
    status: 'new',
});

function openCreate() {
    form.reset();
    editing.value = null;
    showModal.value = true;
}

function openEdit(lead) {
    editing.value = lead.id;
    form.name = lead.name;
    form.phone_number = lead.phone_number;
    form.email_address = lead.email_address;
    form.address = lead.address;
    form.status = lead.status || 'new';
    showModal.value = true;
}

const selectAll = ref(false);

watch(selectAll, (val) => {
    if (val) {
        selectedTenantLeads.value = props.leads.data.map((lead) => lead.id);
    } else {
        selectedTenantLeads.value = [];
    }
});

const allIds = computed(() => props.leads.data.map((l) => l.id));

watch(selectedTenantLeads, (val) => {
    selectAll.value = val.length === allIds.value.length;
});

function submit() {
    if (editing.value) {
        form.put(route('leads.update', editing.value), {
            onSuccess: () => (showModal.value = false),
        });
    } else {
        form.post(route('leads.store'), {
            onSuccess: () => (showModal.value = false),
        });
    }
}

function remove(id) {
    if (confirm('Delete this lead?')) {
        router.delete(route('leads.destroy', id));
    }
}

//bulk delete
const bulkDelete = () => {
    if (!selectedTenantLeads.value.length) return;
    if (!confirm('Are you sure you want to delete Leads?')) return;

    router.delete(route('leads.bulk-delete'), {
        data: { ids: selectedTenantLeads.value },
        onSuccess: () => {
            selectedTenantLeads.value = [];
            router.visit(route('leads.index'), {
                preserveScroll: true,
            });
        },
    });
};

//truncate long descriptions
function truncateWords(text, wordCount = 2) {
    if (!text) return '';
    const words = text.split(' ');
    return words.length > wordCount
        ? words.slice(0, wordCount).join(' ') + '…'
        : text;
}

function showAddress(address) {
    fullAddress.value = address;
    showAddressModal.value = true;
}
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold">Leads</h2>
                <PrimaryButton
                    @click="openCreate"
                    class="flex items-center gap-2"
                >
                    <Plus class="h-4 w-4" /> Add Lead
                </PrimaryButton>
            </div>
        </template>
        <div
            v-if="selectedTenantLeads.length"
            class="mb-4 flex items-center justify-between rounded border border-yellow-200 bg-yellow-50 p-3"
        >
            <div class="flex gap-2">
                <DangerButton @click="bulkDelete"
                    >Delete ({{ selectedTenantLeads.length }})</DangerButton
                >
            </div>
        </div>

        <div class="mt-6 overflow-x-auto rounded-lg bg-white shadow">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <td class="px-4 py-3">
                            <input type="checkbox" v-model="selectAll" />
                        </td>
                        <th
                            class="px-4 py-2 text-left text-xs font-semibold text-gray-600"
                        >
                            Name
                        </th>
                        <th
                            class="px-4 py-2 text-left text-xs font-semibold text-gray-600"
                        >
                            Phone
                        </th>
                        <th
                            class="px-4 py-2 text-left text-xs font-semibold text-gray-600"
                        >
                            Email
                        </th>
                        <th
                            class="px-4 py-2 text-left text-xs font-semibold text-gray-600"
                        >
                            Address
                        </th>
                        <th
                            class="px-4 py-2 text-left text-xs font-semibold text-gray-600"
                        >
                            Status
                        </th>
                        <th
                            class="px-4 py-2 text-right text-xs font-semibold text-gray-600"
                        >
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr
                        v-for="lead in leads.data"
                        :key="lead.id"
                        class="hover:bg-gray-50"
                    >
                        <td class="px-6 py-3">
                            <input
                                type="checkbox"
                                :value="lead.id"
                                v-model="selectedTenantLeads"
                            />
                        </td>
                        <td class="px-4 py-2">{{ lead.name }}</td>
                        <td class="px-4 py-2">{{ lead.phone_number }}</td>
                        <td class="px-4 py-2">{{ lead.email_address }}</td>
                        <td class="px-4 py-2">
                            <span
                                @click="showAddress(lead.address)"
                                class="cursor-pointer text-green-600 hover:underline"
                            >
                                {{ truncateWords(lead.address, 1) }}
                            </span>
                        </td>
                        <td class="px-4 py-2">{{ lead.status || '—' }}</td>
                        <td class="flex justify-end gap-2 px-4 py-2 text-right">
                            <button
                                @click="openEdit(lead)"
                                class="text-blue-600 hover:underline"
                            >
                                <Edit class="h-4 w-4" />
                            </button>
                            <button
                                @click="remove(lead.id)"
                                class="text-red-600 hover:underline"
                            >
                                <Trash2 class="h-4 w-4" />
                            </button>
                        </td>
                    </tr>
                    <tr v-if="leads.data.length === 0">
                        <td
                            colspan="6"
                            class="py-6 text-center text-sm text-gray-500"
                        >
                            No leads found.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <Pagination :links="leads.links" />

        <Modal :show="showModal" @close="showModal = false">
            <div class="p-6">
                <h2 class="mb-4 text-lg font-semibold">
                    {{ editing ? 'Edit Lead' : 'Create Lead' }}
                </h2>

                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <InputLabel for="name" value="Full Name" />
                        <TextInput
                            id="name"
                            v-model="form.name"
                            class="mt-1 block w-full"
                        />
                        <InputError :message="form.errors.name" class="mt-1" />
                    </div>

                    <div>
                        <InputLabel for="phone" value="Phone Number" />
                        <TextInput
                            id="contact"
                            v-model="form.phone_number"
                            class="mt-1 block w-full"
                        />
                        <InputError
                            :message="form.errors.phone_number"
                            class="mt-1"
                        />
                    </div>

                    <div>
                        <InputLabel
                            for="email_address"
                            value="Email Address (optional)"
                        />
                        <TextInput
                            id="email"
                            v-model="form.email_address"
                            class="mt-1 block w-full"
                        />
                        <InputError
                            :message="form.errors.email_address"
                            class="mt-1"
                        />
                    </div>

                    <div>
                        <InputLabel for="address" value="Address" />
                        <TextArea
                            id="address"
                            v-model="form.address"
                            rows="2"
                            class="mt-1 block w-full"
                        />
                        <InputError
                            :message="form.errors.address"
                            class="mt-1"
                        />
                    </div>

                    <div>
                        <InputLabel for="status" value="Status" />
                        <select
                            v-model="form.status"
                            id="status"
                            class="mt-1 block w-full rounded-md border-gray-300"
                        >
                            <option class="text-red-500" value="new">
                                New
                            </option>
                            <option class="text-amber-500" value="contacted">
                                Contacted
                            </option>
                            <option class="text-green-600" value="converted">
                                Converted
                            </option>
                        </select>
                        <InputError
                            :message="form.errors.status"
                            class="mt-1"
                        />
                    </div>

                    <div class="flex justify-end gap-3">
                        <DangerButton type="button" @click="showModal = false">
                            <X class="mr-1 h-4 w-4" /> Cancel
                        </DangerButton>
                        <PrimaryButton :disabled="form.processing">
                            <Save class="mr-1 h-4 w-4" />
                            {{ editing ? 'Update' : 'Save' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <Modal :show="showAddressModal" @close="showAddressModal = false">
            <div class="p-6">
                <h2 class="mb-4 text-lg font-semibold">Full Address</h2>
                <p class="text-gray-700">{{ fullAddress }}</p>
                <div class="mt-4 text-right">
                    <PrimaryButton @click="showAddressModal = false"
                        >Close</PrimaryButton
                    >
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
