<script setup lang="ts">
defineProps<{ items: string[] }>()
</script>

<template>
  <div class="flex flex-col gap-[12px]">
    <div v-for="item in items" :key="item" class="flex items-start gap-[11px]">
      <svg class="text-brass-700 mt-[3px] shrink-0" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M20 6 9 17l-5-5"/></svg>
      <span class="text-body text-text leading-[1.7]">{{ item }}</span>
    </div>
  </div>
</template>
