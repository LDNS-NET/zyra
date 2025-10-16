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
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Generate Vouchers
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="border-b border-gray-200 bg-white p-6">
                        <form @submit.prevent="submitForm" class="space-y-6">
                            <div>
                                <InputLabel
                                    for="prefix"
                                    value="Voucher Prefix"
                                />
                                <TextInput
                                    id="prefix"
                                    v-model="form.prefix"
                                    class="mt-1 block w-full"
                                />
                                <InputError
                                    :message="form.errors.prefix"
                                    class="mt-2"
                                />
                            </div>

                            <div>
                                <InputLabel
                                    for="length"
                                    value="Voucher Code Length"
                                />
                                <TextInput
                                    id="length"
                                    type="number"
                                    v-model="form.length"
                                    class="mt-1 block w-full"
                                    min="4"
                                    max="20"
                                />
                                <InputError
                                    :message="form.errors.length"
                                    class="mt-2"
                                />
                            </div>

                            <div>
                                <InputLabel
                                    for="quantity"
                                    value="Number of Vouchers to Generate"
                                />
                                <TextInput
                                    id="quantity"
                                    type="number"
                                    v-model="form.quantity"
                                    class="mt-1 block w-full"
                                    min="1"
                                />
                                <InputError
                                    :message="form.errors.quantity"
                                    class="mt-2"
                                />
                            </div>

                            <div>
                                <InputLabel
                                    for="package_id"
                                    value="Internet Package"
                                />
                                <select
                                    v-model="form.package_id"
                                    class="mt-1 block w-full rounded-md border-gray-300"
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

                            <div class="flex justify-end">
                                <DangerButton
                                    type="button"
                                    @click="cancelForm"
                                    class="mr-4"
                                    >Cancel</DangerButton
                                >
                                <PrimaryButton :disabled="form.processing"
                                    >Generate</PrimaryButton
                                >
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
