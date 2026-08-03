<script setup lang="ts">
import { fetchBranches } from '~/services/pages'

// Stable key so the header, homepage and Contact page share one request.
const { data } = await useAsyncData('branches', () => fetchBranches())
const branches = computed(() => data.value?.data ?? [])
</script>

<template>
  <section id="dealer" class="bg-background py-[clamp(60px,7vw,104px)]">
    <div class="mx-auto max-w-[var(--container-max)] px-[clamp(20px,4vw,48px)]">
    <div class="mb-[40px] flex flex-col items-center gap-[24px] text-center">
      <div class="flex max-w-[780px] flex-col items-center gap-[14px]"><div class="flex items-center gap-[14px]"><span class="h-px w-[34px] bg-brass-500"/><span class="text-eyebrow text-brass-700 font-bold tracking-[0.24em] uppercase">Hệ thống</span></div><h2 class="m-0 text-[clamp(var(--text-display),3.6vw,var(--text-display-lg))] leading-[1.1] font-bold tracking-[-0.032em]">Showroom &amp; kho hàng</h2></div>
      <NuxtLink to="/dai-ly" class="text-caption inline-flex items-center gap-[11px] rounded-sm border border-neutral-300 px-[30px] py-[16px] font-bold tracking-[0.1em] text-charcoal-900 uppercase no-underline transition hover:border-charcoal-900 hover:bg-charcoal-900 hover:text-gold-200">Đăng ký làm đại lý <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6"><path d="M5 12h14"/><path d="m13 6 6 6-6 6"/></svg></NuxtLink>
    </div>

    <div class="grid grid-cols-[repeat(auto-fit,minmax(250px,1fr))] gap-px border border-border bg-border">
      <div
        v-for="loc in branches"
        :key="loc.id"
        class="flex min-w-0 flex-col gap-[12px] bg-background px-[26px] py-[30px] transition hover:bg-surface"
      >
        <span class="text-eyebrow text-brass-700 font-bold tracking-[0.16em] uppercase">{{ loc.name }}</span>
        <span class="text-body flex-1 leading-[1.7] text-text">{{ loc.address }}</span>
        <a
          :href="`tel:${loc.phoneTel}`"
          class="text-heading font-bold tracking-[0.01em] text-charcoal-900 no-underline hover:text-brass-700"
        >{{ loc.phoneDisplay }}</a>
      </div>
    </div></div>
  </section>
</template>
