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
    <PageCenteredHero :eyebrow="page.eyebrow" :title="page.title" :subtitle="page.subtitle" />

    <section class="bg-background py-[clamp(48px,5vw,72px)]">
      <div class="mx-auto max-w-[var(--container-max)] px-[clamp(20px,4vw,48px)]">
        <PageBenefitGrid :items="page.benefits" />
      </div>
    </section>

    <section class="border-border bg-surface border-t py-[clamp(48px,5vw,72px)]">
      <div class="mx-auto max-w-[var(--container-max)] px-[clamp(20px,4vw,48px)]">
        <PageFormPanel>
          <template #left>
            <span class="text-eyebrow text-brass-700 font-bold tracking-[0.22em] uppercase">Điều kiện</span>
            <h2 class="text-display-lg m-0 leading-[1.15] font-bold">Ai có thể làm đại lý?</h2>
            <PageCriteriaList :items="page.criteria" />
          </template>
          <template #form>
            <PageLeadForm
              source="dealer"
              :title="page.formTitle"
              :desc="page.formDesc"
              :success-title="page.successTitle"
              :success-desc="page.successDesc"
              submit-label="Gửi đăng ký"
            />
          </template>
        </PageFormPanel>
      </div>
    </section>

    <PageBranchGrid eyebrow="Hệ thống" title="Điểm bán &amp; kho hàng" :branches="branches" />

    <PageBranchMap :branches="branches" />
  </div>
</template>
