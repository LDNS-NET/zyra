<script setup>
defineProps({
    value: {
        type: String,
    },
});
</script>

<template>
    <label
        class="block bg-gradient-to-r from-black via-blue-600 to-green-400 bg-clip-text text-base font-semibold text-transparent transition-all duration-200 dark:text-white"
    >
        <span v-if="value">{{ value }}</span>
        <span v-else><slot /></span>
    </label>
</template>
