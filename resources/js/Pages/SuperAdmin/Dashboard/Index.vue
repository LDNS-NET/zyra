<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { User, Banknote, MessageSquare, Building2, Activity, ArrowRight } from 'lucide-vue-next';
import SuperAdminLayout from '@/Layouts/SuperAdminLayout.vue';

const page = usePage();
const props = computed(() => page.props ?? {});

// Extract specific props
const stats = computed(() => [
    {
        title: 'Registered Tenants',
        value: props.value?.totalTenants ?? 0,
        icon: Building2,
        color: 'bg-blue-100 text-blue-600 dark:bg-blue-900 dark:text-blue-300',
        //link: route('superadmin.tenants.index'),
    },
    {
        title: 'Users',
        value: props.value?.totalEndUsers ?? 0,
        icon: User,
        color: 'bg-green-100 text-green-600 dark:bg-green-900 dark:text-green-300',
        //link: route('superadmin.users.index'),
    },
    {
        title: 'Payments',
        value: `KSh ${Number(props.value?.totalPayments || 0).toLocaleString()}`,
        icon: Banknote,
        color: 'bg-yellow-100 text-yellow-600 dark:bg-yellow-900 dark:text-yellow-300',
        //link: route('superadmin.payments.index'),
    },
    {
        title: 'SMS Sent',
        value: props.value?.totalSMS ?? 0,
        icon: MessageSquare,
        color: 'bg-purple-100 text-purple-600 dark:bg-purple-900 dark:text-purple-300',
        //link: route('superadmin.sms.index'),
    },
]);

// ✅ Safely access recentActivity
const recentActivity = computed(() => props.value?.recentActivity ?? []);
</script>

<template>
    <Head title="Super Admin Dashboard" />

    <SuperAdminLayout>
        <template #header>
            <h2 class="text-2xl font-semibold leading-tight">
                Super Admin Dashboard
            </h2>
        </template>

        <div class="space-y-6">
            <!-- ✅ Stats Cards -->
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div
                    v-for="(item, index) in stats"
                    :key="index"
                    class="rounded-2xl border border-gray-100 bg-white p-5 shadow transition-all hover:shadow-md dark:border-gray-700 dark:bg-gray-800"
                >
                    <div class="mb-3 flex items-center justify-between">
                        <div :class="['rounded-lg p-2', item.color]">
                            <component :is="item.icon" class="h-6 w-6" />
                        </div>
                        <Link
                            :href="item.link"
                            class="text-sm text-gray-500 hover:text-blue-600 dark:text-gray-400"
                        >
                            <ArrowRight class="inline h-4 w-4" />
                        </Link>
                    </div>

                    <h3
                        class="text-sm font-medium text-gray-500 dark:text-gray-400"
                    >
                        {{ item.title }}
                    </h3>
                    <p class="mt-1 text-2xl font-bold">{{ item.value }}</p>
                </div>
            </div>

            <!-- ✅ Recent Activity -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div
                    class="rounded-2xl border border-gray-100 bg-white p-6 shadow dark:border-gray-700 dark:bg-gray-800"
                >
                    <div class="mb-4 flex justify-between">
                        <h3 class="text-lg font-semibold">Recent Activity</h3>
                        <Activity
                            class="h-5 w-5 text-gray-500 dark:text-gray-400"
                        />
                    </div>

                    <!-- Optional debug line -->
                    <!-- <pre class="text-xs text-gray-400">{{ recentActivity }}</pre> -->

                    <ul v-if="recentActivity.length" class="space-y-3 text-sm">
                        <li
                            v-for="(item, index) in recentActivity"
                            :key="index"
                            class="flex justify-between border-b border-gray-100 pb-2 dark:border-gray-700"
                        >
                            <span v-html="item.message"></span>
                            <span
                                class="text-xs text-gray-500 dark:text-gray-400"
                            >
                                {{ item.time }}
                            </span>
                        </li>
                    </ul>

                    <div
                        v-else
                        class="py-4 text-center text-sm text-gray-500 dark:text-gray-400"
                    >
                        No recent activity yet.
                    </div>
                </div>
            </div>
        </div>
    </SuperAdminLayout>
</template>
