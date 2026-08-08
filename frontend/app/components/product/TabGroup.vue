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

/*
 * The heading each panel leads with.
 *
 * A tab button is not a heading, so the page used to go from its H1 straight
 * to "Sản phẩm liên quan" with the specifications and the Q&A unlabelled.
 * The editor's own heading wins on the description; elsewhere the tab label
 * already names the section.
 */
const headingFor = (key: string) =>
  (key === 'desc' && props.product.descHeading)
    ? props.product.descHeading
    : TABS.find(t => t.key === key)?.label ?? ''
</script>

<template>
  <section>
    <div role="tablist" class="flex overflow-x-auto border-b border-border">
      <button
        v-for="t in tabs"
        :key="t.key"
        type="button"
        role="tab"
        :id="`tab-${t.key}`"
        :aria-selected="active === t.key"
        :aria-controls="`panel-${t.key}`"
        class="text-body flex-shrink-0 cursor-pointer border-none border-b-2 bg-transparent px-6 py-4 font-bold tracking-[0.06em] uppercase transition"
        :class="active === t.key
          ? 'border-brass-500 text-brass-700'
          : 'border-transparent text-text-muted hover:text-text'"
        @click="active = t.key"
      >{{ t.label }}</button>
    </div>

    <!--
      Every panel is rendered, and the inactive ones are hidden rather than
      left out. Building only the open tab meant the specifications, the
      policies and the Q&A never reached the server-rendered page at all —
      a crawler saw the H1, the description, and nothing else, so the bulk
      of what each product says about itself was invisible.
    -->
    <div class="pt-9">
      <!-- Description -->
      <div
        v-show="active === 'desc'"
        id="panel-desc"
        role="tabpanel"
        aria-labelledby="tab-desc"
        :hidden="active !== 'desc'"
      >
        <h2 class="text-display m-0 mb-6 font-bold tracking-[-0.02em]">{{ headingFor('desc') }}</h2>
        <div class="grid gap-8 md:grid-cols-2">
        <div>
          <!-- Pre-rendered by Drupal's text format. -->
          <div
            v-if="product.description"
            class="text-body kb-prose leading-relaxed text-text-muted"
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
      </div>

      <!-- Specifications -->
      <div
        v-if="product.specifications.length"
        v-show="active === 'spec'"
        id="panel-spec"
        role="tabpanel"
        aria-labelledby="tab-spec"
        :hidden="active !== 'spec'"
      >
        <h2 class="text-display m-0 mb-6 font-bold tracking-[-0.02em]">{{ headingFor('spec') }}</h2>
        <ProductSpecTable :rows="product.specifications" />
      </div>

      <!-- Warranty / policy -->
      <div
        v-if="product.policyCards.length"
        v-show="active === 'warranty'"
        id="panel-warranty"
        role="tabpanel"
        aria-labelledby="tab-warranty"
        :hidden="active !== 'warranty'"
      >
        <h2 class="text-display m-0 mb-6 font-bold tracking-[-0.02em]">{{ headingFor('warranty') }}</h2>
        <div class="grid gap-5 md:grid-cols-2">
          <div
            v-for="card in product.policyCards"
            :key="card.title"
            class="flex flex-col gap-2 border border-border p-6"
          >
            <h3 class="text-caption text-brass-700 m-0 font-bold tracking-[0.1em] uppercase">
              {{ card.title }}
            </h3>
            <span class="text-body text-text-muted leading-relaxed">{{ card.desc }}</span>
          </div>
        </div>
      </div>

      <!-- FAQ -->
      <div
        v-if="product.faqs.length"
        v-show="active === 'faq'"
        id="panel-faq"
        role="tabpanel"
        aria-labelledby="tab-faq"
        :hidden="active !== 'faq'"
      >
        <h2 class="text-display m-0 mb-6 font-bold tracking-[-0.02em]">{{ headingFor('faq') }}</h2>
        <ProductFaqAccordion :faqs="product.faqs" />
      </div>
    </div>
  </section>
</template>
