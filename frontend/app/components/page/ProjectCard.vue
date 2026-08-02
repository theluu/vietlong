<script setup lang="ts">
import type { Project } from '~/types/page'
defineProps<{ project: Project }>()
</script>

<template>
  <NuxtLink :to="`/du-an/${project.slug}`" class="group flex min-w-0 flex-col border border-border text-inherit no-underline transition hover:-translate-y-1 hover:border-brass-500 hover:shadow-floating">
    <div class="relative aspect-[4/3] overflow-hidden bg-white">
      <img :src="project.image" :alt="project.title" class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.03]" loading="lazy">
      <span class="absolute top-0 left-0 bg-charcoal-900 px-3.5 py-2 text-[9px] font-bold tracking-[.18em] text-gold-200 uppercase">{{ project.type }}</span>
    </div>
    <div class="flex flex-1 flex-col gap-2.5 p-5.5">
      <h2 class="text-heading m-0 font-bold leading-snug">{{ project.title }}</h2>
      <p class="text-caption text-text-muted m-0 leading-relaxed">{{ project.description }}</p>
      <div class="text-caption text-brass-700 mt-auto flex items-center justify-between gap-2.5 border-t border-border pt-3.5 font-bold">
        <span>{{ project.products }}</span><span aria-hidden="true" class="flex h-7 w-7 items-center justify-center rounded-full border border-border">→</span>
      </div>
    </div>
  </NuxtLink>
</template>
