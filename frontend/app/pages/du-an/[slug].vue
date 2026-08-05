<script setup lang="ts">
import type { RelatedItem } from '~/types/page'
import { fetchProject, fetchProjects } from '~/services/pages'
const { hotline: HOTLINE } = useSite()

const slug = String(useRoute().params.slug)
const { data, error } = await useAsyncData(`project:${slug}`, () => fetchProject(slug))
if (error.value || !data.value?.data) {
  throw createError({ statusCode: 404, statusMessage: 'Không tìm thấy dự án', fatal: true })
}
const project = computed(() => data.value!.data)

const { data: listData } = await useAsyncData('projects', () => fetchProjects())

const related = computed<RelatedItem[]>(() => {
  const all = (listData.value?.data ?? []).filter(item => item.slug !== project.value.slug)
  // Same building type first, so the read-next row stays relevant.
  const ranked = [
    ...all.filter(item => item.typeKey === project.value.typeKey),
    ...all.filter(item => item.typeKey !== project.value.typeKey),
  ]
  return ranked.slice(0, 4).map(item => ({
    key: item.id,
    to: `/du-an/${item.slug}`,
    image: item.image,
    badge: item.type,
    title: item.title,
    summary: item.description,
    meta: item.products,
  }))
})

useSeoMeta({
  title: () => `${project.value.title} | Keybolts`,
  description: () => project.value.description,
})
useHead(() => ({
  link: [{ rel: 'canonical', href: `https://keybolts.com.vn/du-an/${project.value.slug}` }],
}))
</script>

<template>
  <div>
    <div class="border-border bg-surface border-b">
      <div class="mx-auto max-w-[var(--container-max)] px-[clamp(20px,4vw,48px)] py-[16px]">
        <LayoutBreadcrumb :items="[
          { label: 'Trang chủ', url: '/' },
          { label: 'Dự án', url: '/du-an' },
          { label: project.title, url: `/du-an/${project.slug}` },
        ]" />
      </div>
    </div>

    <ArticleHead
      :eyebrow="project.type"
      :title="project.title"
      :summary="project.description"
      :meta="['Đội kỹ thuật Keybolts', project.products]"
    />

    <section class="bg-background pt-[clamp(32px,3.6vw,52px)] pb-[clamp(44px,5vw,72px)]">
      <div class="mx-auto grid max-w-[var(--container-max)] grid-cols-1 items-start gap-[clamp(32px,4vw,60px)] px-[clamp(20px,4vw,48px)] lg:grid-cols-[minmax(0,1fr)_340px]">
        <article class="flex min-w-0 flex-col gap-[30px]">
          <div class="group border-border relative aspect-video overflow-hidden border bg-white">
            <UiResponsiveImage
              :image="project.image"
              :alt="project.title"
              sizes="(min-width: 1024px) 760px, 100vw"
              priority
              class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.05]"
            />
          </div>

          <ArticleCallout label="Giải pháp đã triển khai" :text="project.description" />

          <section class="flex flex-col gap-[14px]">
            <h2 class="text-display m-0 leading-[1.3] font-bold tracking-[-0.01em]">Sản phẩm sử dụng</h2>
            <p class="text-body text-text-muted m-0 leading-[1.95]">
              Công trình dùng {{ project.products }}, chọn theo độ dày cánh và chiều mở của từng nhóm cửa. Toàn bộ bộ khóa,
              tay nắm và bản lề đi cùng một hệ hoàn thiện để tổng thể liền mạch giữa các tầng.
            </p>
            <div class="border-brass-500 bg-surface flex items-start gap-[12px] border-l-[3px] px-[24px] py-[22px]">
              <svg class="text-brass-700 mt-[2px] shrink-0" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
              <span class="text-body text-text-muted leading-[1.85]">Cần danh mục và báo giá cho một công trình tương tự? Gửi bảng thống kê cửa qua hotline, kỹ thuật bóc tách theo từng loại cửa trong 24h làm việc.</span>
            </div>
          </section>
        </article>

        <aside class="flex flex-col gap-[24px] lg:sticky lg:top-[120px]">
          <div class="border-border flex flex-col gap-[12px] border p-[24px]">
            <span class="text-brass-700 text-[10px] font-bold tracking-[0.2em] uppercase">Thông tin công trình</span>
            <div class="border-border flex flex-col gap-[3px] border-t pt-[12px]">
              <span class="text-caption text-text-muted">Loại công trình</span>
              <span class="text-body font-bold">{{ project.type }}</span>
            </div>
            <div class="border-border flex flex-col gap-[3px] border-t pt-[12px]">
              <span class="text-caption text-text-muted">Sản phẩm / giải pháp</span>
              <span class="text-body font-bold">{{ project.products }}</span>
            </div>
          </div>

          <div class="border-border flex flex-col gap-[12px] border p-[24px]">
            <span class="text-brass-700 text-[10px] font-bold tracking-[0.2em] uppercase">Xem thêm</span>
            <NuxtLink to="/san-pham" class="border-border text-caption text-text hover:text-brass-700 border-t py-[9px] leading-[1.5] no-underline">Toàn bộ danh mục sản phẩm</NuxtLink>
            <NuxtLink to="/du-an" class="border-border text-caption text-text hover:text-brass-700 border-t py-[9px] leading-[1.5] no-underline">Các công trình đã bàn giao</NuxtLink>
            <NuxtLink to="/lien-he" class="border-border text-caption text-text hover:text-brass-700 border-t py-[9px] leading-[1.5] no-underline">Gửi yêu cầu khảo sát</NuxtLink>
          </div>

          <ArticleConsultCard
            label="Cần tư vấn?"
            title="Gửi bảng thống kê cửa, nhận báo giá trong 24h"
            :phone="HOTLINE"
          />
        </aside>
      </div>
    </section>

    <ArticleRelated
      eyebrow="Xem tiếp"
      title="Dự án liên quan"
      :items="related"
      cta-label="Xem tất cả dự án"
      cta-to="/du-an"
    />
  </div>
</template>
