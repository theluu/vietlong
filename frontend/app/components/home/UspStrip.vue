<script setup lang="ts">
import { USPS } from '~/utils/homeContent'
</script>

<template>
  <div class="border-b border-border bg-background">
    <div
      class="mx-auto grid max-w-[var(--container-max)] grid-cols-1 px-[clamp(20px,4vw,48px)] sm:grid-cols-2 lg:grid-cols-4"
    >
      <div
        v-for="(u, i) in USPS"
        :key="u.title"
        class="flex items-center gap-4 py-[26px] pr-[26px]"
        :class="i < USPS.length - 1 ? 'lg:border-r lg:border-border' : ''"
      >
        <span class="text-brass-700 text-display leading-none">◆</span>
        <div class="flex flex-col gap-[3px]">
          <span class="text-body font-bold tracking-[0.01em]">{{ u.title }}</span>
          <span class="text-caption text-text-muted">{{ u.desc }}</span>
        </div>
      </div>
    </div>
  </div>
</template>
