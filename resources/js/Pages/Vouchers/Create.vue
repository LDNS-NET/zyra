<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import { useToast } from 'vue-toastification';

const toast = useToast();

const props = defineProps({
    packages: Array,
});

const form = useForm({
    prefix: '',
    length: 10,
    quantity: 50,
    package_id: '',
});

const submitForm = () => {
    form.post(route('vouchers.store'), {
        onSuccess: () => {
            form.reset();
            toast.success('Vouchers generated successfully');
        },
        onError: () => {
            toast.error('Failed to generate vouchers. Please check the form.');
        },
    });
};

const cancelForm = () => {
    window.history.back();
};
</script>

<template>
    <Head title="Generate Vouchers" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100">
                Generate Vouchers
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <div
                    class="overflow-hidden rounded-2xl border border-black bg-gray-200 shadow-lg dark:border-blue-700 dark:bg-gray-900"
                >
                    <div
                        class="border-b border-blue-200 p-6 dark:border-green-400"
                    >
                        <form @submit.prevent="submitForm" class="space-y-6">
                            <!-- Voucher Prefix -->
                            <div>
                                <InputLabel
                                    for="prefix"
                                    value="Voucher Prefix"
                                />
                                <TextInput
                                    id="prefix"
                                    v-model="form.prefix"
                                    class="mt-1 block w-full dark:bg-gray-800 dark:text-gray-100"
                                    placeholder="e.g. WIFI"
                                />
                                <InputError
                                    :message="form.errors.prefix"
                                    class="mt-2"
                                />
                            </div>

                            <!-- Voucher Code Length -->
                            <div>
                                <InputLabel
                                    for="length"
                                    value="Voucher Code Length"
                                />
                                <TextInput
                                    id="length"
                                    type="number"
                                    v-model="form.length"
                                    min="4"
                                    max="20"
                                    class="mt-1 block w-full dark:bg-gray-800 dark:text-gray-100"
                                />
                                <InputError
                                    :message="form.errors.length"
                                    class="mt-2"
                                />
                            </div>

                            <!-- Quantity -->
                            <div>
                                <InputLabel
                                    for="quantity"
                                    value="Number of Vouchers"
                                />
                                <TextInput
                                    id="quantity"
                                    type="number"
                                    v-model="form.quantity"
                                    min="1"
                                    class="mt-1 block w-full dark:bg-gray-800 dark:text-gray-100"
                                />
                                <InputError
                                    :message="form.errors.quantity"
                                    class="mt-2"
                                />
                            </div>

                            <!-- Internet Package -->
                            <div>
                                <InputLabel
                                    for="package_id"
                                    value="Internet Package"
                                />
                                <select
                                    id="package_id"
                                    v-model="form.package_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 bg-white text-gray-900 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
                                >
                                    <option value="" disabled>
                                        Select Package
                                    </option>
                                    <option
                                        v-for="pkg in packages"
                                        :key="pkg.id"
                                        :value="pkg.id"
                                    >
                                        {{ pkg.name }} — KES {{ pkg.price }}
                                    </option>
                                </select>
                                <InputError
                                    :message="form.errors.package_id"
                                    class="mt-2"
                                />
                            </div>

                            <!-- Actions -->
                            <div class="flex justify-end gap-3">
                                <DangerButton
                                    type="button"
                                    @click="cancelForm"
                                    :disabled="form.processing"
                                    class="px-4"
                                >
                                    Cancel
                                </DangerButton>
                                <PrimaryButton :disabled="form.processing">
                                    Generate
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
