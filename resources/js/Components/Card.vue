<template>
    <div
        class="flex w-full max-w-full flex-col justify-between gap-3 rounded-2xl border border-gray-200 bg-gradient-to-br from-slate-50 via-white to-slate-100 p-5 shadow-md transition-all duration-300 hover:scale-[1.02] hover:shadow-lg sm:p-6 dark:border-gray-700 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900"
    >
        <div class="mb-1 flex items-center gap-3">
            <component
                v-if="icon"
                :is="icon"
                class="h-6 w-6 text-blue-600 sm:h-7 sm:w-7 dark:text-blue-400"
            />
            <span
                class="text-sm font-semibold text-gray-700 sm:text-base dark:text-gray-100"
            >
                {{ title }}
            </span>
        </div>

        <div
            class="text-2xl font-extrabold tracking-tight text-gray-900 sm:text-3xl dark:text-white"
        >
            <slot name="value">
                {{ value }}
            </slot>
        </div>

        <div
            v-if="subtitle"
            class="mt-1 text-xs font-medium text-gray-500 sm:text-sm dark:text-gray-400"
        >
            {{ subtitle }}
        </div>
    </div>
</template>

<script setup>
defineProps({
    title: String,
    value: [String, Number],
    subtitle: String,
    icon: [Object, Function], // optional icon component
});
</script>

<style scoped>
/* Optional: Add subtle responsive font scaling */
@media (max-width: 640px) {
    div {
        font-size: 0.95rem;
    }
}
</style>
