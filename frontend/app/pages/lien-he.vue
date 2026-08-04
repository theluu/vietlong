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
    <PageCenteredHero :eyebrow="page.eyebrow" :title="page.title" :subtitle="page.subtitle" />

    <section class="bg-background py-[clamp(48px,5vw,72px)]">
      <div class="mx-auto max-w-[var(--container-max)] px-[clamp(20px,4vw,48px)]">
        <div class="mb-[clamp(40px,4vw,60px)]">
          <PageContactChannels :channels="page.channels" />
        </div>

        <PageFormPanel>
          <template #left>
            <span class="text-eyebrow text-brass-700 font-bold tracking-[0.22em] uppercase">Gửi yêu cầu</span>
            <h2 class="text-display-lg m-0 leading-[1.15] font-bold">{{ page.responseTitle }}</h2>
            <p class="text-body text-text-muted m-0 leading-[1.85]">{{ page.responseBody }}</p>
            <div class="border-border flex flex-col gap-[10px] border-t pt-[16px]">
              <span class="text-caption text-text-muted">{{ page.companyName }}</span>
              <span class="text-caption text-text-muted">{{ page.companyAddress }}</span>
            </div>
          </template>
          <template #form>
            <PageLeadForm
              source="contact"
              :title="page.formTitle"
              :desc="page.formDesc"
              :success-title="page.successTitle"
              :success-desc="page.successDesc"
              submit-label="Gửi liên hệ"
            />
          </template>
        </PageFormPanel>
      </div>
    </section>

    <section class="border-border bg-surface border-t">
      <PageBranchGrid eyebrow="Địa chỉ" title="Showroom &amp; kho hàng" :branches="branches" />
    </section>

    <PageBranchMap :branches="branches" />
  </div>
</template>
