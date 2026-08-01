<script setup lang="ts">
import type { ProductCard } from '~/types/product'

const props = defineProps<{ product: ProductCard }>()

// The prototype gives each brand its own chip colours.
const brandChip = computed(() => {
  const name = props.product.brand?.name?.toUpperCase()
  if (name === 'BALTICA') return 'bg-brass-700 text-white'
  return 'bg-charcoal-900 text-gold-200'
})
</script>

<template>
  <NuxtLink
    :to="`/${product.slug}`"
    :data-product-name="product.name"
    class="group flex flex-col border border-border bg-background text-inherit no-underline transition hover:-translate-y-1 hover:border-brass-500 hover:shadow-floating"
  >
    <div class="relative aspect-square overflow-hidden border-b border-border bg-white">
      <img
        v-if="product.image"
        :src="product.image.url"
        :alt="product.image.alt || product.name"
        class="h-full w-full object-contain p-4 transition-transform duration-500 group-hover:scale-105"
        loading="lazy"
      >
      <span
        v-if="product.badge"
        class="absolute top-3 left-3 bg-charcoal-900 px-[11px] py-[5px] text-[10px] font-bold tracking-[0.14em] text-gold-200 uppercase"
      >{{ product.badge }}</span>
    </div>

    <div class="flex flex-1 flex-col gap-[7px] p-[18px]">
      <div class="flex flex-wrap items-center gap-2">
        <span
          v-if="product.brand"
          class="px-2 py-[3px] text-[9px] font-bold tracking-[0.16em] uppercase"
          :class="brandChip"
        >{{ product.brand.name }}</span>
        <span
          v-if="product.category"
          class="text-brass-700 text-[10px] font-bold tracking-[0.14em] uppercase"
        >{{ product.category.name }}</span>
      </div>

      <span class="text-heading font-bold text-text">{{ product.name }}</span>
      <span class="text-caption text-text-muted">{{ product.model }}</span>

      <span
        v-if="product.finish"
        class="text-caption text-text-muted mt-auto flex items-center gap-2 pt-2"
      >
        <span
          v-if="product.finish.swatch"
          class="h-[11px] w-[11px] rounded-full kb-swatch-ring"
          :style="{ background: product.finish.swatch }"
        />
        {{ product.finish.name }}
      </span>

      <span class="text-caption text-brass-700 mt-2 font-bold">Xem chi tiết →</span>
    </div>
  </NuxtLink>
</template>
