<script setup lang="ts">
import type { RelatedItem } from '~/types/page'

defineProps<{
  eyebrow: string
  title: string
  items: RelatedItem[]
  ctaLabel: string
  ctaTo: string
}>()
</script>

<template>
  <section v-if="items.length" class="border-border bg-surface border-t py-[clamp(48px,5vw,76px)]">
    <div class="mx-auto max-w-[var(--container-max)] px-[clamp(20px,4vw,48px)]">
      <div class="mb-[36px] flex flex-col items-center gap-[14px] text-center">
        <span class="text-eyebrow text-brass-700 flex items-center gap-[14px] font-bold tracking-[0.22em] uppercase">
          <span class="bg-brass-500 h-[2px] w-[36px]"/>{{ eyebrow }}
          <span class="bg-brass-500 h-[2px] w-[36px]"/>
        </span>
        <h2 class="text-display-lg m-0 font-bold tracking-[-0.02em]">{{ title }}</h2>
      </div>

      <div class="kb-article-related-grid gap-[clamp(16px,1.8vw,26px)]">
        <NuxtLink
          v-for="item in items"
          :key="item.key"
          :to="item.to"
          class="group border-border bg-background flex flex-col border text-inherit no-underline transition duration-200 ease-in-out hover:-translate-y-1 hover:border-brass-500 hover:shadow-floating"
        >
          <div class="relative aspect-[16/10] overflow-hidden bg-white">
            <img
              :src="item.image"
              :alt="item.title"
              class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.05]"
              loading="lazy"
            >
            <span class="bg-charcoal-900 text-gold-200 absolute top-0 left-0 px-[13px] py-[7px] text-[9px] font-bold tracking-[0.16em] uppercase">{{ item.badge }}</span>
          </div>
          <div class="flex flex-1 flex-col gap-[9px] p-[20px]">
            <span class="text-heading leading-[1.35] font-bold">{{ item.title }}</span>
            <span class="text-caption text-text-muted leading-[1.7]">{{ item.summary }}</span>
            <span class="border-border text-caption text-text-muted mt-auto border-t pt-[14px]">{{ item.meta }}</span>
          </div>
        </NuxtLink>
      </div>

      <div class="mt-[36px] flex justify-center">
        <NuxtLink
          :to="ctaTo"
          class="text-body text-charcoal-900 border-neutral-300 hover:bg-charcoal-900 hover:border-charcoal-900 hover:text-gold-200 inline-flex items-center gap-[10px] rounded-sm border px-[32px] py-[16px] font-bold tracking-[0.06em] uppercase no-underline transition"
        >
          {{ ctaLabel }}
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/><path d="m13 6 6 6-6 6"/></svg>
        </NuxtLink>
      </div>
    </div>
  </section>
</template>
