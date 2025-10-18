<script setup>
import { ref } from 'vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import Pagination from '@/Components/Pagination.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {
    Pencil,
    PencilIcon,
    PlusIcon,
    Save,
    Trash,
    Trash2,
} from 'lucide-vue-next';
import { useToast } from 'vue-toastification';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';

const toast = useToast();

const props = defineProps({
    templates: Object,
    perPage: Number,
});

const showModal = ref(false);
const editing = ref(null);

const form = useForm({
    name: '',
    content: '',
});

/**
 * Open edit modal and populate form
 */
function openEdit(template) {
    editing.value = template.id;
    form.name = template.name;
    form.content = template.content;
    showModal.value = true;
}

function closeModal() {
    showModal.value = false;
    editing.value = null;
    form.reset();
    form.clearErrors();
}

/**
 * Submit edited form via Inertia PUT request
 */
function submit() {
    if (!editing.value) return;

    form.put(route('smstemplates.update', editing.value), {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Template updated successfully');
            closeModal();
        },
        onError: () => {
            toast.error(
                'Failed to update template. Please check the form fields.',
            );
        },
    });
}

function remove(id) {
    if (confirm('Are you sure you want to delete this template?')) {
        router.delete(route('smstemplates.destroy', id), {
            onSuccess: () => {
                toast.success('Template deleted successfully');
            },
            onError: () => {
                toast.error('Failed to delete template.');
            },
        });
    }
}
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between">
                <h2 class="text-xl font-semibold leading-tight">
                    SMS Templates
                </h2>
                <Link
                    :href="route('smstemplates.create')"
                    class="flex items-center gap-2 rounded-lg bg-blue-600 px-3 py-2 text-sm font-medium text-white hover:bg-blue-700"
                >
                    <PlusIcon class="h-4 w-4" />
                    <span>New Template</span>
                </Link>
            </div>
        </template>

        <div class="rounded-xl py-12 dark:bg-black">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div
                    class="overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg dark:bg-transparent"
                >
                    <table
                        class="w-full table-auto divide-y divide-gray-300 border border-gray-300 dark:divide-gray-700 dark:border-gray-700"
                    >
                        <thead>
                            <tr>
                                <th
                                    class="border-b border-r border-gray-300 px-4 py-2 text-left dark:border-gray-700"
                                >
                                    Name
                                </th>
                                <th
                                    class="border-b border-gray-300 px-4 py-2 text-left dark:border-gray-700"
                                >
                                    Content
                                </th>
                                <th
                                    class="border-b border-gray-300 px-4 py-2 text-left dark:border-gray-700"
                                >
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="template in templates.data"
                                :key="template.id"
                                class="hover:bg-gray-50 dark:hover:bg-gray-800"
                            >
                                <td
                                    class="border-b border-r border-gray-300 px-4 py-2 dark:border-gray-700"
                                >
                                    {{ template.name }}
                                </td>
                                <td
                                    class="max-w-xs overflow-hidden text-ellipsis whitespace-nowrap border-b border-gray-300 px-4 py-2 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                                    :title="template.content"
                                >
                                    {{
                                        template.content.length > 20
                                            ? template.content.slice(0, 20) +
                                              '...'
                                            : template.content
                                    }}
                                </td>
                                <td
                                    class="flex justify-end gap-2 px-4 py-3 text-right"
                                >
                                    <button
                                        @click="openEdit(template)"
                                        class="text-blue-500 hover:text-blue-700"
                                    >
                                        <PencilIcon class="h-4 w-auto" />
                                    </button>
                                    <button
                                        @click="remove(template.id)"
                                        class="text-red-500 hover:text-red-700"
                                    >
                                        <Trash2 class="h-4 w-auto" />
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <Pagination :links="templates.links" class="mt-4" />
                </div>

                <!-- Edit Modal -->
                <Modal :show="showModal" @close="showModal = false">
                    <div class="p-6">
                        <h3 class="mb-4 text-lg font-semibold">
                            {{ editing ? 'Edit Template' : 'Create Template' }}
                        </h3>
                        <form @submit.prevent="submit" class="space-y-4">
                            <div>
                                <InputLabel for="name" value="Template name" />
                                <TextInput
                                    id="name"
                                    v-model="form.name"
                                    class="mt-1 block w-full"
                                />
                                <InputError
                                    :message="form.errors.name"
                                    class="mt-1"
                                />
                            </div>
                            <div>
                                <InputLabel
                                    for="content"
                                    value="Template content"
                                />
                                <TextInput
                                    id="content"
                                    v-model="form.content"
                                    class="mt-1 block w-full"
                                />
                                <InputError
                                    :message="form.errors.content"
                                    class="mt-1"
                                />
                            </div>
                            <div class="flex justify-end space-x-2">
                                <DangerButton
                                    type="button"
                                    @click="closeModal"
                                    class="rounded bg-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2"
                                >
                                    Cancel
                                </DangerButton>
                                <PrimaryButton :disabled="form.processing">
                                    <Save class="mr-1 h-4 w-4" />
                                    {{ editing ? 'Update' : 'Save' }}
                                </PrimaryButton>
                            </div>
                        </form>

                        <div></div>
                    </div>
                </Modal>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
