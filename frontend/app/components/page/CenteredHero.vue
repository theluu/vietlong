<script setup lang="ts">
defineProps<{ eyebrow: string; title: string; subtitle: string }>()
</script>

<template>
  <section class="kb-hero-glow bg-charcoal-900 relative overflow-hidden py-[clamp(52px,6vw,84px)] text-white">
    <div class="relative mx-auto flex max-w-[var(--container-max)] flex-col items-center gap-[16px] px-[clamp(20px,4vw,48px)] text-center">
      <span class="text-eyebrow text-gold-200 flex items-center gap-[14px] font-bold tracking-[0.24em] uppercase">
        <span class="bg-gold-200 h-[2px] w-[36px]"/>{{ eyebrow }}
        <span class="bg-gold-200 h-[2px] w-[36px]"/>
      </span>
      <h1 class="m-0 text-[clamp(var(--text-display),3.8vw,var(--text-display-lg))] leading-[1.1] font-bold tracking-[-0.03em]">{{ title }}</h1>
      <p class="text-heading m-0 max-w-[720px] leading-[1.75] font-normal text-white/86">{{ subtitle }}</p>
    </div>
  </section>
</template>
