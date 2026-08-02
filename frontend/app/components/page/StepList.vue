<script setup lang="ts">
import type { NumberedItem } from '~/types/page'
defineProps<{ eyebrow: string; title: string; intro?: string; steps: NumberedItem[] }>()
</script>

<template>
  <section class="mx-auto max-w-[var(--container-max)] px-[clamp(20px,4vw,48px)] py-16">
    <span class="text-eyebrow text-brass-700 font-bold tracking-[0.24em] uppercase">{{ eyebrow }}</span>
    <h2 class="text-display-lg mt-3 font-bold tracking-[-0.03em]">{{ title }}</h2>
    <p v-if="intro" class="text-heading text-text-muted mt-4 max-w-[720px] font-light leading-relaxed">{{ intro }}</p>

    <ol class="m-0 mt-10 flex list-none flex-col gap-px bg-border p-0">
      <li v-for="s in steps" :key="s.number" class="flex gap-6 bg-background p-6">
        <span class="text-display text-brass-700 font-bold tracking-[-0.03em]">{{ s.number }}</span>
        <span class="flex flex-col gap-2">
          <span class="text-heading font-bold">{{ s.title }}</span>
          <span class="text-body text-text-muted leading-relaxed">{{ s.desc }}</span>
        </span>
      </li>
    </ol>
  </section>
</template>
