<script setup lang="ts">
const props = defineProps<{ page: number; pageCount: number; rangeLabel: string }>()
const emit = defineEmits<{ 'update:page': [value: number] }>()

const go = (n: number) => emit('update:page', Math.min(Math.max(n, 1), props.pageCount))
</script>

<template>
  <div>
    <nav v-if="pageCount > 1" aria-label="Phân trang" class="mt-[44px] flex flex-wrap items-center justify-center gap-[8px]">
      <button
        type="button"
        aria-label="Trang trước"
        :disabled="page === 1"
        class="border-neutral-300 text-charcoal-900 hover:bg-charcoal-900 hover:border-charcoal-900 hover:text-gold-200 flex h-[44px] w-[44px] cursor-pointer items-center justify-center border bg-transparent transition disabled:cursor-default disabled:opacity-40 disabled:hover:bg-transparent disabled:hover:text-charcoal-900"
        @click="go(page - 1)"
      >
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m15 18-6-6 6-6"/></svg>
      </button>

      <button
        v-for="n in pageCount"
        :key="n"
        type="button"
        :aria-current="n === page ? 'page' : undefined"
        class="text-body h-[44px] min-w-[44px] cursor-pointer border px-[12px] font-bold transition"
        :class="n === page
          ? 'border-charcoal-900 bg-charcoal-900 text-gold-200'
          : 'border-neutral-300 bg-transparent text-charcoal-900 hover:border-charcoal-900 hover:bg-charcoal-900 hover:text-gold-200'"
        @click="go(n)"
      >{{ n }}</button>

      <button
        type="button"
        aria-label="Trang sau"
        :disabled="page === pageCount"
        class="border-neutral-300 text-charcoal-900 hover:bg-charcoal-900 hover:border-charcoal-900 hover:text-gold-200 flex h-[44px] w-[44px] cursor-pointer items-center justify-center border bg-transparent transition disabled:cursor-default disabled:opacity-40 disabled:hover:bg-transparent disabled:hover:text-charcoal-900"
        @click="go(page + 1)"
      >
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m9 18 6-6-6-6"/></svg>
      </button>
    </nav>

    <p class="text-caption text-text-muted m-0 mt-[16px] text-center tracking-[0.04em]">{{ rangeLabel }}</p>
  </div>
</template>
