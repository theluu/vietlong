<script setup lang="ts">
import type { ArticleDetail } from '~/types/page'
defineProps<{ article: ArticleDetail }>()
</script>

<template>
  <NuxtLink
    :to="`/tin-tuc/${article.slug}`"
    class="group kb-featured-article border-border hover:border-brass-500 hover:shadow-floating mb-[40px] border text-inherit no-underline transition duration-200 ease-in-out"
  >
    <div class="relative aspect-[16/10] overflow-hidden bg-white">
      <img
        :src="article.image"
        :alt="article.title"
        class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.05]"
        fetchpriority="high"
      >
      <span class="bg-gold-200 text-charcoal-900 absolute top-0 left-0 px-[16px] py-[9px] text-[9px] font-bold tracking-[0.18em] uppercase">Nổi bật</span>
    </div>
    <div class="flex flex-col justify-center gap-[14px] p-[clamp(26px,3vw,44px)]">
      <span class="text-brass-700 text-[10px] font-bold tracking-[0.18em] uppercase">{{ article.category }}</span>
      <h2 class="text-display m-0 leading-[1.25] font-bold">{{ article.title }}</h2>
      <p class="text-body text-text-muted m-0 leading-[1.85]">{{ article.summary }}</p>
      <span class="text-caption text-text-muted">{{ article.author }} · {{ article.readTime }}</span>
    </div>
  </NuxtLink>
</template>
