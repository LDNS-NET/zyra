
<script setup>
import { ref, watch } from 'vue';
import { router, usePage, Head } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';

const page = usePage();
const defaultForm = {
    portal_template: 'default',
    logo_url: '',
    user_prefix: '',
    prune_inactive_days: '',
};
const form = ref(
    page.props.settings
        ? { ...defaultForm, ...page.props.settings }
        : { ...defaultForm },
);
const success = ref(page.props.flash?.success || '');
const loading = ref(false);

function submit() {
    loading.value = true;
    success.value = '';
    router.post(route('settings.hotspot.update'), form.value, {
        onSuccess: (page) => {
            success.value =
                page.props.flash.success || 'Settings updated successfully.';
            // Only update form if backend returns new settings
            if (page.props.settings) {
                Object.assign(form.value, {
                    ...form.value,
                    ...page.props.settings,
                });
            }
        },
        onFinish: () => {
            loading.value = false;
        },
    });
}

watch(
    () => page.props.settings,
    (val) => {
        if (val) {
            Object.assign(form.value, { ...defaultForm, ...val });
        }
    },
);
</script>


<template>
    <Head title="Hotspot Settings" />
    <div
        class="mx-auto max-w-2xl rounded-lg bg-white p-6 shadow-md dark:bg-gray-800"
    >
        <h2
            class="mb-6 flex items-center gap-2 text-2xl font-bold text-gray-800 dark:text-gray-200"
        >
            <span>Hotspot Settings</span>
        </h2>
        <form @submit.prevent="submit" class="space-y-8">
            <!-- Captive Portal Template Selection -->
            <div class="rounded-lg border p-4 dark:border-gray-700">
                <div class="mb-4">
                    <InputLabel
                        for="portal_template"
                        value="Captive Portal Template"
                        class="text-lg font-semibold"
                    />
                    <select
                        id="portal_template"
                        v-model="form.portal_template"
                        class="mt-2 w-full rounded border-gray-300 dark:bg-gray-800 dark:text-gray-100"
                    >
                        <option value="default">Default</option>
                        <option value="modern-dark">Modern Dark</option>
                    </select>
                    <InputError
                        class="mt-2"
                        :message="page.props.errors.portal_template"
                    />
                    <div
                        v-if="form.portal_template === 'default'"
                        class="mt-2 text-xs text-gray-500"
                    >
                        Classic light theme with blue accent, logo, and simple
                        layout.
                    </div>
                    <div
                        v-else-if="form.portal_template === 'modern-dark'"
                        class="mt-2 text-xs text-gray-500"
                    >
                        Modern dark theme with bold accent, large buttons, and
                        sleek card design.
                    </div>
                </div>

                <div class="mb-4">
                    <InputLabel
                        for="logo_url"
                        value="Logo URL"
                        class="font-semibold"
                    />
                    <TextInput
                        id="logo_url"
                        v-model="form.logo_url"
                        class="mt-2 w-full"
                        placeholder="https://your-logo-url.com/logo.png"
                    />
                    <InputError
                        class="mt-2"
                        :message="page.props.errors.logo_url"
                    />
                </div>
            </div>
            <!-- User Prefix for Hotspot Users -->
            <div class="rounded-lg border p-4 dark:border-gray-700">
                <InputLabel
                    for="user_prefix"
                    value="User Prefix for New Hotspot Users"
                    class="text-lg font-semibold"
                />
                <TextInput
                    id="user_prefix"
                    v-model="form.user_prefix"
                    class="mt-2 w-full"
                    placeholder="e.g. WIFI-, HS-, NET-"
                />
                <InputError
                    class="mt-2"
                    :message="page.props.errors.user_prefix"
                />
            </div>
            <!-- Pruning Inactive Hotspot Users -->
            <div class="rounded-lg border p-4 dark:border-gray-700">
                <InputLabel
                    for="prune_inactive_days"
                    value="Prune Inactive Users After (days)"
                    class="text-lg font-semibold"
                />
                <TextInput
                    id="prune_inactive_days"
                    type="number"
                    min="1"
                    step="1"
                    v-model="form.prune_inactive_days"
                    class="mt-2 w-full"
                    placeholder="e.g. 30"
                />
                <InputError
                    class="mt-2"
                    :message="page.props.errors.prune_inactive_days"
                />
                <div class="mt-2 text-xs text-gray-500">
                    Inactive hotspot users will be automatically deleted after
                    this many days of no activity. Leave blank to disable
                    pruning.
                </div>
            </div>
            <div class="mt-8 flex items-center justify-end">
                <PrimaryButton
                    :class="{ 'opacity-25': loading }"
                    :disabled="loading"
                >
                    {{ loading ? 'Saving...' : 'Save Settings' }}
                </PrimaryButton>
            </div>
            <div
                v-if="success"
                class="mt-4 rounded-md border border-green-200 bg-green-100 p-4 text-green-800 dark:border-green-700 dark:bg-green-800 dark:text-green-200"
            >
                {{ success }}
            </div>
        </form>
    </div>
</template>
