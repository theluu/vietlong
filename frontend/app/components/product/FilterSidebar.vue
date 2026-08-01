<script setup lang="ts">
import type { Facets } from '~/types/product'

defineProps<{
  facets: Facets
  category: string
  finish: string
}>()

const emit = defineEmits<{
  (e: 'update', patch: { category?: string; finish?: string }): void
}>()

/**
 * Counts come from the API with each axis counted as if its own filter were
 * absent, so these numbers answer "what would I get if I picked this instead".
 */
const entries = (axis: Record<string, { label: string; count: number; swatch?: string }> | undefined) =>
  Object.entries(axis ?? {})
</script>

<template>
  <aside class="flex flex-col gap-5">
    <!-- Categories -->
    <div class="border border-border">
      <div
        class="text-caption border-b border-border bg-surface px-5 py-[14px] font-bold tracking-[0.14em] uppercase"
      >
        Danh mục sản phẩm
      </div>
      <div class="flex flex-col">
        <button
          type="button"
          class="flex cursor-pointer items-center justify-between border-none bg-background px-5 py-3 text-left hover:bg-surface"
          :class="category === '' ? 'text-brass-700 font-bold bg-surface' : 'text-text'"
          @click="emit('update', { category: '' })"
        >
          <span class="text-body">Tất cả sản phẩm</span>
        </button>
        <button
          v-for="[id, opt] in entries(facets.category)"
          :key="id"
          type="button"
          class="flex cursor-pointer items-center justify-between gap-3 border-none bg-background px-5 py-3 text-left hover:bg-surface"
          :class="category === id ? 'text-brass-700 font-bold bg-surface' : 'text-text'"
          @click="emit('update', { category: category === id ? '' : id })"
        >
          <span class="text-body">{{ opt.label }}</span>
          <span class="text-caption text-text-muted">{{ opt.count }}</span>
        </button>
      </div>
    </div>

    <!-- Finishes -->
    <div class="border border-border">
      <div
        class="text-caption border-b border-border bg-surface px-5 py-[14px] font-bold tracking-[0.14em] uppercase"
      >
        Hoàn thiện
      </div>
      <div class="flex flex-wrap gap-2 px-5 py-[18px]">
        <button
          v-for="[id, opt] in entries(facets.finish)"
          :key="id"
          type="button"
          class="text-caption flex cursor-pointer items-center gap-2 rounded-sm border px-[14px] py-2 transition"
          :class="finish === id
            ? 'bg-charcoal-900 text-gold-200 border-charcoal-900'
            : 'bg-background text-text-muted border-border hover:border-brass-500'"
          @click="emit('update', { finish: finish === id ? '' : id })"
        >
          <span
            class="h-[11px] w-[11px] rounded-full kb-swatch-ring"
            :style="{ background: opt.swatch || '#ccc' }"
          />
          {{ opt.label }}
          <span class="opacity-60">{{ opt.count }}</span>
        </button>
      </div>
    </div>

    <!-- Technical advice CTA -->
    <div class="relative flex flex-col gap-[14px] overflow-hidden bg-charcoal-900 px-[22px] py-[26px] text-white">
      <span class="text-caption text-gold-200 font-bold tracking-[0.18em] uppercase">
        Tư vấn kỹ thuật
      </span>
      <p class="text-body m-0 leading-relaxed text-white/72">
        Gửi kích thước và loại cửa, chúng tôi chọn đúng model &amp; báo giá theo số lượng.
      </p>
      <a
        :href="HOTLINE_TEL"
        class="text-body inline-flex items-center justify-center gap-[9px] rounded-sm bg-gold-200 px-5 py-[13px] font-bold tracking-[0.04em] text-charcoal-900 no-underline hover:bg-white"
      >{{ HOTLINE }}</a>
    </div>
  </aside>
</template>
