<script setup lang="ts">
import { fetchAbout, fetchBranches } from '~/services/pages'

const { hotline: HOTLINE, hotlineTel: HOTLINE_TEL } = useSite()
const { data } = await useAsyncData('page:about', () => fetchAbout())
const { data: branchData } = await useAsyncData('branches', () => fetchBranches())

const page = computed(() => data.value?.data)
const branches = computed(() => branchData.value?.data ?? [])

if (!page.value) {
  throw createError({ statusCode: 404, statusMessage: 'Không tìm thấy trang', fatal: true })
}

/**
 * The story column's two shots are decoration, not editorial, so they are
 * borrowed from the segments the editor already curated rather than adding
 * two more image fields to maintain.
 */
const storyImages = computed(() =>
  (page.value?.segments ?? []).map(segment => segment.image).filter((src): src is string => !!src).slice(0, 2))

useSeoMeta({
  title: () => `${page.value?.title} | Keybolts`,
  description: () => page.value?.subtitle,
})
useHead({ link: [{ rel: 'canonical', href: 'https://keybolts.com.vn/gioi-thieu' }] })
</script>

<template>
  <div v-if="page">
    <PageAboutHero
      :eyebrow="page.eyebrow"
      :title="page.title"
      :subtitle="page.subtitle"
      :image="page.heroImage"
      :caption="page.heroCaption"
      :cta-primary="page.ctaPrimary"
      :cta-secondary="page.ctaSecondary"
      :facts="page.facts"
    />

    <PageStoryBlock
      :eyebrow="page.storyEyebrow"
      :title="page.storyTitle"
      :body="page.storyBody"
      :credentials="page.credentials"
      :images="storyImages"
    />

    <PageSegmentGrid eyebrow="Khách hàng" title="Keybolts phục vụ ai?" :segments="page.segments" />

    <PageProcessGrid
      eyebrow="Quy trình"
      title="Từ tư vấn đến bảo hành"
      intro="Năm bước rõ ràng — bạn biết chính xác điều gì sẽ diễn ra sau khi bấm gọi."
      :steps="page.steps"
    />

    <PageValueGrid eyebrow="Cam kết" title="Điều Keybolts đảm bảo bằng văn bản" :values="page.values" />

    <PageBranchGrid eyebrow="Hệ thống" title="Showroom &amp; kho hàng" :branches="branches" variant="hairline" />

    <PageClosingCta
      eyebrow="Bắt đầu"
      title="Gửi bản vẽ hoặc ảnh cửa — nhận phương án trong 24h"
      body="Kỹ thuật Keybolts sẽ chọn đúng model theo loại cửa, độ dày cánh và phong cách nội thất của bạn, kèm báo giá chi tiết theo số lượng."
      :phone="HOTLINE"
      :image="page.heroImage"
    />
  </div>
</template>
