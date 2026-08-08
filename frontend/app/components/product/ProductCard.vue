<script setup lang="ts">
import type { ProductCard } from '~/types/product'

const props = defineProps<{ product: ProductCard }>()

// The prototype gives each brand its own chip colours.
const brandChip = computed(() => {
  const name = props.product.brand?.name?.toUpperCase()
  if (name === 'BALTICA') return 'bg-brass-700 text-white'
  return 'bg-charcoal-900 text-gold-200'
})

/*
 * Every smart lock opens four ways, so the card states them as a fact of the
 * group rather than a per-product claim. They are deliberately not filters
 * and not categories — narrowing by something all of them share tells the
 * customer nothing.
 */
const BASE_FEATURES = ['Vân tay', 'Mã số', 'Thẻ từ', 'Chìa cơ']

/*
 * What actually differs between models, which is the whole job of the badge:
 * let someone see the difference in a couple of seconds without reading the
 * name. Absent when the product has neither.
 */
const featureBadges = computed(() => {
  const out: string[] = []
  if (props.product.faceid) out.push('FaceID')
  if (props.product.remoteApp) out.push('Mở từ xa')
  return out
})
</script>

<template>
  <NuxtLink
    :to="`/${product.slug}`"
    :data-product-name="product.name"
    class="group flex flex-col border border-border bg-background text-inherit no-underline transition hover:-translate-y-1 hover:border-brass-500 hover:shadow-floating"
  >
    <div class="relative aspect-square overflow-hidden border-b border-border bg-white">
      <UiResponsiveImage
        :image="product.image"
        :alt="product.image?.alt || product.name"
        sizes="(min-width: 1024px) 300px, 50vw"
        class="h-full w-full object-contain p-4 transition-transform duration-500 group-hover:scale-105"
      />
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

      <ul
        v-if="product.smartLock"
        class="m-0 mt-1 flex list-none flex-wrap gap-x-[10px] gap-y-[3px] p-0"
      >
        <li
          v-for="f in BASE_FEATURES"
          :key="f"
          class="text-caption text-text-muted flex items-center gap-[4px]"
        >
          <svg
            width="10"
            height="10"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="3.2"
            class="text-brass-500 flex-none"
            aria-hidden="true"
          ><path d="m5 13 4 4L19 7" /></svg>
          {{ f }}
        </li>
      </ul>

      <div v-if="featureBadges.length" class="mt-1 flex flex-wrap gap-[6px]">
        <span
          v-for="b in featureBadges"
          :key="b"
          class="border border-brass-500 px-[8px] py-[2px] text-[10px] font-bold tracking-[0.1em] text-brass-700 uppercase"
        >{{ b }}</span>
      </div>

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
