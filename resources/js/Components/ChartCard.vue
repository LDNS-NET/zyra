<script setup>
import { computed } from 'vue';
import ApexCharts from 'vue3-apexcharts';

const props = defineProps({
    title: String,
    labels: Array,
    values: Array,
    type: {
        type: String,
        default: 'bar',
    },
});

const chartHeight = computed(() => {
    if (
        !props.values ||
        !props.values.length ||
        props.values.every((v) => v === 0 || v === null)
    ) {
        return 120;
    }
    // slightly shorter on mobile
    return window.innerWidth < 640 ? 220 : 280;
});

const options = computed(() => ({
    chart: {
        toolbar: { show: false },
        foreColor: '#64748B',
    },
    ...(props.type !== 'donut'
        ? {
              xaxis: {
                  categories: props.labels,
                  labels: {
                      style: {
                          colors: ['#6B7280'],
                      },
                  },
              },
          }
        : {}),
    ...(props.type === 'donut'
        ? {
              labels: props.labels,
          }
        : {}),
    dataLabels: { enabled: false },
    theme: {
        mode: document.documentElement.classList.contains('dark')
            ? 'dark'
            : 'light',
    },
    grid: {
        borderColor: '#E5E7EB',
    },
}));

const series = computed(() =>
    props.type === 'donut'
        ? props.values
        : [{ name: 'Data', data: props.values }],
);
</script>
<template>
    <div
        class="flex flex-col gap-3 rounded-2xl border border-cyan-100 bg-gradient-to-br from-green-100 via-white to-cyan-50 p-4 shadow-xl transition-all duration-300 hover:scale-[1.02] hover:shadow-2xl sm:p-6 dark:border-indigo-800 dark:from-gray-900 dark:via-gray-800 dark:to-indigo-900"
    >
        <div
            class="mb-3 flex flex-col items-start justify-between gap-2 sm:mb-4 sm:flex-row sm:items-center"
        >
            <h3
                class="w-full text-center text-base font-bold text-gray-700 sm:w-auto sm:text-left sm:text-lg dark:text-white"
            >
                {{ title }}
            </h3>
            <!-- optional tag (commented out)
      <span
        class="px-2 py-0.5 text-xs font-semibold uppercase bg-cyan-100 text-cyan-700 dark:bg-indigo-700 dark:text-white rounded shadow"
      >
        {{ type }}
      </span>
      -->
        </div>

        <div class="overflow-x-auto ">
            <apexchart
                :options="options"
                :series="series"
                :type="type"
                :height="chartHeight"
                class="min-w-[250px]"
            />
        </div>
    </div>
</template>