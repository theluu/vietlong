<script setup lang="ts">
defineProps<{ label: string; title: string; phone: string }>()
</script>

<template>
  <div class="bg-charcoal-900 flex flex-col gap-[14px] p-[26px] text-white">
    <span class="text-gold-200 text-[10px] font-bold tracking-[0.2em] uppercase">{{ label }}</span>
    <span class="text-heading leading-[1.4] font-bold">{{ title }}</span>
    <a
      :href="`tel:${phone.replace(/\s/g, '')}`"
      class="bg-gold-200 text-charcoal-900 text-caption inline-flex items-center justify-center gap-[10px] rounded-sm py-[14px] font-bold tracking-[0.08em] uppercase no-underline transition hover:bg-white hover:text-charcoal-900"
    >{{ phone }}</a>
  </div>
</template>
