<script setup lang="ts">
withDefaults(defineProps<{
  eyebrow: string
  title: string
  intro?: string
  /** The map section flanks its eyebrow with rules; the plain lists do not. */
  rules?: boolean
}>(), { rules: false })
</script>

<template>
  <div class="mb-[36px] flex flex-col items-center gap-[14px] text-center">
    <span class="text-eyebrow text-brass-700 flex items-center gap-[14px] font-bold tracking-[0.22em] uppercase">
      <span v-if="rules" class="bg-brass-500 h-[2px] w-[36px]"/>{{ eyebrow }}
      <span v-if="rules" class="bg-brass-500 h-[2px] w-[36px]"/>
    </span>
    <h2 class="text-display-lg m-0 font-bold tracking-[-0.02em]">{{ title }}</h2>
    <p v-if="intro" class="text-body text-text-muted m-0 max-w-[640px] leading-[1.8]">{{ intro }}</p>
  </div>
</template>
