<script setup lang="ts">
import { fetchBranches } from '~/services/pages'

// Stable key so the header, homepage and Contact page share one request.
const { data } = await useAsyncData('branches', () => fetchBranches())
const branches = computed(() => data.value?.data ?? [])
</script>

<template>
  <section class="mx-auto max-w-[var(--container-max)] px-[clamp(20px,4vw,48px)] py-16">
    <span class="text-eyebrow text-brass-700 font-bold tracking-[0.24em] uppercase">Hệ thống</span>
    <h2 class="text-display-lg mt-3 mb-10 font-bold tracking-[-0.03em]">Showroom &amp; kho hàng</h2>

    <div class="grid grid-cols-1 gap-px border border-border bg-border sm:grid-cols-2 lg:grid-cols-3">
      <div
        v-for="loc in branches"
        :key="loc.id"
        class="flex flex-col gap-3 bg-background p-[26px] transition hover:bg-surface"
      >
        <span class="text-heading font-bold">{{ loc.name }}</span>
        <span class="text-caption text-text-muted leading-relaxed">{{ loc.address }}</span>
        <a
          :href="`tel:${loc.phoneTel}`"
          class="text-body text-brass-700 font-bold no-underline"
        >{{ loc.phoneDisplay }}</a>
      </div>
    </div>
  </section>
</template>
