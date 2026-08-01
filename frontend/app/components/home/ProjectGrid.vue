<script setup lang="ts">
import { PROJECT_TYPES } from '~/utils/homeContent'
</script>

<template>
  <section class="mx-auto max-w-[var(--container-max)] px-[clamp(20px,4vw,48px)] py-16">
    <span class="text-eyebrow text-brass-700 font-bold tracking-[0.24em] uppercase">Ứng dụng</span>
    <h2 class="text-display-lg mt-3 mb-10 font-bold tracking-[-0.03em]">Dự án &amp; công trình</h2>

    <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
      <div
        v-for="proj in PROJECT_TYPES"
        :key="proj.key"
        class="flex flex-col gap-3 border border-border p-6"
      >
        <span class="text-heading font-bold">{{ proj.title }}</span>
        <span class="text-caption text-text-muted leading-relaxed">{{ proj.desc }}</span>
      </div>
    </div>
  </section>
</template>
