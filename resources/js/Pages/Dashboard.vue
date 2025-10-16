<script setup>
import { ref, onMounted } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DashboardSection from '@/Components/DashboardSection.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Card from '@/Components/Card.vue';
import ChartCard from '@/Components/ChartCard.vue';
import {
    Users,
    User,
    Ticket,
    Inbox,
    RadioTower,
    DollarSign,
    Package,
    Coins,
    MessagesSquare,
    Server,
    FileText,
    Smile,
    BarChart2,
    TrendingUp,
    Activity,
    Check,
    X,
    Clock,
} from 'lucide-vue-next';

const props = defineProps(['stats']);
const page = usePage();
const user = usePage().props.auth.user;
const expiresAt = ref(page.props.subscription_expires_at || null);
const countdown = ref('');
const daysRemaining = ref(0);

function updateCountdown() {
    if (!expiresAt.value) return;

    const now = new Date();
    const expiry = new Date(expiresAt.value);
    const diff = expiry - now;

    if (diff <= 0) {
        countdown.value = 'Expired';
        daysRemaining.value = 0;
        return;
    }

    const days = Math.floor(diff / (1000 * 60 * 60 * 24));
    const hours = Math.floor((diff / (1000 * 60 * 60)) % 24);
    const minutes = Math.floor((diff / (1000 * 60)) % 60);
    const seconds = Math.floor((diff / 1000) % 60);

    countdown.value = `${days}d ${hours}h ${minutes}m ${seconds}s`;
    daysRemaining.value = days;
}

onMounted(() => {
    updateCountdown();
    setInterval(updateCountdown, 1000);
});

// Debug subscription data
console.log('Subscription data:', props.stats?.subscription);

/* Dynamic greeting based on time of day
const getGreeting = () => {
    const hour = new Date().getHours();
    if (hour < 12) return 'Good morning';
    if (hour < 18) return 'Good afternoon';
    return 'Good evening';
};*/
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Tenant Dashboard" />

        <div class="space-y-6 p-6">
            <!-- Pay Subscription Button & Duration -->
            <div class="mb-4 flex items-center justify-between">
                <div v-if="expiresAt && daysRemaining <= 30" class="items flex">
                    <template v-if="expiresAt">
                        <button
                            class="rounded-xl bg-blue-400 px-3 py-1 dark:bg-green-400"
                        >
                            <div class="font-extrabold">Exp in:</div>
                            <span class="font-bold">{{ countdown }}</span>
                        </button>
                    </template>
                </div>
                <div v-if="expiresAt && daysRemaining <= 31" class="ml-4">
                    <PrimaryButton
                        class="rounded-xl px-3 py-1 hover:bg-blue-600 dark:bg-green-400 dark:hover:bg-green-600"
                        ><a
                            href="https://payment.intasend.com/pay/8d7f60c4-f2c2-4642-a2b6-0654a3cc24e3/"
                            target="_blank"
                            class="rounded-xl bg-blue-400 px-3 py-1 dark:bg-green-400"
                        >
                            Make Payment
                        </a>
                    </PrimaryButton>
                </div>
            </div>
            <!-- Account Balance Card -->
            <div
                class="mb-8 flex items-center justify-between rounded-xl bg-gradient-to-r from-green-100 to-blue-100 p-6 shadow-lg"
            >
                <div>
                    <h2 class="mb-1 text-xl font-bold text-gray-800">
                        Account Balance
                    </h2>
                    <div class="text-3xl font-extrabold text-green-700">
                        KES {{ stats.account_balance ?? '0.00' }}
                    </div>
                    <div class="mt-1 text-xs text-gray-500">
                        Wallet ID:
                        <span class="font-mono">{{
                            stats.wallet_id || 'Not Set'
                        }}</span>
                    </div>
                </div>
                <DollarSign class="h-12 w-12 text-green-400" />
            </div>
            <!-- Trial Banner -->
            <div
                v-if="stats.subscription && stats.subscription.is_on_trial"
                class="mb-8 rounded-xl bg-gradient-to-r from-blue-500 to-indigo-600 p-6 shadow-lg"
            >
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="rounded-full bg-white/20 p-3">
                            <Clock class="h-8 w-8 text-white" />
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-white">
                                Free Trial Active
                            </h2>
                            <p class="text-blue-100">
                                {{ stats.subscription.trial_days_remaining }}
                                days
                                <span
                                    v-if="
                                        stats.subscription
                                            .trial_hours_remaining !== undefined
                                    "
                                >
                                    {{
                                        stats.subscription.trial_hours_remaining
                                    }}
                                    hours
                                </span>
                                remaining in your trial period
                            </p>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-3xl font-bold text-white">
                            {{ stats.subscription.trial_days_remaining }}
                            <span
                                v-if="
                                    stats.subscription.trial_hours_remaining !==
                                    undefined
                                "
                            >
                                .{{ stats.subscription.trial_hours_remaining }}h
                            </span>
                        </div>
                        <div class="text-sm text-blue-100">left</div>
                    </div>
                </div>
            </div>

            <!-- USERS -->
            <DashboardSection title="Network Users">
                <Card title="Total" :value="stats.users.total" :icon="Users" />
                <Card
                    title="Hotspot"
                    :value="stats.users.hotspot"
                    :icon="RadioTower"
                />
                <Card title="PPPoE" :value="stats.users.pppoe" :icon="User" />
                <Card
                    title="Static"
                    :value="stats.users.static"
                    :icon="Server"
                />
                <Card
                    title="Active"
                    :value="stats.users.active"
                    :icon="Smile"
                />
                <Card title="Expired" :value="stats.users.expired" :icon="X" />
            </DashboardSection>

            <!-- LEADS -->
            <DashboardSection title="Leads">
                <Card title="Total" :value="stats.leads.total" :icon="Inbox" />
                <Card
                    title="Pending"
                    :value="stats.leads.pending"
                    :icon="Activity"
                />
                <Card
                    title="Converted"
                    :value="stats.leads.converted"
                    :icon="Check"
                />
                <Card title="Lost" :value="stats.leads.lost" :icon="X" />
            </DashboardSection>

            <!-- TICKETS -->
            <DashboardSection title="Tickets">
                <Card title="Open" :value="stats.tickets.open" :icon="Ticket" />
                <Card
                    title="Closed"
                    :value="stats.tickets.closed"
                    :icon="Check"
                />
                <Card
                    title="Assigned to Me"
                    :value="stats.tickets.assigned_to_me"
                    :icon="User"
                />
            </DashboardSection>

            <!-- MIKROTIKS -->
            <DashboardSection title="MikroTik Devices">
                <Card
                    title="Total"
                    :value="stats.mikrotiks.total"
                    :icon="RadioTower"
                />
                <Card
                    title="Connected"
                    :value="stats.mikrotiks.connected"
                    :icon="Check"
                />
                <Card
                    title="Disconnected"
                    :value="stats.mikrotiks.disconnected"
                    :icon="X"
                />
            </DashboardSection>

            <!-- PAYMENTS -->
            <DashboardSection
                title="Payments"
                v-if="user.role === 'admin' || user.role === 'cashier'"
            >
                <Card
                    title="Total Payments"
                    :value="stats.payments.count"
                    :icon="DollarSign"
                />
                <Card
                    title="Total Amount"
                    :value="`KES ${stats.payments.total_amount}`"
                    :icon="Coins"
                />
                <Card
                    v-if="stats.payments.latest"
                    title="Latest Payment"
                    :value="`KES ${stats.payments.latest.amount}`"
                    :subtitle="stats.payments.latest.paid_at"
                    :icon="DollarSign"
                />
                <Card
                    title="Pending Disbursements"
                    :value="stats.payments.pending_disbursement"
                    :icon="FileText"
                />
            </DashboardSection>

            <!-- SMS -->

            <DashboardSection
                title="SMS"
                v-if="user.role === 'admin' || user.role === 'cashier'"
            >
                <Card
                    title="Total Sent"
                    :value="stats.sms.total_sent"
                    :icon="MessagesSquare"
                />
                <Card
                    title="This Month"
                    :value="stats.sms.sent_this_month"
                    :icon="TrendingUp"
                />
            </DashboardSection>

            <!-- PACKAGES -->

            <DashboardSection title="Packages">
                <Card
                    title="Total"
                    :value="stats.packages.total"
                    :icon="Package"
                />
                <Card
                    title="Active"
                    :value="stats.packages.active"
                    :icon="Check"
                />
            </DashboardSection>

            <!-- EQUIPMENT -->

            <DashboardSection
                title="Equipment"
                v-if="user.role === 'admin' || user.role === 'technician'"
            >
                <Card
                    title="Total Items"
                    :value="stats.equipment.total"
                    :icon="Server"
                />
                <Card
                    title="Total Value"
                    :value="`KES ${stats.equipment.total_value}`"
                    :icon="DollarSign"
                />
            </DashboardSection>

            <!-- CHARTS -->
            <DashboardSection title="Analytics & Trends">
                <div
                    class="grid gap-6"
                    style="
                        grid-template-columns: repeat(
                            auto-fit,
                            minmax(320px, 1fr)
                        );
                    "
                >
                    <ChartCard
                        title="User Type Distribution"
                        :labels="
                            stats.user_distribution
                                ? Object.keys(stats.user_distribution)
                                : []
                        "
                        :values="
                            stats.user_distribution
                                ? Object.values(stats.user_distribution)
                                : []
                        "
                        type="donut"
                        :icon="BarChart2"
                        class="min-w-[300px]! w-full"
                    />
                    <ChartCard
                        title="Monthly SMS Sent"
                        :labels="
                            stats.sms_chart ? Object.keys(stats.sms_chart) : []
                        "
                        :values="
                            stats.sms_chart
                                ? Object.values(stats.sms_chart)
                                : []
                        "
                        type="bar"
                        :icon="TrendingUp"
                        class="min-w-[300px]! w-full"
                    />
                    <ChartCard
                        title="Payments Over Time"
                        :labels="
                            stats.payments_chart
                                ? Object.keys(stats.payments_chart)
                                : []
                        "
                        :values="
                            stats.payments_chart
                                ? Object.values(stats.payments_chart)
                                : []
                        "
                        type="line"
                        :icon="DollarSign"
                        class="min-w-[300px]! w-full"
                    />
                </div>
            </DashboardSection>

            <!-- RECENT ACTIVITY -->
            <DashboardSection title="Recent Activity">
                <div
                    class="grid gap-6"
                    style="
                        grid-template-columns: repeat(
                            auto-fit,
                            minmax(320px, 1fr)
                        );
                    "
                >
                    <div
                        class="min-w-[300px]! w-full rounded-lg bg-white p-4 shadow"
                    >
                        <h3
                            class="mb-2 flex items-center gap-2 font-semibold text-blue-600"
                        >
                            <Users class="h-5 w-5" /> New Users
                        </h3>
                        <ul class="text-sm text-gray-500">
                            <li
                                v-for="u in stats.recent_activity.latest_users"
                                :key="u.username"
                            >
                                {{ u.username }} - {{ u.type }}
                            </li>
                        </ul>
                    </div>
                    <div
                        class="min-w-[300px]! w-full rounded-lg bg-white p-4 shadow"
                    >
                        <h3
                            class="mb-2 flex items-center gap-2 font-semibold text-green-600"
                        >
                            <DollarSign class="h-5 w-5" /> Recent Payments
                        </h3>
                        <ul class="text-sm text-gray-500">
                            <li
                                v-for="p in stats.recent_activity
                                    .latest_payments"
                                :key="p.receipt_number"
                            >
                                KES {{ p.amount }} - {{ p.paid_at }}
                            </li>
                        </ul>
                    </div>
                    <div
                        class="min-w-[300px]! w-full rounded-lg bg-white p-4 shadow"
                    >
                        <h3
                            class="mb-2 flex items-center gap-2 font-semibold text-purple-600"
                        >
                            <Inbox class="h-5 w-5" /> Latest Leads
                        </h3>
                        <ul class="text-sm text-gray-500">
                            <li
                                v-for="l in stats.recent_activity.latest_leads"
                                :key="l.name"
                            >
                                {{ l.name }} - {{ l.status }}
                            </li>
                        </ul>
                    </div>
                </div>
            </DashboardSection>

            <!-- EXPORT OPTIONS -->
            <div
                v-if="user.role === 'admin'"
                class="mt-8 flex justify-end gap-3"
            >
                <button
                    @click="window.print()"
                    class="rounded bg-blue-600 px-4 py-2 text-white"
                >
                    Print
                </button>
                <a
                    :href="route('dashboard.export', { format: 'excel' })"
                    class="rounded bg-green-600 px-4 py-2 text-white"
                    >Export Excel</a
                >
                <a
                    :href="route('dashboard.export', { format: 'pdf' })"
                    class="rounded bg-red-600 px-4 py-2 text-white"
                    >Export PDF</a
                >
            </div>
        </div>
    </AuthenticatedLayout>
</template>
