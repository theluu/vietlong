<script setup lang="ts">
import type { NumberedItem } from '~/types/page'
defineProps<{ eyebrow: string; title: string; intro?: string; steps: NumberedItem[] }>()
</script>

<template>
  <section class="bg-background py-[clamp(48px,5vw,80px)]">
    <div class="mx-auto max-w-[var(--container-max)] px-[clamp(20px,4vw,48px)]">
      <PageSectionHeading :eyebrow="eyebrow" :title="title" :intro="intro" rules />

      <div class="kb-process-grid border-border bg-border gap-px border">
        <div
          v-for="step in steps"
          :key="step.number"
          class="bg-background hover:bg-surface flex flex-col gap-[10px] px-[24px] py-[28px] transition-colors duration-200 ease-in-out"
        >
          <span class="text-display text-brass-500 leading-none font-bold">{{ step.number }}</span>
          <span class="text-heading leading-[1.3] font-bold">{{ step.title }}</span>
          <span class="text-caption text-text-muted leading-[1.75]">{{ step.desc }}</span>
        </div>
      </div>
    </div>
  </section>
</template>
