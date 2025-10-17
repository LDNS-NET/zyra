<script setup>
defineProps({
    id: String,
    modelValue: String,
});
</script>
<template>
    <div>
        <textarea
            :id="id"
            :value="modelValue"
            @input="$emit('update:modelValue', $event.target.value)"
            class="mt-1 block w-full rounded-lg border border-blue-600 dark:bg-black"
            rows="4"
        ></textarea>
    </div>
</template>
