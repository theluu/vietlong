<script setup lang="ts">
import { fetchArticles, fetchProjects } from '~/services/pages'

/**
 * Real projects and articles rather than the hard-coded copies this panel used
 * to show — those drifted out of date the moment an editor published anything.
 */
const { data: projectData } = await useAsyncData('projects', () => fetchProjects())
const { data: articleData } = await useAsyncData('articles', () => fetchArticles())
const projects = computed(() => (projectData.value?.data ?? []).slice(0, 3))
const articles = computed(() => (articleData.value?.data ?? []).slice(0, 4))
</script>

<template>
  <section id="projects" class="bg-background py-[clamp(60px,7vw,104px)]">
    <div class="mx-auto grid max-w-[var(--container-max)] grid-cols-[repeat(auto-fit,minmax(420px,1fr))] items-stretch gap-[clamp(28px,2.6vw,40px)] px-[clamp(20px,4vw,48px)]">
      <div class="flex flex-col border border-border bg-background shadow-floating">
        <div class="flex flex-col gap-[14px] border-b border-border px-[26px] pt-[26px] pb-[22px]">
          <div class="flex items-center gap-[14px]"><span class="h-px w-[34px] bg-brass-500"/><span class="text-eyebrow text-brass-700 font-bold tracking-[0.24em] uppercase">Ứng dụng</span></div>
          <h2 class="text-display m-0 leading-[1.15] font-bold tracking-[-0.028em]">Dự án &amp; công trình</h2>
        </div>
        <div class="flex flex-1 flex-col">
          <NuxtLink v-for="proj in projects" :key="proj.id" :to="`/du-an/${proj.slug}`" class="group grid flex-1 grid-cols-[104px_1fr] items-center gap-[20px] border-b border-border px-[26px] py-[20px] text-inherit no-underline last:border-b-0 hover:bg-surface">
            <UiResponsiveImage
              :image="proj.image"
              :alt="proj.title"
              sizes="(min-width: 1024px) 25vw, 50vw"
              class="kb-product-image-frame aspect-square w-full bg-surface object-cover transition duration-500 group-hover:scale-[1.02]"
            />
            <span class="flex min-w-0 flex-col gap-[6px]"><strong class="text-heading leading-[1.35] tracking-[-0.015em]">{{ proj.title }}</strong><span class="text-caption text-neutral-600 leading-[1.65]">{{ proj.description }}</span></span>
          </NuxtLink>
        </div>
      </div>

      <div id="guides" class="flex flex-col border border-border bg-background shadow-floating">
        <div class="flex flex-col gap-[14px] border-b border-border px-[26px] pt-[26px] pb-[22px]">
          <div class="flex items-center gap-[14px]"><span class="h-px w-[34px] bg-brass-500"/><span class="text-eyebrow text-brass-700 font-bold tracking-[0.24em] uppercase">Kiến thức</span></div>
          <h2 class="text-display m-0 leading-[1.15] font-bold tracking-[-0.028em]">Tư vấn lựa chọn</h2>
        </div>
        <div class="flex flex-1 flex-col">
          <NuxtLink v-for="art in articles" :key="art.id" :to="`/tin-tuc/${art.slug}`" class="group grid flex-1 grid-cols-[104px_1fr_20px] items-center gap-[20px] border-b border-border px-[26px] py-[20px] text-inherit no-underline last:border-b-0 hover:bg-surface">
            <UiResponsiveImage
              :image="art.image"
              alt="Ảnh bài viết"
              sizes="(min-width: 1024px) 25vw, 50vw"
              class="kb-product-image-frame aspect-square w-full bg-surface object-cover"
            />
            <span class="flex min-w-0 flex-col gap-[5px]"><span class="text-eyebrow text-brass-700 font-bold tracking-[0.16em] uppercase">{{ art.category }}</span><strong class="text-heading leading-[1.35] tracking-[-0.015em]">{{ art.title }}</strong><span class="text-caption text-neutral-600 leading-[1.6]">{{ art.summary }}</span></span>
            <svg class="text-neutral-400" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m9 18 6-6-6-6"/></svg>
          </NuxtLink>
        </div>
      </div>
    </div>
  </section>
</template>
