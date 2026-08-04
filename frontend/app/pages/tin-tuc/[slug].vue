<script setup lang="ts">
import type { RelatedItem } from '~/types/page'
import { fetchArticle, fetchArticles } from '~/services/pages'
const { hotline: HOTLINE, hotlineTel: HOTLINE_TEL } = useSite()

const slug = String(useRoute().params.slug)
const { data, error } = await useAsyncData(`article:${slug}`, () => fetchArticle(slug))
if (error.value || !data.value?.data) {
  throw createError({ statusCode: 404, statusMessage: 'Không tìm thấy bài viết', fatal: true })
}
const article = computed(() => data.value!.data)

const { data: listData } = await useAsyncData('articles', () => fetchArticles())

const COMPARE_ID = 'bang-so-sanh'
const FAQ_ID = 'cau-hoi'

/** The sidebar index covers the body sections plus the two fixed blocks below them. */
const toc = computed(() => {
  const entries = article.value.sections.map(section => ({
    id: section.id,
    label: section.title.replace(/^\d+\.\s*/, ''),
  }))
  if (article.value.compareRows.length) entries.push({ id: COMPARE_ID, label: 'Bảng so sánh theo loại cửa' })
  if (article.value.faqs.length) entries.push({ id: FAQ_ID, label: 'Câu hỏi thường gặp' })
  return entries.map((entry, i) => ({ ...entry, number: String(i + 1).padStart(2, '0') }))
})

const related = computed<RelatedItem[]>(() =>
  (listData.value?.data ?? [])
    .filter(item => item.slug !== article.value.slug)
    .slice(0, 4)
    .map(item => ({
      key: item.id,
      to: `/tin-tuc/${item.slug}`,
      image: item.image,
      badge: item.category,
      title: item.title,
      summary: item.summary,
      meta: item.readTime,
    })),
)

useSeoMeta({
  title: () => `${article.value.title} | Keybolts`,
  description: () => article.value.summary,
})
useHead(() => ({
  link: [{ rel: 'canonical', href: `https://keybolts.com.vn/tin-tuc/${article.value.slug}` }],
}))
</script>

<template>
  <div>
    <div class="border-border bg-surface border-b">
      <div class="mx-auto max-w-[var(--container-max)] px-[clamp(20px,4vw,48px)] py-[16px]">
        <LayoutBreadcrumb :items="[
          { label: 'Trang chủ', url: '/' },
          { label: 'Tin tức', url: '/tin-tuc' },
          { label: article.title, url: `/tin-tuc/${article.slug}` },
        ]" />
      </div>
    </div>

    <ArticleHead
      :eyebrow="article.category"
      :title="article.title"
      :summary="article.summary"
      :meta="[article.author, article.updated, article.readTime].filter(Boolean)"
    />

    <section class="bg-background pt-[clamp(32px,3.6vw,52px)] pb-[clamp(44px,5vw,72px)]">
      <div class="mx-auto grid max-w-[var(--container-max)] grid-cols-1 items-start gap-[clamp(32px,4vw,60px)] px-[clamp(20px,4vw,48px)] lg:grid-cols-[minmax(0,1fr)_340px]">
        <article class="flex min-w-0 flex-col gap-[30px]">
          <div class="group border-border relative aspect-video overflow-hidden border bg-white">
            <img :src="article.image" :alt="article.title" class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.05]">
          </div>

          <ArticleCallout v-if="article.quickAnswer" label="Trả lời nhanh" :text="article.quickAnswer" />

          <ArticleSection v-for="section in article.sections" :key="section.id" :section="section" />

          <p v-if="!article.sections.length" class="text-heading text-text-muted m-0 leading-[1.9]">{{ article.summary }}</p>

          <ArticleCompareTable
            v-if="article.compareRows.length"
            :id="COMPARE_ID"
            title="Bảng so sánh nhanh theo loại cửa"
            :rows="article.compareRows"
          />

          <ArticleFaq v-if="article.faqs.length" :id="FAQ_ID" title="Câu hỏi thường gặp" :faqs="article.faqs" />

          <div class="border-border bg-surface flex flex-col items-start gap-[20px] border p-[28px] sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-col gap-[8px]">
              <span class="text-brass-700 text-[10px] font-bold tracking-[0.2em] uppercase">Tác giả</span>
              <span class="text-heading font-bold">{{ article.author }}</span>
              <span class="text-caption text-text-muted leading-[1.8]">Nhóm kỹ thuật phụ trách khảo sát cửa và lắp đặt tại 5 cơ sở Bắc Ninh, Phú Thọ, Vĩnh Phúc.</span>
            </div>
            <a
              :href="HOTLINE_TEL"
              class="bg-charcoal-900 text-gold-200 text-caption hover:bg-neutral-700 inline-flex shrink-0 items-center gap-[10px] rounded-sm px-[28px] py-[15px] font-bold tracking-[0.08em] uppercase no-underline transition"
            >
              <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
              Hỏi kỹ thuật
            </a>
          </div>
        </article>

        <aside class="flex flex-col gap-[24px] lg:sticky lg:top-[120px]">
          <nav v-if="toc.length" class="border-border flex flex-col gap-[10px] border p-[24px]">
            <span class="text-brass-700 mb-[4px] text-[10px] font-bold tracking-[0.2em] uppercase">Nội dung bài viết</span>
            <a
              v-for="entry in toc"
              :key="entry.id"
              :href="`#${entry.id}`"
              class="border-border text-caption text-text hover:text-brass-700 flex gap-[10px] border-t py-[9px] leading-[1.5] no-underline"
            >
              <span class="text-brass-500 shrink-0 font-bold">{{ entry.number }}</span>
              {{ entry.label }}
            </a>
          </nav>

          <div v-if="article.products.length" class="border-border flex flex-col gap-[12px] border p-[24px]">
            <span class="text-brass-700 text-[10px] font-bold tracking-[0.2em] uppercase">Sản phẩm nhắc đến</span>
            <NuxtLink
              v-for="product in article.products"
              :key="product.id"
              :to="`/${product.slug}`"
              class="border-border hover:text-brass-700 flex items-center gap-[14px] border-t py-[12px] text-inherit no-underline"
            >
              <div class="border-border h-[56px] w-[56px] shrink-0 border bg-white">
                <img v-if="product.image" :src="product.image.url" :alt="product.image.alt" class="h-full w-full object-contain p-[6px]" loading="lazy">
              </div>
              <div class="flex min-w-0 flex-col gap-[3px]">
                <span class="text-body leading-[1.3] font-bold">{{ product.name }}</span>
                <span class="text-caption text-text-muted">{{ product.model }}</span>
              </div>
            </NuxtLink>
          </div>

          <ArticleConsultCard
            label="Cần tư vấn?"
            title="Gửi ảnh cửa, nhận gợi ý model trong 24h"
            :phone="HOTLINE"
          />
        </aside>
      </div>
    </section>

    <ArticleRelated
      eyebrow="Đọc tiếp"
      title="Bài viết liên quan"
      :items="related"
      cta-label="Xem tất cả bài viết"
      cta-to="/tin-tuc"
    />
  </div>
</template>
