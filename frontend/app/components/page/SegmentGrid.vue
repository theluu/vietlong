<script setup lang="ts">
import type { Segment } from '~/types/page'
defineProps<{ eyebrow: string; title: string; segments: Segment[] }>()
</script>

<template>
  <section class="border-border bg-surface border-t py-[clamp(48px,5vw,80px)]">
    <div class="mx-auto max-w-[var(--container-max)] px-[clamp(20px,4vw,48px)]">
      <PageSectionHeading :eyebrow="eyebrow" :title="title" rules />

      <div class="kb-segment-grid gap-[clamp(16px,1.6vw,24px)]">
        <NuxtLink
          v-for="segment in segments"
          :key="segment.title"
          :to="segment.ctaUrl || '/san-pham'"
          class="group border-border bg-background hover:border-brass-500 hover:shadow-floating flex flex-col border text-inherit no-underline transition duration-200 ease-in-out hover:-translate-y-1"
        >
          <div v-if="segment.image" class="relative aspect-[4/3] overflow-hidden bg-white">
            <img
              :src="segment.image"
              :alt="segment.title"
              class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.05]"
              loading="lazy"
            >
          </div>
          <div class="flex flex-1 flex-col gap-[8px] p-[22px]">
            <span class="text-heading font-bold">{{ segment.title }}</span>
            <span class="text-caption text-text-muted leading-[1.75]">{{ segment.desc }}</span>
            <span
              v-if="segment.ctaLabel"
              class="border-border text-caption text-brass-700 mt-auto border-t pt-[14px] font-bold"
            >{{ segment.ctaLabel }} →</span>
          </div>
        </NuxtLink>
      </div>
    </div>
  </section>
</template>
