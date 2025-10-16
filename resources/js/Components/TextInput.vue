<script setup>
import { onMounted, ref } from 'vue'

const model = defineModel({
  type: [String, Number],
  required: true,
})

const input = ref(null)

onMounted(() => {
  if (input.value?.hasAttribute('autofocus')) {
    input.value.focus()
  }
})

defineExpose({
  focus: () => input.value?.focus(),
})
</script>

<template>
  <input
    v-model="model"
    ref="input"
    class="block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-4 py-2 text-base placeholder-gray-400 dark:placeholder-gray-500 focus:border-blue-500 focus:ring focus:ring-blue-400/40 focus:outline-none transition-all duration-200 shadow-sm"
  />
</template>
