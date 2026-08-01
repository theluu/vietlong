<script setup lang="ts">
import type { ProductDetail } from '~/types/product'

const props = defineProps<{ product: ProductDetail }>()

const TABS = [
  { key: 'desc', label: 'Mô tả sản phẩm' },
  { key: 'spec', label: 'Thông số kỹ thuật' },
  { key: 'warranty', label: 'Bảo hành & chính sách' },
  { key: 'faq', label: 'Hỏi đáp' },
] as const

const active = ref<string>('desc')
const openFaq = ref<number | null>(0)

// Tabs with nothing behind them are worse than no tab at all.
const tabs = computed(() => TABS.filter((t) => {
  if (t.key === 'spec') return props.product.specifications.length > 0
  if (t.key === 'warranty') return props.product.policyCards.length > 0
  if (t.key === 'faq') return props.product.faqs.length > 0
  return true
}))

watch(tabs, (list) => {
  if (!list.some(t => t.key === active.value)) active.value = list[0]?.key ?? 'desc'
}, { immediate: true })
</script>

<template>
  <section>
    <div role="tablist" class="flex overflow-x-auto border-b border-border">
      <button
        v-for="t in tabs"
        :key="t.key"
        type="button"
        role="tab"
        :aria-selected="active === t.key"
        class="text-body flex-shrink-0 cursor-pointer border-none border-b-2 bg-transparent px-6 py-4 font-bold tracking-[0.06em] uppercase transition"
        :class="active === t.key
          ? 'border-brass-500 text-brass-700'
          : 'border-transparent text-text-muted hover:text-text'"
        @click="active = t.key"
      >{{ t.label }}</button>
    </div>

    <div class="pt-9">
      <!-- Description -->
      <div v-if="active === 'desc'" class="grid gap-8 md:grid-cols-2">
        <div>
          <h2 v-if="product.descHeading" class="text-display m-0 font-bold tracking-[-0.02em]">
            {{ product.descHeading }}
          </h2>
          <!-- Pre-rendered by Drupal's text format. -->
          <div
            v-if="product.description"
            class="text-body kb-prose mt-4 leading-relaxed text-text-muted"
            v-html="product.description"
          />
        </div>

        <ul v-if="product.highlights.length" class="m-0 flex list-none flex-col gap-3 p-0">
          <li
            v-for="hl in product.highlights"
            :key="hl"
            class="text-body flex gap-3 border-b border-border pb-3 leading-relaxed"
          >
            <span class="text-brass-700 font-bold">✓</span>{{ hl }}
          </li>
        </ul>
      </div>

      <!-- Specifications -->
      <ProductSpecTable v-else-if="active === 'spec'" :rows="product.specifications" />

      <!-- Warranty / policy -->
      <div v-else-if="active === 'warranty'" class="grid gap-5 md:grid-cols-2">
        <div
          v-for="card in product.policyCards"
          :key="card.title"
          class="flex flex-col gap-2 border border-border p-6"
        >
          <span class="text-caption text-brass-700 font-bold tracking-[0.1em] uppercase">
            {{ card.title }}
          </span>
          <span class="text-body text-text-muted leading-relaxed">{{ card.desc }}</span>
        </div>
      </div>

      <!-- FAQ -->
      <ProductFaqAccordion v-else-if="active === 'faq'" :faqs="product.faqs" />
    </div>
  </section>
</template>
