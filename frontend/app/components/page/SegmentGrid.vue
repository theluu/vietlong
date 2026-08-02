<script setup lang="ts">
import type { Segment } from '~/types/page'
defineProps<{ eyebrow: string; title: string; segments: Segment[] }>()
</script>

<template>
  <section class="bg-surface">
    <div class="mx-auto max-w-[var(--container-max)] px-[clamp(20px,4vw,48px)] py-16">
      <span class="text-eyebrow text-brass-700 font-bold tracking-[0.24em] uppercase">{{ eyebrow }}</span>
      <h2 class="text-display-lg mt-3 mb-10 font-bold tracking-[-0.03em]">{{ title }}</h2>

      <div class="kb-segment-grid gap-[clamp(16px,1.6vw,24px)]">
        <NuxtLink
          v-for="s in segments"
          :key="s.title"
          :to="s.ctaUrl || '/san-pham'"
          class="flex flex-col gap-3 border border-border bg-background p-6 text-inherit no-underline transition hover:-translate-y-1 hover:border-brass-500 hover:shadow-floating"
        >
          <img v-if="s.image" :src="s.image" :alt="s.title" class="aspect-[4/3] w-full object-cover" loading="lazy">
          <span class="text-heading font-bold">{{ s.title }}</span>
          <span class="text-caption text-text-muted leading-relaxed">{{ s.desc }}</span>
          <span v-if="s.ctaLabel" class="text-caption text-brass-700 mt-auto pt-3 font-bold">{{ s.ctaLabel }} →</span>
        </NuxtLink>
      </div>
    </div>
  </section>
</template>
