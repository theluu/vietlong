<script setup lang="ts">
import { fetchBranches, fetchContact } from '~/services/pages'

const { data } = await useAsyncData('page:contact', () => fetchContact())
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
useHead({ link: [{ rel: 'canonical', href: 'https://keybolts.com.vn/lien-he' }] })
</script>

<template>
  <div v-if="page">
    <PageHero
      :eyebrow="page.eyebrow"
      :title="page.title"
      :subtitle="page.subtitle"
      :breadcrumb="[
        { label: 'Trang chủ', url: '/' },
        { label: 'Liên hệ', url: '/lien-he' },
      ]"
    />

    <div class="mx-auto max-w-[var(--container-max)] px-[clamp(20px,4vw,48px)] py-12">
      <PageContactChannels :channels="page.channels" />
    </div>

    <section class="bg-surface">
      <div class="mx-auto grid max-w-[var(--container-max)] gap-10 px-[clamp(20px,4vw,48px)] py-16 lg:grid-cols-2">
        <div class="flex flex-col gap-4">
          <h2 class="text-display m-0 font-bold tracking-[-0.02em]">{{ page.responseTitle }}</h2>
          <p class="text-body text-text-muted m-0 leading-relaxed">{{ page.responseBody }}</p>
          <span class="text-body mt-4 font-bold">{{ page.companyName }}</span>
          <span class="text-caption text-text-muted">{{ page.companyAddress }}</span>
        </div>

        <PageLeadForm
          source="contact"
          :title="page.formTitle"
          :desc="page.formDesc"
          :success-title="page.successTitle"
          :success-desc="page.successDesc"
        />
      </div>
    </section>

    <PageBranchGrid eyebrow="Địa chỉ" title="Showroom &amp; kho hàng" :branches="branches" />
  </div>
</template>
