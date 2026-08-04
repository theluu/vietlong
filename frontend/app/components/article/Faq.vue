<script setup lang="ts">
import type { ArticleFaq } from '~/types/page'

defineProps<{ id: string; title: string; faqs: ArticleFaq[] }>()

const open = ref<number>(0)
</script>

<template>
  <section :id="id" class="flex scroll-mt-[140px] flex-col gap-[16px]">
    <h2 class="text-display m-0 leading-[1.3] font-bold tracking-[-0.01em]">{{ title }}</h2>
    <div class="border-border flex flex-col border-t">
      <div v-for="(faq, i) in faqs" :key="faq.question" class="border-border border-b">
        <button
          type="button"
          class="text-heading text-text flex w-full cursor-pointer items-center justify-between gap-[20px] border-none bg-transparent px-0 py-[20px] text-left font-bold"
          :aria-expanded="open === i"
          @click="open = open === i ? -1 : i"
        >
          {{ faq.question }}
          <span
            class="border-border flex h-[30px] w-[30px] shrink-0 items-center justify-center rounded-full border transition-transform duration-200 ease-in-out"
            :class="open === i ? 'rotate-180' : 'rotate-0'"
          >
            <svg class="text-brass-700" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>
          </span>
        </button>
        <p v-if="open === i" class="text-body text-text-muted m-0 pb-[22px] leading-[1.9]">{{ faq.answer }}</p>
      </div>
    </div>
  </section>
</template>
