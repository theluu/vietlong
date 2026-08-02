<script setup lang="ts">
import { fetchArticles, fetchNews } from '~/services/pages'

const { data } = await useAsyncData('page:news', () => fetchNews())
const { data: articleData } = await useAsyncData('articles', () => fetchArticles())
const page = computed(() => data.value?.data)
const articles = computed(() => articleData.value?.data ?? [])

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
    <PageHero
      :eyebrow="page.eyebrow"
      :title="page.title"
      :subtitle="page.subtitle"
      :breadcrumb="[
        { label: 'Trang chủ', url: '/' },
        { label: 'Tin tức', url: '/tin-tuc' },
      ]"
    />

    <main class="mx-auto max-w-[var(--container-max)] px-[clamp(20px,4vw,48px)] py-12">
      <div class="mb-10 flex flex-wrap gap-2 border-b border-border pb-5">
        <button
          v-for="filter in filters"
          :key="filter.key"
          type="button"
          class="text-body cursor-pointer rounded-sm border px-5 py-2.5 font-bold transition"
          :class="filter.key === active
            ? 'border-charcoal-900 bg-charcoal-900 text-gold-200'
            : 'border-border bg-background text-text hover:border-brass-500'"
          @click="setFilter(filter.key)"
        >{{ filter.label }}</button>
      </div>

      <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
        <PageNewsCard v-for="article in visible" :key="article.id" :article="article" />
      </div>

      <div class="mt-10 flex flex-col gap-4 border-t border-border pt-6 sm:flex-row sm:items-center sm:justify-between">
        <span class="text-caption text-text-muted">{{ rangeLabel }}</span>
        <nav v-if="pageCount > 1" aria-label="Phân trang" class="flex gap-2">
          <button
            v-for="number in pageCount"
            :key="number"
            type="button"
            class="text-body h-10 w-10 cursor-pointer border font-bold"
            :class="number === currentPage
              ? 'border-charcoal-900 bg-charcoal-900 text-gold-200'
              : 'border-border bg-background text-text'"
            @click="currentPage = number"
          >{{ number }}</button>
        </nav>
      </div>
    </main>
  </div>
</template>
