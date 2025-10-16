<script setup>
import { ref, onMounted, watch } from "vue";
import { Link } from "@inertiajs/vue3";
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import Dropdown from "@/Components/Dropdown.vue";
import DropdownLink from "@/Components/DropdownLink.vue";
import NavLink from "@/Components/NavLink.vue";
import { useTheme } from "@/composables/useTheme";

// Icons (Lucide)
import {
    LayoutDashboard,
    Users,
    Banknote,
    MessageSquare,
    LogOut,
    Settings,
    Building2,
    SunIcon,
    FolderEdit,
    AlertCircleIcon,
    ReceiptCent,
    GitPullRequest,
    Link2Icon,
    MailCheck,
} from "lucide-vue-next";

const { theme, applyTheme } = useTheme();
const sidebarOpen = ref(false);

onMounted(() => {
    const savedTheme = localStorage.getItem("house_theme") || "light";
    theme.value = savedTheme;
    applyTheme(savedTheme);
});

watch(theme, (val) => {
    localStorage.setItem("house_theme", val);
    applyTheme(val);
});
</script>

<template>
    <!-- Wrapper -->
    <div
        class="min-h-auto·flex·w-full·bg-gray-50·text-gray-900·transition-colors·duration-300·dark:bg-gray-900·dark:text-white"
    >
        <!-- Sidebar -->
        <aside
            class="fixed inset-y-0 left-0 z-30 w-64 flex-shrink-0 transform bg-white shadow-lg transition-transform duration-200 ease-in-out lg:relative lg:translate-x-0 dark:bg-gray-800"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        >
            <div
                class="flex items-center justify-between border-b border-gray-200 px-4 py-4 dark:border-gray-700"
            >
                <Link
                    :href="route('dashboard')"
                    class="flex items-center space-x-2 "
                >
                    <ApplicationLogo class="h-8 w-auto" />
                    <span
                        class="text-lg font-semibold text-black dark:text-white"
                        >zISP</span
                    >
                </Link>
                <button
                    @click="sidebarOpen = false"
                    class="text-gray-500 hover:text-gray-700 lg:hidden dark:text-gray-300"
                >
                    <svg
                        class="h-6 w-6"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>
                </button>
            </div>

            <!-- Sidebar Links -->
            <nav class="h-[calc(100vh-4rem)] space-y-1 overflow-y-auto p-4">
                <div class="mb-4 px-3">
                    <NavLink
                        :href="route('dashboard')"
                        :active="route().current('dashboard')"
                        class="flex items-center p-2 dark:text-white"
                    >
                        <LayoutDashboard
                            class="mr-2 h-4 w-4 text-blue-700 dark:text-blue-300"
                        />
                        Dashboard
                    </NavLink>
                </div>

                <div class="mb-4 px-3">
                    <NavLink
                        href="route('apartments.index')"
                        :active="route().current('apartments.index')"
                        class="flex items-center dark:text-white p-2"
                    >
                        <Building2 class="mr-2 h-4 w-4 text-indigo-500 dark:text-indigo-200" />
                        Apartments
                    </NavLink>
                </div>

                <div class="mb-4 px-3">
                    <NavLink
                        href="route('renters.index')"
                        :active="route().current('renters.index')"
                        class="flex items-center dark:text-white p-2"
                    >
                        <Users class="mr-2 h-4 w-4 text-emerald-500" />
                        Renters
                    </NavLink>
                </div>

                <div class="mb-4 px-3">
                    <NavLink
                        href="route('payments.index')"
                        :active="route().current('payments.index')"
                        class="flex items-center dark:text-white p-2"
                    >
                        <Banknote class="mr-2 h-4 w-4 text-yellow-500" />
                        Payments
                    </NavLink>
                </div>

                <div class="mb-4 px-3">
                    <NavLink
                        href="route('sms.index')"
                        :active="route().current('sms.index')"
                        class="flex items-center dark:text-white p-2"
                    >
                        <MessageSquare class="mr-2 h-4 w-4 text-purple-500" />
                        SMS
                    </NavLink>
                </div>

                <div class="mb-4 px-3">
                    <NavLink
                        href="route('emails.index')"
                        :active="route().current('emails.index')"
                        class="flex items-center dark:text-white p-2"
                    >
                        <MailCheck class="mr-2 h-4 w-4 text-red-500" />
                        Emails
                    </NavLink>
                </div>

                <div class="align-bottom m-4 py-52">
                    <h2 class="flex">
                        <AlertCircleIcon class="mr-5 h-7 w-auto text-red-500"/>
                        <span>Coming soon</span>
                        
                    </h2>

                    <div class="bg-slate-300 border dark:bg-blue-700 rounded-xl py-2 m-2">
                        <div class="mb-2 px-2">
                            <NavLink
                                href="#"
                                :active="route().current('invoices.index')"
                                class="flex items-center dark:text-white p-2"
                            >
                                <ReceiptCent class="mr-2 h-4 w-4 text-blue-500" />
                                Invoices
                            </NavLink>
                        </div>

                        <div class="mb-2 px-2">
                            <NavLink
                                href="#"
                                :active="route().current('stk.index')"
                                class="flex items-center dark:text-white p-2"
                            >
                                <GitPullRequest class="mr-2 h-4 w-4 text-purple-500" />
                                STK push
                            </NavLink>
                        </div>

                        <div class="mb-2 px-2">
                            <NavLink
                                href="#"
                                :active="route().current('Tportal.index')"
                                class="flex items-center dark:text-white p-2"
                            >
                                <Link2Icon class="mr-2 h-4 w-4 text-green-500" />
                                Tenant portal
                            </NavLink>
                        </div>
                    </div>

                </div>

                

            </nav>
        </aside>

        <!-- Overlay (mobile) -->
        <div
            v-if="sidebarOpen"
            @click="sidebarOpen = false"
            class="fixed inset-0 bg-black opacity-50 z-20 lg:hidden"
        ></div>

        <!-- Main Section -->
        <div
            class="flex flex-col flex-1 min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors duration-300"
        >
            <!-- Top Navbar -->
            <nav
                class="flex justify-between items-center px-4 py-3 shadow-sm bg-gray-300 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-700"
            >
                <div class="flex gap-4 font-extrabold items-left ml-2">
                    <button
                        @click="sidebarOpen = true"
                        class="lg:hidden text-gray-900 dark:text-white focus:outline-none"
                    >
                        <svg
                            class="h-6 w-6"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16"
                            />
                        </svg>
                    </button>

                    {{ $page.props.auth.user.name }}
                </div>
                <div class="flex ml-2">
                    <Dropdown align="right" width="48">
                        <template #trigger>
                            <button
                                type="button"
                                class="inline-flex items-center rounded-md px-3 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 dark:text-gray-200"
                            >
                                <Settings
                                    class="h-5 w-auto ml-1 text-gray-900 dark:text-white"
                                />
                            </button>
                        </template>

                        <template #content>
                            <DropdownLink
                                @click="
                                    theme = theme === 'dark' ? 'light' : 'dark'
                                "
                                class="p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center"
                            >
                                <SunIcon class="h-4 w-4 mr-2" />
                                Theme
                            </DropdownLink>

                            <DropdownLink
                                :href="route('profile.edit')"
                                class="flex"
                            >
                                <FolderEdit class="h-4 w-4 mr-2" />
                                Profile
                            </DropdownLink>

                            <DropdownLink
                                :href="route('logout')"
                                method="post"
                                as="button"
                                class="flex items-center"
                            >
                                <LogOut class="h-4 w-4 mr-2 text-red-600" />
                                Log Out
                            </DropdownLink>
                        </template>
                    </Dropdown>
                </div>
            </nav>

            <!-- Header -->
            <header
                v-if="$slots.header"
                class="bg-cyan-100 dark:bg-cyan-900 shadow border transition-colors duration-300"
            >
                <div
                    class="max-w-7xl mx-auto py-2 px-2 sm:px-4 lg:px-6 text-gray-900 dark:text-white"
                >
                    <slot name="header" />
                </div>
            </header>

            <!-- Main Content -->
            <main
                class="flex justify-center p-4  dark:bg-gray-900 rounded-xl transition-colors duration-300"
            >
                <slot />
            </main>
        </div>
    </div>
</template>
