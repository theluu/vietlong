<script setup lang="ts">
defineProps<{ eyebrow: string; title: string; items: string[] }>()
</script>

<template>
  <div class="flex flex-col gap-5">
    <span class="text-eyebrow text-brass-700 font-bold tracking-[0.24em] uppercase">{{ eyebrow }}</span>
    <h2 class="text-display m-0 font-bold tracking-[-0.02em]">{{ title }}</h2>
    <ul class="m-0 flex list-none flex-col gap-3 p-0">
      <li v-for="c in items" :key="c" class="text-body flex gap-3 border-b border-border pb-3 leading-relaxed">
        <span class="text-brass-700 font-bold">✓</span>{{ c }}
      </li>
    </ul>
  </div>
</template>
