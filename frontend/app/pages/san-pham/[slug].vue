<script setup lang="ts">
import { fetchProduct } from '~/services/products'

const route = useRoute()
const slug = computed(() => String(route.params.slug))

const { data, error } = await useAsyncData(
  () => `product:${slug.value}`,
  () => fetchProduct(slug.value),
  { watch: [slug] },
)

const product = computed(() => data.value?.data)

if (error.value || !product.value) {
  throw createError({ statusCode: 404, statusMessage: 'Không tìm thấy sản phẩm', fatal: true })
}

const variants = computed(() => product.value!.variants)

// The matrix marks the current node's own axis values; fall back to the
// product's own finish so the swatch is still highlighted for a lone variant.
const currentFinishKey = computed(
  () => variants.value.finishes.find(f => f.slug === product.value!.slug)?.key ?? '',
)
const currentSizeKey = computed(
  () => variants.value.sizes.find(s => s.slug === product.value!.slug)?.key ?? '',
)
const activeFinishLabel = computed(
  () => product.value!.finish?.name
    ?? variants.value.finishes.find(f => f.key === currentFinishKey.value)?.label
    ?? '',
)

useSeoMeta({
  title: () => `${product.value?.name} ${product.value?.model} | Keybolts`,
  description: () => product.value?.shortDesc,
  ogImage: () => product.value?.image?.url,
  ogType: 'website',
})

useHead(() => ({
  script: [
    {
      type: 'application/ld+json',
      innerHTML: JSON.stringify(product.value?.jsonLd ?? {}),
    },
  ],
}))
</script>

<template>
  <div v-if="product">
    <div class="mx-auto max-w-[var(--container-max)] px-[clamp(20px,4vw,48px)] py-6">
      <LayoutBreadcrumb :items="product.breadcrumb" />
    </div>

    <!-- Gallery + buy column -->
    <div
      class="mx-auto grid max-w-[var(--container-max)] gap-10 px-[clamp(20px,4vw,48px)] pb-12 lg:grid-cols-2"
    >
      <ProductGallery :images="product.images" :name="product.name" />

      <div class="flex flex-col gap-6">
        <div class="flex flex-col gap-3">
          <span
            v-if="product.category"
            class="text-eyebrow text-brass-700 font-bold tracking-[0.2em] uppercase"
          >{{ product.category.name }}</span>

          <h1 class="text-display-lg m-0 font-bold tracking-[-0.03em]">{{ product.name }}</h1>

          <p class="text-body text-text-muted m-0">
            Mã sản phẩm: <span class="font-bold text-text">{{ product.model }}</span>
          </p>

          <p v-if="product.shortDesc" class="text-heading text-text-muted m-0 font-light leading-relaxed">
            {{ product.shortDesc }}
          </p>

          <div class="flex flex-wrap items-center gap-3">
            <span
              v-if="product.stockStatus"
              class="text-caption bg-success/12 text-success rounded-sm px-3 py-1 font-bold"
            >{{ product.stockStatus }}</span>
            <span
              v-for="tc in product.certification"
              :key="tc"
              class="text-caption rounded-sm border border-border px-3 py-1 text-text-muted"
            >{{ tc }}</span>
          </div>
        </div>

        <!-- Price -->
        <div class="flex flex-col gap-2 border border-border bg-surface p-5">
          <span class="text-caption text-text-muted tracking-[0.1em] uppercase">Giá bán</span>
          <span class="text-display text-brass-700 font-bold">
            {{ product.contactPrice ? 'Liên hệ' : 'Liên hệ' }}
          </span>
          <span class="text-caption text-text-muted leading-relaxed">
            Giá thay đổi theo size, hoàn thiện và số lượng — gọi để nhận báo giá chính xác.
          </span>
        </div>

        <!-- Variants -->
        <div v-if="variants.finishes.length" class="flex flex-col gap-3">
          <span class="text-caption font-bold tracking-[0.1em] uppercase">
            Hoàn thiện · <span class="text-brass-700">{{ activeFinishLabel }}</span>
          </span>
          <ProductVariantSelector
            :options="variants.finishes"
            :current-key="currentFinishKey"
            kind="finish"
          />
        </div>

        <div v-if="variants.sizes.length" class="flex flex-col gap-3">
          <span class="text-caption font-bold tracking-[0.1em] uppercase">Kích thước bộ khóa</span>
          <ProductVariantSelector
            :options="variants.sizes"
            :current-key="currentSizeKey"
            kind="size"
          />
        </div>

        <!-- CTAs -->
        <div class="flex flex-wrap gap-4">
          <a
            :href="HOTLINE_TEL"
            class="text-body rounded-sm bg-charcoal-900 px-8 py-4 font-bold tracking-[0.06em] text-white uppercase no-underline hover:bg-brass-700"
          >Gọi {{ HOTLINE }}</a>
          <NuxtLink
            to="/lien-he"
            class="text-body rounded-sm bg-gold-200 px-8 py-4 font-bold tracking-[0.06em] text-charcoal-900 uppercase no-underline hover:bg-white"
          >Nhận báo giá</NuxtLink>
        </div>

        <ProductAssuranceList />

        <dl v-if="product.warranty || product.doorThickness || product.origin" class="m-0 grid gap-3">
          <div v-if="product.doorThickness" class="text-caption flex gap-2">
            <dt class="text-text-muted">Độ dày cửa:</dt>
            <dd class="m-0 font-bold">{{ product.doorThickness }}</dd>
          </div>
          <div v-if="product.warranty" class="text-caption flex gap-2">
            <dt class="text-text-muted">Bảo hành:</dt>
            <dd class="m-0 font-bold">{{ product.warranty }}</dd>
          </div>
          <div v-if="product.origin" class="text-caption flex gap-2">
            <dt class="text-text-muted">Xuất xứ:</dt>
            <dd class="m-0 font-bold">{{ product.origin }}</dd>
          </div>
        </dl>
      </div>
    </div>

    <!-- Tabs -->
    <div class="border-t border-border bg-background">
      <div class="mx-auto max-w-[var(--container-max)] px-[clamp(20px,4vw,48px)] py-14">
        <ProductTabGroup :product="product" />
      </div>
    </div>

    <!-- Related -->
    <div v-if="product.related.length" class="border-t border-border bg-surface">
      <div class="mx-auto max-w-[var(--container-max)] px-[clamp(20px,4vw,48px)] py-14">
        <span class="text-eyebrow text-brass-700 font-bold tracking-[0.24em] uppercase">
          Cùng bộ sưu tập
        </span>
        <h2 class="text-display mt-3 mb-8 font-bold">Sản phẩm liên quan</h2>
        <div class="kb-related-grid gap-[clamp(16px,1.6vw,24px)]">
          <ProductCard v-for="r in product.related" :key="r.id" :product="r" />
        </div>
      </div>
    </div>
  </div>
</template>
