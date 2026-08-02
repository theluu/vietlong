<script setup lang="ts">
import { fetchBranches, fetchDealers } from '~/services/pages'

const { data } = await useAsyncData('page:dealers', () => fetchDealers())
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
useHead({ link: [{ rel: 'canonical', href: 'https://keybolts.com.vn/dai-ly' }] })
</script>

<template>
  <div v-if="page">
    <PageHero
      :eyebrow="page.eyebrow"
      :title="page.title"
      :subtitle="page.subtitle"
      :breadcrumb="[
        { label: 'Trang chủ', url: '/' },
        { label: 'Đại lý', url: '/dai-ly' },
      ]"
    />

    <PageStepList eyebrow="Quyền lợi" title="Keybolts hỗ trợ đại lý những gì" :steps="page.benefits" />

    <section class="bg-surface">
      <div class="mx-auto grid max-w-[var(--container-max)] gap-10 px-[clamp(20px,4vw,48px)] py-16 lg:grid-cols-2">
        <PageCriteriaList eyebrow="Điều kiện" title="Ai có thể làm đại lý?" :items="page.criteria" />
        <PageLeadForm
          source="dealer"
          :title="page.formTitle"
          :desc="page.formDesc"
          :success-title="page.successTitle"
          :success-desc="page.successDesc"
        />
      </div>
    </section>

    <PageBranchGrid eyebrow="Hệ thống" title="Điểm bán &amp; kho hàng" :branches="branches" />
  </div>
</template>
