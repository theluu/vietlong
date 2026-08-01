<script setup lang="ts">
import type { ProductCard } from '~/types/product'
import { FEATURED_TABS } from '~/utils/homeContent'

const props = defineProps<{ featured: Record<string, ProductCard[]> }>()

// Every group ships in the one /homepage response, so switching is instant.
const tabs = computed(() => FEATURED_TABS.filter(t => (props.featured[t.key]?.length ?? 0) > 0))
const active = ref(tabs.value[0]?.key ?? 'dong')
const items = computed(() => props.featured[active.value] ?? [])
</script>

<template>
  <section class="bg-surface">
    <div class="mx-auto max-w-[var(--container-max)] px-[clamp(20px,4vw,48px)] py-16">
      <span class="text-eyebrow text-brass-700 font-bold tracking-[0.24em] uppercase">Bán chạy</span>
      <h2 class="text-display-lg mt-3 mb-8 font-bold tracking-[-0.03em]">Sản phẩm nổi bật</h2>

      <div role="tablist" class="mb-8 flex flex-wrap gap-3">
        <button
          v-for="t in tabs"
          :key="t.key"
          type="button"
          role="tab"
          :aria-selected="active === t.key"
          :data-tab="t.key"
          class="text-caption cursor-pointer rounded-sm border px-5 py-3 font-bold tracking-[0.06em] uppercase transition"
          :class="active === t.key
            ? 'bg-charcoal-900 text-gold-200 border-charcoal-900'
            : 'bg-background text-text-muted border-border hover:border-brass-500'"
          @click="active = t.key"
        >{{ t.label }}</button>
      </div>

      <div class="kb-related-grid gap-[clamp(16px,1.6vw,24px)]">
        <ProductCard v-for="p in items" :key="p.id" :product="p" />
      </div>
    </div>
  </section>
</template>
