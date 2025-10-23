<script setup>
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import General from './General.vue';
import Hotspot from './Hotspot.vue';
import PaymentGateway from './PaymentGateway.vue';
import SmsGateway from './SmsGateway.vue';
import WhatsappGateway from './WhatsappGateway.vue';
import { Settings, Wifi, CreditCard, Smartphone } from 'lucide-vue-next';

const mainTabs = [
    { key: 'general', label: 'General', icon: Settings },
    //{ key: 'notifications', label: 'Notifications', icon: Bell },
    { key: 'hotspot', label: 'Hotspot', icon: Wifi },
    //{ key: 'whatsapp', label: 'WhatsApp', icon: MessageCircle },
    { key: 'sms', label: 'SMS', icon: Smartphone },
    { key: 'payment', label: 'Payment', icon: CreditCard },
];
const mainTab = ref('general');

const mainTabComponent = computed(() => {
    switch (mainTab.value) {
        case 'general':
            return General;
        case 'hotspot':
            return Hotspot;
        case 'payment':
            return PaymentGateway;
        case 'sms':
            return SmsGateway;
        case 'whatsapp':
            return WhatsappGateway;
        default:
            return General;
    }
});
</script>

<template>
    <AuthenticatedLayout>
        <div class="mx-auto max-w-5xl p-6">
            <div class="grid grid-cols-1 gap-6 md:grid-cols-4">
                <!-- Sidebar Navigation -->
                <aside
                    class="flex flex-col gap-2 rounded-xl bg-gray-300 p-4 shadow md:col-span-1 dark:bg-gray-800"
                >
                    <h2 class="mb-4 text-lg font-bold">Settings</h2>
                    <nav class="flex flex-col gap-2">
                        <button
                            v-for="tab in mainTabs"
                            :key="tab.key"
                            @click="mainTab = tab.key"
                            :class="
                                mainTab === tab.key
                                    ? 'bg-blue-50 font-semibold text-blue-700'
                                    : 'dark:text-gray-200'
                            "
                            class="flex items-center gap-2 rounded px-3 py-2 transition-colors hover:bg-blue-300 focus:outline-none"
                            :title="'Go to ' + tab.label + ' settings'"
                        >
                            <component :is="tab.icon" class="h-5 w-5" />
                            <span>{{ tab.label }}</span>
                        </button>
                    </nav>
                </aside>
                <!-- Main Content -->
                <main class="md:col-span-3">
                    <!-- Main Tab Content -->
                    <component :is="mainTabComponent" />
                </main>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
aside button {
    transition:
        background 0.2s,
        color 0.2s;
}
</style>
