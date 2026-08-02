<script setup lang="ts">
import { fetchProject } from '~/services/pages'
const slug = String(useRoute().params.slug)
const { data, error } = await useAsyncData(`project:${slug}`, () => fetchProject(slug))
if (error.value || !data.value?.data) throw createError({ statusCode: 404, statusMessage: 'Không tìm thấy dự án', fatal: true })
const project = computed(() => data.value!.data)
useSeoMeta({ title: () => `${project.value.title} | Keybolts`, description: () => project.value.description })
</script>

<template>
  <div>
    <div class="border-b border-border bg-surface"><div class="mx-auto max-w-[1360px] px-[clamp(20px,4vw,48px)] py-4"><LayoutBreadcrumb :items="[{ label: 'Trang chủ', url: '/' }, { label: 'Dự án', url: '/du-an' }, { label: project.title, url: `/du-an/${project.slug}` }]" /></div></div>
    <header class="bg-charcoal-900 py-[clamp(40px,5vw,70px)] text-white"><div class="mx-auto flex max-w-[920px] flex-col gap-4 px-[clamp(20px,4vw,48px)]"><span class="text-gold-200 text-caption font-bold tracking-[.2em] uppercase">{{ project.type }}</span><h1 class="text-display-lg m-0 font-bold leading-tight">{{ project.title }}</h1><p class="text-heading m-0 max-w-3xl leading-relaxed text-white/80">{{ project.description }}</p></div></header>
    <main class="mx-auto grid max-w-[1180px] gap-10 px-[clamp(20px,4vw,48px)] py-[clamp(36px,5vw,72px)] lg:grid-cols-[minmax(0,1fr)_320px]">
      <img :src="project.image" :alt="project.title" class="aspect-[4/3] h-full max-h-[620px] w-full border border-border object-cover">
      <aside class="border-t-3 border-brass-500 bg-surface p-7 lg:sticky lg:top-32 lg:self-start"><span class="text-caption text-brass-700 font-bold tracking-[.16em] uppercase">Sản phẩm / giải pháp</span><h2 class="text-display mt-4 mb-3 font-bold">{{ project.products }}</h2><p class="text-body text-text-muted m-0 leading-relaxed">{{ project.description }}</p><NuxtLink to="/lien-he" class="mt-6 inline-flex rounded-sm bg-charcoal-900 px-6 py-3 text-sm font-bold text-gold-200 no-underline">Nhận tư vấn</NuxtLink></aside>
    </main>
  </div>
</template>
