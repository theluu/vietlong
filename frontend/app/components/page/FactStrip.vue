<script setup lang="ts">
import type { Fact } from '~/types/page'
defineProps<{ facts: Fact[] }>()
</script>

<template>
  <div class="border-b border-border bg-surface">
    <div class="kb-fact-strip mx-auto max-w-[var(--container-max)] gap-8 px-[clamp(20px,4vw,48px)] py-10">
      <div v-for="f in facts" :key="f.label" class="flex flex-col gap-1">
        <span class="text-display-lg text-brass-700 leading-none font-bold tracking-[-0.03em]">{{ f.number }}</span>
        <span class="text-caption text-text-muted">{{ f.label }}</span>
      </div>
    </div>
  </div>
</template>
