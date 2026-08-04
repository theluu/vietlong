<script setup lang="ts">
defineProps<{
  eyebrow: string
  title: string
  summary: string
  /** Byline, update date, read time — rendered with hairline dividers between. */
  meta?: string[]
}>()
</script>

<template>
  <header class="bg-charcoal-900 py-[clamp(40px,4.5vw,66px)] text-white">
    <div class="mx-auto flex max-w-[920px] flex-col gap-[16px] px-[clamp(20px,4vw,48px)]">
      <span class="text-gold-200 flex items-center gap-[12px] text-[10px] font-bold tracking-[0.2em] uppercase">
        <span class="bg-gold-200 h-[2px] w-[28px]"/>{{ eyebrow }}
      </span>
      <h1 class="m-0 text-[clamp(var(--text-display),3.6vw,var(--text-display-lg))] leading-[1.14] font-bold tracking-[-0.025em]">{{ title }}</h1>
      <p class="text-heading m-0 leading-[1.8] font-normal text-white/80">{{ summary }}</p>
      <div v-if="meta?.length" class="text-caption flex flex-wrap items-center gap-[16px] pt-[8px] text-white/60">
        <template v-for="(item, i) in meta" :key="item">
          <span v-if="i > 0" class="h-[12px] w-px bg-white/25"/>
          <span>{{ item }}</span>
        </template>
      </div>
    </div>
  </header>
</template>
