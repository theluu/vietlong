<script setup lang="ts">
import type { ContactChannel } from '~/types/page'
defineProps<{ channels: ContactChannel[] }>()
</script>

<template>
  <div class="grid grid-cols-1 gap-px border border-border bg-border md:grid-cols-3">
    <a
      v-for="c in channels"
      :key="c.label"
      :href="c.ctaUrl || '#'"
      rel="noopener"
      class="flex flex-col gap-2 bg-background p-6 text-inherit no-underline transition hover:bg-surface"
    >
      <span class="text-caption text-brass-700 font-bold tracking-[0.14em] uppercase">{{ c.label }}</span>
      <span class="text-heading font-bold">{{ c.value }}</span>
      <span class="text-caption text-text-muted leading-relaxed">{{ c.note }}</span>
    </a>
  </div>
</template>
