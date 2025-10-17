<script setup>
import { ref, computed, watch } from 'vue';
import { useForm, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import Pagination from '@/Components/Pagination.vue';
import { useToast } from 'vue-toastification';

const toast = useToast();

import { UserPlus, UserCheck } from 'lucide-vue-next';

const props = defineProps({
    users: Object,
    filters: Object,
    counts: Object,
    packages: Object, // comes from controller
});

const showModal = ref(false);
const editing = ref(null);
const viewing = ref(null);
const selectedFilter = ref('all');

const form = useForm({
    full_name: '',
    username: '',
    password: '',
    phone: '',
    email: '',
    location: '',
    package_id: '',
    type: 'hotspot',
    expires_at: '',
});

watch(selectedFilter, (value) => {
    router.get(route('users.index'), { type: value }, { preserveScroll: true });
});

function openCreate() {
    editing.value = null;
    form.reset();
    form.type = 'hotspot';
    showModal.value = true;
}

const selected = ref([]);

const bulkForm = useForm({ ids: [] });

const confirmBulkDelete = () => {
    bulkForm.ids = selected.value;
    if (bulkForm.ids.length) {
        bulkForm.post(route('users.bulk-delete'), {
            onSuccess: () => {
                selected.value = [];
            },
        });
    }
};

function openEdit(user) {
    editing.value = user.id;
    form.full_name = user.full_name ?? '';
    form.username = user.username ?? '';
    form.password = '';
    form.phone = user.phone ?? '';
    form.email = user.email ?? '';
    form.location = user.location ?? '';
    form.package_id = user.package_id ?? '';
    form.type = user.type ?? 'hotspot';
    form.expires_at = user.expires_at ? user.expires_at.slice(0, 16) : '';
    showModal.value = true;
}

function submit() {
    const options = {
        onSuccess: () => {
            showModal.value = false;
            router.reload({ only: ['users'], preserveScroll: true });
            toast.success(
                editing.value
                    ? 'User updated successfully'
                    : 'User created successfully',
            );
        },
        onError: () => {
            toast.error('Something went wrong. Please check the form.');
        },
    };

    if (editing.value) {
        form.put(route('users.update', editing.value), options);
    } else {
        form.post(route('users.store'), options);
    }
}

function remove(id) {
    if (confirm('Are you sure you want to delete this User?')) {
        router.delete(route('users.destroy', id), {
            preserveScroll: true,
            onSuccess: () => {
                toast.success('User deleted successfully');
            },
            onError: () => {
                toast.error('Failed to delete user');
            },
        });
    }
}

const selectedUsers = ref([]);

const bulkDelete = () => {
    if (selectedUsers.value.length && confirm('Delete selected Users?')) {
        router.delete(route('users.bulk-delete'), {
            data: { ids: selectedUsers.value },
            onSuccess: () => {
                selectedUsers.value = [];
                router.visit(route('users.index'), {
                    preserveScroll: true,
                    preserveState: false,
                });
                toast.success('Users successfully deleted');
            },
        });
    }
};

function viewUser(user) {
    viewing.value = user;
}

const packagesByType = computed(() => {
    return props.packages[form.type] || [];
});
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2
                    class="flex items-center gap-2 text-2xl font-semibold text-gray-800"
                >
                    <UserCheck class="h-6 w-6 text-blue-600" />
                    Users
                </h2>
                <PrimaryButton
                    @click="openCreate"
                    class="flex items-center gap-2 bg-green-700"
                >
                    <UserPlus class="h-4 w-4" />
                    Add User
                </PrimaryButton>
            </div>
        </template>

        <div class="px-4 py-6 sm:px-6 lg:px-8">
            <!-- Filters -->
            <!-- Filter Buttons -->
            <div class="mb-4 flex flex-wrap gap-3">
                <button
                    v-for="type in ['all', 'hotspot', 'pppoe', 'static']"
                    :key="type"
                    @click="selectedFilter = type"
                    class="rounded-full border px-4 py-1.5 text-sm font-medium transition-all duration-150"
                    :class="{
                        'border-blue-600 bg-blue-600 text-white shadow-sm dark:border-blue-500 dark:bg-blue-500':
                            selectedFilter === type,
                        'border-gray-300 bg-white text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700':
                            selectedFilter !== type,
                    }"
                >
                    {{ type.charAt(0).toUpperCase() + type.slice(1) }}
                    <span class="ml-1 text-xs opacity-80"
                        >({{ counts[type] || 0 }})</span
                    >
                </button>
            </div>

            <!-- Bulk Delete Actions -->
            <div
                v-if="selectedUsers.length"
                class="mb-4 flex flex-wrap items-center justify-between rounded-lg border border-yellow-300 bg-yellow-50 p-3 dark:border-yellow-700 dark:bg-yellow-900/30"
            >
                <div class="flex gap-3">
                    <DangerButton @click="bulkDelete">
                        Delete ({{ selectedUsers.length }})
                    </DangerButton>
                    <!-- Add more bulk actions here if needed -->
                </div>

                <p class="text-sm text-yellow-700 dark:text-yellow-300">
                    {{ selectedUsers.length }} user<span
                        v-if="selectedUsers.length > 1"
                        >s</span
                    >
                    selected
                </p>
            </div>

            <!-- Users Table -->
            <div
                class="overflow-x-auto rounded-xl border border-blue-400 bg-white shadow dark:bg-gray-900"
            >
                <!-- Table wrapper for responsiveness -->
                <div class="w-full min-w-[600px] sm:min-w-full">
                    <table
                        class="w-full divide-y divide-gray-200 text-sm dark:divide-gray-700"
                    >
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-3 py-3">
                                    <input
                                        type="checkbox"
                                        :checked="
                                            selectedUsers.length ===
                                            users.data.length
                                        "
                                        @change="
                                            selectedUsers = $event.target
                                                .checked
                                                ? users.data.map((u) => u.id)
                                                : []
                                        "
                                    />
                                </th>
                                <th
                                    class="whitespace-nowrap px-4 py-3 text-left text-xs font-semibold sm:text-sm dark:text-blue-400"
                                >
                                    Username
                                </th>
                                <th
                                    class="whitespace-nowrap px-4 py-3 text-left text-xs font-semibold sm:text-sm dark:text-blue-400"
                                >
                                    Account No
                                </th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold sm:text-sm dark:text-blue-400"
                                >
                                    Phone
                                </th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold sm:text-sm dark:text-blue-400"
                                >
                                    Package
                                </th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold sm:text-sm dark:text-blue-400"
                                >
                                    Expiry
                                </th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold sm:text-sm dark:text-blue-400"
                                >
                                    Status
                                </th>
                                <th
                                    class="px-4 py-3 text-right text-xs font-semibold sm:text-sm dark:text-blue-400"
                                >
                                    Actions
                                </th>
                            </tr>
                        </thead>

                        <tbody
                            class="divide-y divide-blue-200 dark:divide-blue-900"
                        >
                            <tr
                                v-for="user in users.data"
                                :key="user.id"
                                class="transition hover:bg-gray-100 dark:hover:bg-gray-800"
                            >
                                <td class="px-3 py-3 align-top">
                                    <input
                                        type="checkbox"
                                        :value="user.id"
                                        v-model="selectedUsers"
                                    />
                                </td>

                                <td class="px-4 py-3 align-top">
                                    <Link
                                        :href="route('users.show', user.id)"
                                        class="block break-words font-semibold hover:text-blue-500 dark:hover:text-green-400"
                                    >
                                        {{ user.username }}
                                        <div
                                            class="max-w-[160px] truncate text-xs text-gray-500 sm:max-w-none dark:text-gray-400"
                                        >
                                            {{ user.full_name }}
                                        </div>
                                    </Link>
                                </td>

                                <td
                                    class="px-4 py-3 align-top font-mono text-xs"
                                >
                                    <span v-if="user.account_number">
                                        {{
                                            user.account_number?.substring(
                                                0,
                                                10,
                                            )
                                        }}
                                    </span>
                                    <span v-else>—</span>
                                </td>

                                <td class="px-4 py-3 align-top text-sm">
                                    {{ user.phone }}
                                </td>

                                <td class="px-4 py-3 align-top text-sm">
                                    {{ user.package?.name || '-' }}
                                </td>

                                <td
                                    class="whitespace-nowrap px-4 py-3 align-top text-sm"
                                >
                                    {{ user.expiry_human }}
                                </td>

                                <td class="px-4 py-3 align-top">
                                    <span
                                        class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-medium"
                                        :class="
                                            user.is_online
                                                ? 'bg-green-100 text-green-700 dark:bg-green-900/50 dark:text-green-300'
                                                : 'bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300'
                                        "
                                    >
                                        {{
                                            user.is_online
                                                ? 'Online'
                                                : 'Offline'
                                        }}
                                    </span>
                                </td>

                                <td class="px-4 py-3 text-right align-top">
                                    <div
                                        class="relative inline-block text-left"
                                    >
                                        <button
                                            @click="
                                                user.showActions =
                                                    !user.showActions
                                            "
                                            class="rounded p-1 hover:bg-gray-100 focus:outline-none dark:hover:bg-gray-700"
                                        >
                                            <svg
                                                class="h-5 w-5"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <circle
                                                    cx="12"
                                                    cy="12"
                                                    r="1.5"
                                                />
                                                <circle
                                                    cx="19.5"
                                                    cy="12"
                                                    r="1.5"
                                                />
                                                <circle
                                                    cx="4.5"
                                                    cy="12"
                                                    r="1.5"
                                                />
                                            </svg>
                                        </button>

                                        <div
                                            v-if="user.showActions"
                                            class="absolute right-0 z-10 mt-2 w-32 origin-top-right rounded-md border bg-white shadow-lg ring-1 ring-black ring-opacity-5 dark:border-gray-700 dark:bg-gray-800"
                                        >
                                            <div class="py-1">
                                                <button
                                                    @click="
                                                        viewUser(user);
                                                        user.showActions = false;
                                                    "
                                                    class="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-700"
                                                >
                                                    View
                                                </button>
                                                <button
                                                    @click="
                                                        openEdit(user);
                                                        user.showActions = false;
                                                    "
                                                    class="block w-full px-4 py-2 text-left text-sm text-blue-600 hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-700/50"
                                                >
                                                    Edit
                                                </button>
                                                <button
                                                    @click="
                                                        remove(user.id);
                                                        user.showActions = false;
                                                    "
                                                    class="block w-full px-4 py-2 text-left text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-700/50"
                                                >
                                                    Delete
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>

                            <tr v-if="users.data.length === 0">
                                <td
                                    colspan="8"
                                    class="py-6 text-center text-gray-500 dark:text-gray-400"
                                >
                                    No users found.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="p-3 sm:p-4">
                    <Pagination class="mt-3 sm:mt-4" :links="users.links" />
                </div>
            </div>
        </div>

        <!-- Modal Form -->
        <Modal :show="showModal" @close="showModal = false">
            <div class="p-6">
                <h3 class="mb-4 text-lg font-semibold">
                    {{ editing ? 'Edit User' : 'Create User' }}
                </h3>
                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <InputLabel for="full_name" value="Full Name" />
                        <TextInput
                            v-model="form.full_name"
                            id="full_name"
                            class="mt-1 block w-full"
                        />
                        <InputError :message="form.errors.full_name" />
                    </div>

                    <div>
                        <InputLabel for="username" value="Username" />
                        <TextInput
                            v-model="form.username"
                            id="username"
                            class="mt-1 block w-full"
                        />
                        <InputError :message="form.errors.username" />
                    </div>

                    <div>
                        <InputLabel for="password" value="Password" />
                        <TextInput
                            v-model="form.password"
                            id="password"
                            class="mt-1 block w-full"
                            type="text"
                            autocomplete="off"
                        />
                        <InputError :message="form.errors.password" />
                    </div>

                    <div>
                        <InputLabel for="phone" value="Phone" />
                        <TextInput
                            v-model="form.phone"
                            id="phone"
                            class="mt-1 block w-full"
                        />
                        <InputError :message="form.errors.phone" />
                    </div>

                    <div>
                        <InputLabel for="email" value="Email" />
                        <TextInput
                            v-model="form.email"
                            id="email"
                            class="mt-1 block w-full"
                        />
                        <InputError :message="form.errors.email" />
                    </div>

                    <div>
                        <InputLabel for="location" value="Location" />
                        <TextInput
                            v-model="form.location"
                            id="location"
                            class="mt-1 block w-full"
                        />
                        <InputError :message="form.errors.location" />
                    </div>

                    <div>
                        <InputLabel for="expires_at" value="Expiry Date" />
                        <TextInput
                            id="expires_at"
                            type="datetime-local"
                            v-model="form.expires_at"
                            class="mt-1 block w-full"
                        />
                        <InputError :message="form.errors.expires_at" />
                    </div>

                    <div>
                        <InputLabel for="type" value="User Type" />
                        <select
                            v-model="form.type"
                            id="type"
                            class="mt-1 w-full rounded-md border-gray-300 dark:bg-black"
                        >
                            <option value="hotspot">Hotspot</option>
                            <option value="pppoe">PPPoE</option>
                            <option value="static">Static</option>
                        </select>
                        <InputError :message="form.errors.type" />
                    </div>

                    <div>
                        <InputLabel for="package_id" value="Package" />
                        <select
                            v-model="form.package_id"
                            id="package_id"
                            class="mt-1 w-full rounded-md border-gray-300 dark:bg-black"
                        >
                            <option
                                v-for="pkg in packagesByType"
                                :key="pkg.id"
                                :value="pkg.id"
                            >
                                {{ pkg.name }}
                            </option>
                        </select>
                        <InputError :message="form.errors.package_id" />
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <DangerButton @click="showModal = false" type="button"
                            >Cancel</DangerButton
                        >
                        <PrimaryButton :disabled="form.processing">{{
                            editing ? 'Update' : 'Save'
                        }}</PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- View Modal -->
        <Modal :show="viewing" @close="viewing = null">
            <div
                class="mx-auto max-w-2xl space-y-6 rounded-lg bg-gradient-to-r from-cyan-100 to-violet-100 p-6 shadow-xl"
            >
                <div class="text-center">
                    <h2 class="text-2xl font-bold text-gray-800">
                        User Profile
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Complete access details for client configuration
                    </p>
                </div>

                <!-- Credentials Section -->
                <div class="rounded-md border bg-indigo-200 p-4">
                    <h3 class="mb-2 text-sm font-semibold text-blue-700">
                        Login Credentials
                    </h3>
                    <div
                        class="grid grid-cols-1 gap-4 text-sm text-gray-800 sm:grid-cols-2"
                    >
                        <div>
                            <span class="text-blue-700">Username:</span>
                            <div class="font-mono text-green-700">
                                {{ viewing?.username }}
                            </div>
                            <div
                                v-if="viewing?.account_number"
                                class="text-xs text-gray-500"
                            >
                                Account No:
                                {{ viewing?.account_number.substring(0, 6) }}
                            </div>
                        </div>
                        <div>
                            <span class="text-blue-700">Password:</span>
                            <div class="flex items-center space-x-2">
                                <span class="font-mono text-red-600">{{
                                    viewing?.password
                                }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Personal Info -->
                <div
                    class="grid grid-cols-1 gap-4 text-sm text-gray-700 sm:grid-cols-2"
                >
                    <div>
                        <span class="font-semibold text-black">Full Name:</span>
                        <p>{{ viewing?.full_name }}</p>
                    </div>
                    <div>
                        <span class="font-semibold text-black">Phone:</span>
                        <p>{{ viewing?.phone }}</p>
                    </div>
                    <div>
                        <span class="font-semibold text-black">Email:</span>
                        <p>{{ viewing?.email || '—' }}</p>
                    </div>
                    <div>
                        <span class="font-semibold text-black">Location:</span>
                        <p>{{ viewing?.location || '—' }}</p>
                    </div>
                    <div>
                        <span class="font-semibold text-black">Package:</span>
                        <p>{{ viewing?.package?.name || '—' }}</p>
                    </div>
                    <div>
                        <span class="font-semibold text-black">Type:</span>
                        <span
                            class="inline-block rounded bg-blue-100 px-2 py-1 text-xs font-semibold text-blue-800"
                        >
                            {{ viewing?.type }}
                        </span>
                    </div>
                    <div>
                        <span class="font-semibold text-black">Expiry:</span>
                        <p>{{ viewing?.expiry_human }}</p>
                    </div>
                </div>

                <div class="flex justify-end pt-4">
                    <PrimaryButton @click="viewing = null">Close</PrimaryButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
