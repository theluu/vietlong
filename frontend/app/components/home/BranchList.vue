<script setup lang="ts">
import { fetchBranches } from '~/services/pages'

// Stable key so the header, homepage and Contact page share one request.
const { data } = await useAsyncData('branches', () => fetchBranches())
const branches = computed(() => data.value?.data ?? [])
</script>

<template>
  <section class="mx-auto max-w-[var(--container-max)] px-[clamp(20px,4vw,48px)] py-16">
    <div class="mb-10 text-center"><span class="text-eyebrow text-brass-700 font-bold tracking-[0.24em] uppercase">Hệ thống</span>
    <h2 class="text-display-lg mt-3 mb-5 font-bold tracking-[-0.03em]">Showroom &amp; kho hàng</h2>
    <NuxtLink to="/dai-ly" class="text-caption inline-flex border border-charcoal-900 px-6 py-3 font-bold tracking-[0.08em] text-charcoal-900 uppercase no-underline">Đăng ký làm đại lý</NuxtLink></div>

    <div class="grid grid-cols-1 gap-px border border-border bg-border sm:grid-cols-2 lg:grid-cols-5">
      <div
        v-for="loc in branches"
        :key="loc.id"
        class="flex min-w-0 flex-col gap-3 bg-background p-5 transition hover:bg-surface"
      >
        <span class="text-eyebrow text-brass-700 font-bold tracking-[0.1em] uppercase">{{ loc.name }}</span>
        <span class="text-caption text-text-muted leading-relaxed">{{ loc.address }}</span>
        <a
          :href="`tel:${loc.phoneTel}`"
          class="text-body text-brass-700 font-bold no-underline"
        >{{ loc.phoneDisplay }}</a>
      </div>
    </div>
  </section>
</template>
