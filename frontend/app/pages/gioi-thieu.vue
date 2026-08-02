<script setup lang="ts">
import { fetchAbout, fetchBranches } from '~/services/pages'

const { data } = await useAsyncData('page:about', () => fetchAbout())
const { data: branchData } = await useAsyncData('branches', () => fetchBranches())

const page = computed(() => data.value?.data)
const branches = computed(() => branchData.value?.data ?? [])

if (!page.value) {
  throw createError({ statusCode: 404, statusMessage: 'Không tìm thấy trang', fatal: true })
}

useSeoMeta({
  title: () => `${page.value?.title} | Keybolts`,
  description: () => page.value?.subtitle,
})

useHead({ link: [{ rel: 'canonical', href: 'https://keybolts.com.vn/gioi-thieu' }] })

const breadcrumb = [
  { label: 'Trang chủ', url: '/' },
  { label: 'Giới thiệu', url: '/gioi-thieu' },
]
</script>

<template>
  <div v-if="page">
    <PageHero
      :eyebrow="page.eyebrow"
      :title="page.title"
      :subtitle="page.subtitle"
      :image="page.heroImage"
      :caption="page.heroCaption"
      :cta-primary="page.ctaPrimary"
      :cta-secondary="page.ctaSecondary"
      :breadcrumb="breadcrumb"
    />
    <PageFactStrip :facts="page.facts" />
    <PageStoryBlock
      :eyebrow="page.storyEyebrow"
      :title="page.storyTitle"
      :body="page.storyBody"
      :credentials="page.credentials"
    />
    <PageSegmentGrid eyebrow="Khách hàng" title="Keybolts phục vụ ai?" :segments="page.segments" />
    <PageStepList
      eyebrow="Quy trình"
      title="Từ tư vấn đến bảo hành"
      intro="Năm bước rõ ràng — bạn biết chính xác điều gì sẽ diễn ra sau khi bấm gọi."
      :steps="page.steps"
    />
    <PageValueGrid eyebrow="Cam kết" title="Điều Keybolts đảm bảo bằng văn bản" :values="page.values" />
    <PageBranchGrid eyebrow="Hệ thống" title="Showroom &amp; kho hàng" :branches="branches" />
  </div>
</template>
