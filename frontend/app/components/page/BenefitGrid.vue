<script setup lang="ts">
import type { NumberedItem } from '~/types/page'
defineProps<{ items: NumberedItem[] }>()
</script>

<template>
  <div class="kb-benefit-grid gap-[clamp(16px,1.8vw,26px)]">
    <div
      v-for="item in items"
      :key="item.number"
      class="border-border hover:border-brass-500 hover:shadow-floating flex flex-col gap-[12px] border p-[30px] transition duration-200 ease-in-out"
    >
      <span class="text-display text-brass-700 leading-none font-bold">{{ item.number }}</span>
      <span class="text-heading font-bold">{{ item.title }}</span>
      <span class="text-caption text-text-muted leading-[1.8]">{{ item.desc }}</span>
    </div>
  </div>
</template>
