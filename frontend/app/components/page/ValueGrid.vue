<script setup lang="ts">
import type { ValueItem } from '~/types/page'
defineProps<{ eyebrow: string; title: string; values: ValueItem[] }>()
</script>

<template>
  <section class="bg-charcoal-900 text-white">
    <div class="mx-auto max-w-[var(--container-max)] px-[clamp(20px,4vw,48px)] py-16">
      <span class="text-eyebrow text-gold-200 font-bold tracking-[0.24em] uppercase">{{ eyebrow }}</span>
      <h2 class="text-display-lg mt-3 mb-10 font-bold tracking-[-0.03em]">{{ title }}</h2>

      <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
        <div v-for="v in values" :key="v.title" class="flex flex-col gap-3 border border-white/12 p-6">
          <span class="text-heading text-gold-200 font-bold">{{ v.title }}</span>
          <span class="text-body leading-relaxed text-white/72">{{ v.desc }}</span>
        </div>
      </div>
    </div>
  </section>
</template>
