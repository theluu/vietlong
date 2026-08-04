<script setup lang="ts">
defineProps<{ label: string; text: string }>()
</script>

<template>
  <div class="border-gold-200 bg-charcoal-900 flex flex-col gap-[14px] border-l-[3px] px-[28px] py-[26px] text-white">
    <span class="text-gold-200 text-[10px] font-bold tracking-[0.2em] uppercase">{{ label }}</span>
    <p class="text-heading m-0 leading-[1.85] font-normal">{{ text }}</p>
  </div>
</template>
