<script setup lang="ts">
import type { NewsArticle } from '~/types/page'
defineProps<{ article: NewsArticle }>()
</script>

<template>
  <NuxtLink :to="`/tin-tuc/${article.slug}`" class="group flex min-w-0 flex-col overflow-hidden border border-border bg-background text-inherit no-underline transition hover:-translate-y-1 hover:border-brass-500 hover:shadow-floating">
    <div class="aspect-[16/10] overflow-hidden bg-surface">
      <UiResponsiveImage
        :image="article.image"
        :alt="article.title"
        sizes="(min-width: 1024px) 33vw, 100vw"
        class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.03]"
      />
    </div>
    <div class="flex flex-1 flex-col gap-3 p-6">
      <span class="text-caption text-brass-700 font-bold tracking-[0.14em] uppercase">{{ article.category }}</span>
      <h2 class="text-heading m-0 font-bold leading-snug">{{ article.title }}</h2>
      <p class="text-caption text-text-muted m-0 leading-relaxed">{{ article.summary }}</p>
      <span class="text-caption text-text-muted mt-auto pt-3">{{ article.readTime }}</span>
    </div>
  </NuxtLink>
</template>
