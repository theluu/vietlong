<script setup lang="ts">
import { fetchArticle, fetchArticles, fetchNews } from '~/services/pages'

/**
 * The flagship guide carries `field_sort_order = 99`, which the serializer
 * filters out of the listing — so it has no route in from the UI unless the
 * page fetches it by slug. If an editor renames it the block disappears
 * rather than breaking the page.
 */
const FEATURED_SLUG = 'nen-chon-khoa-van-tay-nao-cho-cua-go'

const { data } = await useAsyncData('page:news', () => fetchNews())
const { data: articleData } = await useAsyncData('articles', () => fetchArticles())
const { data: featuredData } = await useAsyncData(
  `article:${FEATURED_SLUG}`,
  () => fetchArticle(FEATURED_SLUG).catch(() => null),
)

const page = computed(() => data.value?.data)
const articles = computed(() => articleData.value?.data ?? [])
const featured = computed(() => featuredData.value?.data ?? null)

if (!page.value) {
  throw createError({ statusCode: 404, statusMessage: 'Không tìm thấy trang', fatal: true })
}

const filters = [
  { key: 'all', label: 'Tất cả' },
  { key: 'guide', label: 'Chọn khóa' },
  { key: 'compare', label: 'So sánh' },
  { key: 'howto', label: 'Hướng dẫn' },
  { key: 'faq', label: 'FAQ' },
] as const
const active = ref<(typeof filters)[number]['key']>('all')
const currentPage = ref(1)
const perPage = 6
const filtered = computed(() => active.value === 'all'
  ? articles.value
  : articles.value.filter(article => article.categoryKey === active.value))
const pageCount = computed(() => Math.max(1, Math.ceil(filtered.value.length / perPage)))
const visible = computed(() => filtered.value.slice((currentPage.value - 1) * perPage, currentPage.value * perPage))
const rangeLabel = computed(() => {
  const start = filtered.value.length ? (currentPage.value - 1) * perPage + 1 : 0
  return `Hiển thị ${start}–${start + visible.value.length - (visible.value.length ? 1 : 0)} trong ${filtered.value.length} mục`
})

function setFilter(key: (typeof filters)[number]['key']) {
  active.value = key
  currentPage.value = 1
}

useSeoMeta({
  title: () => `${page.value?.title} | Keybolts`,
  description: () => page.value?.subtitle,
})
useHead({ link: [{ rel: 'canonical', href: 'https://keybolts.com.vn/tin-tuc' }] })
</script>

<template>
  <div v-if="page">
    <PageCenteredHero :eyebrow="page.eyebrow" :title="page.title" :subtitle="page.subtitle" />

    <section class="bg-background py-[clamp(40px,4.5vw,64px)]">
      <div class="mx-auto max-w-[var(--container-max)] px-[clamp(20px,4vw,48px)]">
        <PageFeaturedArticle v-if="featured" :article="featured" />

        <div class="mb-[32px] flex flex-wrap gap-[10px]">
          <button
            v-for="filter in filters"
            :key="filter.key"
            type="button"
            class="text-caption cursor-pointer rounded-sm border px-[22px] py-[12px] font-bold tracking-[0.08em] uppercase transition duration-200 ease-in-out"
            :class="filter.key === active
              ? 'border-charcoal-900 bg-charcoal-900 text-gold-200'
              : 'border-border bg-background text-charcoal-900 hover:border-brass-500'"
            @click="setFilter(filter.key)"
          >{{ filter.label }}</button>
        </div>

        <div class="kb-article-related-grid gap-[clamp(16px,1.8vw,26px)]">
          <PageNewsCard v-for="article in visible" :key="article.id" :article="article" />
        </div>

        <PagePager
          :page="currentPage"
          :page-count="pageCount"
          :range-label="rangeLabel"
          @update:page="currentPage = $event"
        />
      </div>
    </section>
  </div>
</template>
