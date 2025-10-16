<script setup>
import Checkbox from "@/Components/Checkbox.vue";
import GuestLayout from "@/Layouts/GuestLayout.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: "",
    username: "",
    password: "",
    remember: false,
});

const submit = () => {
    form.post(route("login"), {
        onFinish: () => form.reset("password"),
    });
};
</script>

<template>
    <GuestLayout>

        <Head title="Log in" />

        <div v-if="status" class="mb-4 text-sm font-medium text-green-600">
            {{ status }}
        </div>
        <div class="text-blue-800">
            <h2 class="mb-4 text-center text-2xl font-extrabold">
                Login
            </h2>
        </div>

        <form @submit.prevent="submit" class="bg-blue-100 py-2 px-3 rounded-xl">
            

            <div class="mt-4">
                <InputLabel for="username" value="Usename" />

                <TextInput id="username" type="text" class="mt-1 block w-full" v-model="form.username" required
                    autofocus autocomplete="username" />
                <InputError class="mt-2" :message="form.errors.username" />
            </div>

            <div class="mt-4">
                <InputLabel for="password" value="Password" />

                <TextInput id="password" type="password" class="mt-1 block w-full" v-model="form.password" required
                    autocomplete="current-password" />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4 block">
                <label class="flex items-center">
                    <Checkbox name="remember" v-model:checked="form.remember" />
                    <span class="ms-2 text-sm text-gray-600">Remember me</span>
                </label>
            </div>

            <div class="mt-4 flex items-center justify-between">
                <div class="text-blue-800 underline bg-gray-200 rounded-xl px-2 hover:text-white hover:bg-black/60">
                    <Link :href="route('register')" class="">
                    create account?
                    </Link>
                </div>

                <div class="hover:text-white hover:gb-black/60">
                    <PrimaryButton class="ms-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                        Log in
                    </PrimaryButton>
                </div>

            </div>
            <div class="justify-end m-4">
                <Link v-if="canResetPassword" :href="route('password.request')" class="text-sm underline">
                Forgot your password?
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>
