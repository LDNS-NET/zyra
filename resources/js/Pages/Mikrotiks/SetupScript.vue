<script setup>
import { ref, onUnmounted, watch } from 'vue';
import { route } from 'ziggy-js';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    router: Object,
    script: String,
});

const copied = ref(false);
const waiting = ref(false);
const online = ref(false);
const pollingError = ref('');
let pollInterval = null;

const POLL_INTERVAL = 5000; // 5 seconds

// -------------------
// Copy & Download
// -------------------
function copyScript() {
    navigator.clipboard.writeText(props.script);
    copied.value = true;
    setTimeout(() => (copied.value = false), 2000);
}

function downloadScript() {
    const blob = new Blob([props.script], { type: 'text/plain' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    // use .rsc.txt to make it openable on Windows
    a.download = `setup_router_${props.router.id}.rsc.txt`;
    a.click();
    URL.revokeObjectURL(url);
}

// -------------------
// Polling Logic
// -------------------
function checkRouterStatus() {
    fetch(route('mikrotiks.ping', props.router.id))
        .then((res) => {
            if (!res.ok)
                throw new Error('Failed to reach router check endpoint.');
            return res.json();
        })
        .then((data) => {
            if (data.status === 'online') {
                online.value = true;
                waiting.value = false;
                stopPolling();
            }
        })
        .catch((err) => {
            pollingError.value = err.message || 'Error checking router status.';
            waiting.value = false;
            stopPolling();
        });
}

function startPolling() {
    if (pollInterval) return; // prevent duplicate intervals
    waiting.value = true;
    pollingError.value = '';
    pollInterval = setInterval(checkRouterStatus, POLL_INTERVAL);
}

function stopPolling() {
    if (pollInterval) {
        clearInterval(pollInterval);
        pollInterval = null;
    }
}

onUnmounted(() => {
    stopPolling();
});

// Optional user feedback when router becomes online
watch(online, (isOnline) => {
    if (isOnline) {
        alert('✅ Router is now online and ready for configuration!');
    }
});

function proceed() {
    // Redirect to router index or next onboarding step
    router.visit(route('mikrotiks.index'));
}
</script>

<template>
    <Head title="Mikrotik Onboarding" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-200">
                Mikrotik System Onboarding Script
            </h2>
        </template>

        <div class="mx-auto max-w-3xl py-8">
            <div
                class="rounded-xl border border-dashed border-blue-400 bg-white p-6 shadow dark:bg-black"
            >
                <p class="mb-4">
                    <b class="text-blue-600">Step 1:<br /></b>
                    Copy or download the script below and run it in your
                    Mikrotik terminal (Winbox, WebFig, or SSH).<br />
                    <b class="text-blue-500">Step 2:<br /></b>
                    After running the script, copy the
                    <b class="text-blue-500">IP address</b> shown in the output
                    into the system to complete onboarding.<br />
                    <b class="text-blue-600">Step 3:<br /></b>
                    Click <b class="text-blue-600">"I've run the script"</b> to
                    begin checking if your router is online. Once detected, you
                    can continue.
                </p>

                <div
                    class="mb-4 rounded border-l-4 border-blue-400 bg-blue-50 p-4 text-blue-900 dark:border-green-600 dark:bg-blue-900 dark:text-blue-200"
                >
                    <b>What does this script do?</b>
                    <ul class="ml-6 mt-2 list-disc text-sm">
                        <li>Sets the router's identity (name)</li>
                        <li>Prints all current IP addresses for onboarding</li>
                        <li>Enables API and adds a system API user</li>
                        <li>
                            Restricts API/Winbox/SSH to trusted IPs (edit as
                            needed)
                        </li>
                        <li>
                            Enables OVPN server and RADIUS authentication for
                            PPP, Hotspot, and OVPN
                        </li>
                        <li>
                            Adds a RADIUS client for full system authentication
                        </li>
                        <li>
                            Adds a <b>disconnect-user</b> script for remote user
                            management
                        </li>
                        <li>
                            Adds a <b>health-check</b> scheduler for system
                            monitoring
                        </li>
                        <li>Follows best security and automation practices</li>
                    </ul>
                </div>

                <!-- Copy / Download Buttons -->
                <div class="mb-4 flex gap-2">
                    <PrimaryButton @click="copyScript">
                        {{ copied ? 'Copied!' : 'Copy Script' }}
                    </PrimaryButton>
                    <PrimaryButton @click="downloadScript">
                        Download Script
                    </PrimaryButton>
                </div>

                <!-- Script Display -->
                <pre
                    class="mb-6 overflow-x-auto rounded bg-gray-900 p-4 text-xs text-green-200"
                    style="min-height: 300px"
                    >{{ script }}</pre
                >

                <!-- Polling Controls -->
                <div class="mb-4 flex flex-wrap items-center gap-3">
                    <PrimaryButton
                        @click="startPolling"
                        :disabled="waiting || online"
                    >
                        I've run the script
                    </PrimaryButton>

                    <!-- Status Feedback -->
                    <template v-if="waiting">
                        <span class="flex items-center gap-2 text-blue-600">
                            <svg
                                class="h-5 w-5 animate-spin"
                                fill="none"
                                viewBox="0 0 24 24"
                            >
                                <circle
                                    class="opacity-25"
                                    cx="12"
                                    cy="12"
                                    r="10"
                                    stroke="currentColor"
                                    stroke-width="4"
                                ></circle>
                                <path
                                    class="opacity-75"
                                    fill="currentColor"
                                    d="M4 12a8 8 0 018-8v8z"
                                ></path>
                            </svg>
                            Waiting for router to come online...
                        </span>
                    </template>

                    <template v-if="online">
                        <span
                            class="flex items-center gap-2 font-semibold text-green-600"
                        >
                            <svg
                                class="h-5 w-5"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M5 13l4 4L19 7"
                                />
                            </svg>
                            Router is online! You can continue.
                        </span>
                    </template>

                    <template v-if="pollingError">
                        <span class="text-red-600">{{ pollingError }}</span>
                    </template>
                </div>

                <!-- Proceed -->
                <div class="flex justify-end">
                    <PrimaryButton @click="proceed" :disabled="!online">
                        Next
                    </PrimaryButton>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
