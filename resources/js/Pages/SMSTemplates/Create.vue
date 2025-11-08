<script setup>
import { Head } from '@inertiajs/vue3';
import { Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextArea from '@/Components/TextArea.vue';
import InputLabel from '@/Components/InputLabel.vue';
import { useToast } from 'vue-toastification';

const toast = useToast();

const form = useForm({
    name: '',
    content: '',
});
function submit() {
    form.post(route('smstemplates.store'), {
        onSuccess: () => {
            toast.success('Template created successfully');
            form.reset();
        },
        onError: () => {
            toast.error('Failed. Check form for errors.');
        },
    });
}
</script>

<template>
    <Head title="Create SMSTemplate"/>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight">SMS Template</h2>
        </template>

        <div class="rounded-xl py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div
                    class="overflow-hidden border border-dashed border-purple-800 bg-gray-200 p-6 px-8 shadow-sm sm:rounded-lg sm:px-4 md:px-8 lg:px-12 xl:px-16 dark:border-blue-600 dark:bg-black"
                >
                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <label
                                for="name"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                                >Template Name</label
                            >
                            <input
                                v-model="form.name"
                                type="text"
                                name="name"
                                id="name"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
                            />
                        </div>
                        <div>
                            <InputLabel
                                for="content"
                                class="flex text-sm font-medium text-gray-700 dark:text-gray-300"
                                >Template Content</InputLabel
                            >

                            <TextArea
                                v-model="form.content"
                                name="content"
                                id="content"
                                rows="4"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
                            ></TextArea>
                        </div>

                        <div class="px-2 font-semibold">
                            Use These Variables to customise your messages:
                        </div>
                        <div
                            class="rounded-xl border border-dotted border-blue-500 bg-gray-300 px-2 py-2 text-sm text-gray-900 dark:bg-gray-900 dark:text-gray-100"
                        >
                            <div>{full_name}, {phone}, {account_number}, {package}</div>
                            <div> {expiry_date}, {username}, {password}, {support_number}</div>
                        </div>

                        <div class="flex items-center justify-between">
                            <div>
                                <PrimaryButton>
                                    <Link
                                        :href="route('smstemplates.index')"
                                        class="inline-flex items-center"
                                    >
                                        Cancel
                                    </Link>
                                </PrimaryButton>
                            </div>
                            <div>
                                <PrimaryButton
                                    type="submit"
                                    class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                                    :disabled="form.processing"
                                >
                                    Save
                                </PrimaryButton>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
