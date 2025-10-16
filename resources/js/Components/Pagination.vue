<script setup>
import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  links: Array,
  perPage: {
    type: Number,
    default: 10,
  },
  baseUrl: {
    type: String,
    required: false,
    default: '',
  },
})

const perPageOptions = [10, 25, 50, 100]
const selectedPerPage = ref(props.perPage)

// Watch for changes in selected per-page
watch(selectedPerPage, (value, oldValue) => {
  // Prevent doing anything if the value hasn’t really changed
  if (value === oldValue) return

  const url = new URL(window.location.href)
  url.searchParams.set('per_page', value)
  url.searchParams.set('page', 1) // reset to first page always

  // Force a refresh even if URL looks same (e.g. 10 → 10)
  router.visit(url.toString(), {
    preserveScroll: true,
    preserveState: false, // force reload table data
    replace: true,        // don't clutter browser history
  })
})
</script>

<template>
  <div class="flex flex-col sm:flex-row justify-between items-center mt-6 gap-4">
    <!-- Per page selector -->
    <div class="flex items-center gap-2">
      <label
        for="perPage"
        class="text-sm font-medium text-gray-700 dark:text-gray-300"
      >
        Per Page:
      </label>
      
      <select
        id="perPage"
        v-model="selectedPerPage"
        class="rounded-md border-gray-300 dark:border-gray-700 
               bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200
               focus:border-blue-500 focus:ring-blue-500 text-sm px-auto py-auto shadow-sm"
      >
      
        <option v-for="option in perPageOptions" :key="option" :value="option">
          {{ option }}
        </option>
        
      </select>
      
    </div>

    <!-- Pagination links -->
    <nav v-if="links.length > 3" class="flex justify-center" aria-label="Pagination">
      <ul
        class="inline-flex items-center gap-1 rounded-lg shadow-sm 
               bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-1"
      >
        <li v-for="(link, key) in links" :key="key">
          <span
            v-if="!link.url"
            class="px-3 py-2 min-w-[36px] flex justify-center rounded-md 
                   text-gray-400 dark:text-gray-500 text-sm cursor-not-allowed select-none"
            v-html="link.label"
          />
          <a
            v-else
            :href="link.url"
            class="px-3 py-2 min-w-[36px] flex justify-center rounded-md text-sm font-medium transition-all duration-150"
            :class="{
              'bg-blue-600 text-white shadow-sm hover:bg-blue-700': link.active,
              'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700':
                !link.active,
            }"
            v-html="link.label"
          />
        </li>
      </ul>
    </nav>
  </div>
</template>
