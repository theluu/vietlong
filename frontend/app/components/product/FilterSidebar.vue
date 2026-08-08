<script setup lang="ts">
import type { Facets } from '~/types/product'

const { hotline: HOTLINE, hotlineTel: HOTLINE_TEL } = useSite()
defineProps<{
  facets: Facets
  category: string
  finish: string
  position: string
  faceid: boolean
  remoteApp: boolean
}>()

const emit = defineEmits<{
  (e: 'update', patch: {
    category?: string
    finish?: string
    position?: string
    faceid?: boolean
    remoteApp?: boolean
  }): void
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
    <!-- Categories. Hidden entirely when the axis is locked by the route,
         otherwise the heading renders above an empty list. -->
    <div v-if="entries(facets.category).length" class="border border-border">
      <div
        class="text-caption border-b border-border bg-surface px-5 py-[14px] font-bold tracking-[0.14em] uppercase"
      >
        Danh mục sản phẩm
      </div>
      <ProductCategoryTree
        :options="facets.category ?? {}"
        :selected="category"
        @select="emit('update', { category: $event })"
      />
    </div>

    <!-- Door position. A search aid, not a category: one lock answers to
         several of these, and picking one never hides the others as a
         product's home. -->
    <div v-if="entries(facets.position).length" class="border border-border">
      <div
        class="text-caption border-b border-border bg-surface px-5 py-[14px] font-bold tracking-[0.14em] uppercase"
      >
        Tìm theo nhu cầu
      </div>
      <div class="flex flex-col">
        <button
          v-for="[id, opt] in entries(facets.position)"
          :key="id"
          type="button"
          class="kb-filter-row flex cursor-pointer items-center justify-between gap-3 border-none bg-background px-5 py-[11px] text-left hover:bg-surface"
          :class="position === id ? 'text-brass-700 bg-surface font-bold' : 'text-text'"
          :aria-pressed="position === id"
          @click="emit('update', { position: position === id ? '' : id })"
        >
          <span class="text-body">{{ opt.label }}</span>
          <span class="text-caption text-text-muted">{{ opt.count }}</span>
        </button>
      </div>
    </div>

    <!-- The two features that vary between models. The four every smart lock
         has — vân tay, mã số, thẻ từ, chìa cơ — are not filters: they would
         narrow nothing. -->
    <div v-if="entries(facets.feature).length" class="border border-border">
      <div
        class="text-caption border-b border-border bg-surface px-5 py-[14px] font-bold tracking-[0.14em] uppercase"
      >
        Tính năng nổi bật
      </div>
      <div class="flex flex-col">
        <button
          v-for="[key, opt] in entries(facets.feature)"
          :key="key"
          type="button"
          class="kb-filter-row flex cursor-pointer items-center justify-between gap-3 border-none bg-background px-5 py-[11px] text-left hover:bg-surface"
          :class="(key === 'faceid' ? faceid : remoteApp) ? 'text-brass-700 bg-surface font-bold' : 'text-text'"
          :aria-pressed="key === 'faceid' ? faceid : remoteApp"
          :disabled="!opt.count && !(key === 'faceid' ? faceid : remoteApp)"
          @click="key === 'faceid'
            ? emit('update', { faceid: !faceid })
            : emit('update', { remoteApp: !remoteApp })"
        >
          <span class="text-body">{{ opt.label }}</span>
          <span class="text-caption text-text-muted">{{ opt.count }}</span>
        </button>
      </div>
    </div>

    <!-- Finishes -->
    <div v-if="entries(facets.finish).length" class="border border-border">
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

<style scoped>
.kb-filter-row[aria-pressed='true'] {
  box-shadow: inset 3px 0 0 0 var(--color-brass-700);
}

.kb-filter-row:disabled {
  cursor: default;
  opacity: 0.42;
}

.kb-filter-row:focus-visible {
  outline: 2px solid var(--color-brass-700);
  outline-offset: -2px;
}
</style>
