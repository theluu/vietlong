<script setup lang="ts">
import type { ArticleSection } from '~/types/page'
defineProps<{ section: ArticleSection }>()
</script>

<template>
  <section :id="section.id" class="flex scroll-mt-[140px] flex-col gap-[14px]">
    <h2 class="text-display m-0 leading-[1.3] font-bold tracking-[-0.01em]">{{ section.title }}</h2>
    <p v-for="paragraph in section.paragraphs" :key="paragraph" class="text-body text-text-muted m-0 leading-[1.95]">{{ paragraph }}</p>

    <div v-if="section.list?.length" class="flex flex-col gap-[10px] pt-[2px]">
      <div v-for="item in section.list" :key="item" class="flex items-start gap-[11px]">
        <svg class="text-brass-700 mt-[4px] shrink-0" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6"><path d="M20 6 9 17l-5-5"/></svg>
        <span class="text-body text-text leading-[1.75]">{{ item }}</span>
      </div>
    </div>

    <div v-if="section.note" class="border-brass-500 bg-surface flex items-start gap-[12px] border-l-[3px] px-[24px] py-[22px]">
      <svg class="text-brass-700 mt-[2px] shrink-0" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
      <span class="text-body text-text-muted leading-[1.85]">{{ section.note }}</span>
    </div>
  </section>
</template>
