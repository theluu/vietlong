<script setup lang="ts">
import type { Branch } from '~/types/page'
defineProps<{ eyebrow: string; title: string; branches: Branch[] }>()
</script>

<template>
  <section class="mx-auto max-w-[var(--container-max)] px-[clamp(20px,4vw,48px)] py-16">
    <span class="text-eyebrow text-brass-700 font-bold tracking-[0.24em] uppercase">{{ eyebrow }}</span>
    <h2 class="text-display-lg mt-3 mb-10 font-bold tracking-[-0.03em]">{{ title }}</h2>

    <div class="grid grid-cols-1 gap-px border border-border bg-border sm:grid-cols-2 lg:grid-cols-3">
      <div v-for="b in branches" :key="b.id" class="flex flex-col gap-3 bg-background p-[26px]">
        <span class="text-caption text-brass-700 font-bold tracking-[0.14em] uppercase">{{ b.tag }}</span>
        <span class="text-heading font-bold">{{ b.name }}</span>
        <span class="text-caption text-text-muted leading-relaxed">{{ b.address }}</span>
        <a :href="`tel:${b.phoneTel}`" class="text-body text-brass-700 font-bold no-underline">{{ b.phoneDisplay }}</a>
        <a
          v-if="b.mapUrl"
          :href="b.mapUrl"
          rel="noopener"
          class="text-caption text-text-muted mt-auto pt-2 no-underline hover:text-brass-700"
        >Chỉ đường →</a>
      </div>
    </div>
  </section>
</template>
