<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm, usePage, Head } from '@inertiajs/vue3';
import Layout from '../Layout.vue';
const page = usePage();
const settings = page.props.settings || {};

// Create useForm with server values (same pattern as your profile)
const form = useForm({
    portal_template: settings.portal_template ?? 'default',
    logo_url: settings.logo_url ?? '',
    user_prefix: settings.user_prefix ?? '',
    prune_inactive_days: settings.prune_inactive_days ?? '',
});
</script>

<template>
    <Layout>
        <Head title="Hotspot Settings" />

        <section
            class="mx-auto max-w-2xl rounded-xl border bg-gray-100 p-6 dark:bg-black"
        >
            <header>
                <h2 class="font-extrabold">Hotspot Settings</h2>
                <p class="mt-1 text-sm text-gray-600">
                    Configure your tenant hotspot appearance and behavior below.
                </p>
            </header>

            <form
                @submit.prevent="form.post(route('settings.hotspot.update'))"
                class="mt-6 space-y-6"
            >
                <div>
                    <InputLabel
                        for="portal_template"
                        value="Captive Portal Template"
                    />
                    <select
                        id="portal_template"
                        v-model="form.portal_template"
                        class="mt-1 block w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100"
                    >
                        <option value="default">Default</option>
                        <option value="modern-dark">Modern Dark</option>
                    </select>
                    <InputError
                        class="mt-2"
                        :message="form.errors.portal_template"
                    />
                </div>

                <div>
                    <InputLabel for="logo_url" value="Logo URL" />
                    <TextInput
                        id="logo_url"
                        v-model="form.logo_url"
                        class="mt-1 block w-full"
                        placeholder="https://your-logo-url.com/logo.png"
                    />
                    <InputError class="mt-2" :message="form.errors.logo_url" />
                </div>

                <div>
                    <InputLabel
                        for="user_prefix"
                        value="User Prefix for New Hotspot Users"
                    />
                    <TextInput
                        id="user_prefix"
                        v-model="form.user_prefix"
                        class="mt-1 block w-full"
                        placeholder="e.g. WIFI-, HS-, NET-"
                    />
                    <InputError
                        class="mt-2"
                        :message="form.errors.user_prefix"
                    />
                </div>

                <div>
                    <InputLabel
                        for="prune_inactive_days"
                        value="Prune Inactive Users After (days)"
                    />
                    <TextInput
                        id="prune_inactive_days"
                        type="number"
                        min="1"
                        step="1"
                        v-model="form.prune_inactive_days"
                        class="mt-1 block w-full"
                        placeholder="e.g. 30"
                    />
                    <InputError
                        class="mt-2"
                        :message="form.errors.prune_inactive_days"
                    />
                    <p class="mt-1 text-xs text-gray-500">
                        Leave blank to disable auto-deletion of inactive users.
                    </p>
                </div>

                <div class="flex items-center justify-between gap-4 py-3">
                    <Transition
                        enter-active-class="transition ease-in-out"
                        enter-from-class="opacity-0"
                        leave-active-class="transition ease-in-out"
                        leave-to-class="opacity-0"
                    >
                        <p
                            v-if="form.recentlySuccessful"
                            class="text-sm text-green-600"
                        >
                            Saved.
                        </p>
                    </Transition>

                    <PrimaryButton :disabled="form.processing"
                        >Save Settings</PrimaryButton
                    >
                </div>
            </form>
        </section>
    </Layout>
</template>
