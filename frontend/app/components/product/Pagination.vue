<script setup lang="ts">
const props = defineProps<{ page: number; total: number; perPage: number }>()
const emit = defineEmits<{ (e: 'go', page: number): void }>()

const pageCount = computed(() => Math.max(1, Math.ceil(props.total / props.perPage)))
const pages = computed(() => Array.from({ length: pageCount.value }, (_, i) => i + 1))
const show = computed(() => pageCount.value > 1)
</script>

<template>
  <nav v-if="show" aria-label="Phân trang" class="flex flex-wrap items-center gap-2">
    <button
      type="button"
      class="text-body cursor-pointer rounded-sm border border-border bg-background px-4 py-2 disabled:opacity-40"
      :disabled="page <= 1"
      @click="emit('go', page - 1)"
    >←</button>

    <button
      v-for="p in pages"
      :key="p"
      type="button"
      class="text-body min-w-10 cursor-pointer rounded-sm border px-4 py-2"
      :class="p === page
        ? 'bg-charcoal-900 text-gold-200 border-charcoal-900 font-bold'
        : 'bg-background text-text border-border hover:border-brass-500'"
      :aria-current="p === page ? 'page' : undefined"
      @click="emit('go', p)"
    >{{ p }}</button>

    <button
      type="button"
      class="text-body cursor-pointer rounded-sm border border-border bg-background px-4 py-2 disabled:opacity-40"
      :disabled="page >= pageCount"
      @click="emit('go', page + 1)"
    >→</button>
  </nav>
</template>
