<script setup lang="ts">
import { fetchProjects, fetchProjectsPage } from '~/services/pages'

const { data } = await useAsyncData('page:projects', fetchProjectsPage)
const { data: projectData } = await useAsyncData('projects', fetchProjects)
const page = computed(() => data.value?.data)
const projects = computed(() => projectData.value?.data ?? [])
if (!page.value) throw createError({ statusCode: 404, statusMessage: 'Không tìm thấy trang', fatal: true })

const filters = [
  { key: 'all', label: 'Tất cả' }, { key: 'biet-thu', label: 'Biệt thự' },
  { key: 'khach-san', label: 'Khách sạn' }, { key: 'can-ho', label: 'Căn hộ' },
  { key: 'van-phong', label: 'Văn phòng' },
] as const
const active = ref<(typeof filters)[number]['key']>('all')
const currentPage = ref(1)
const filtered = computed(() => active.value === 'all' ? projects.value : projects.value.filter(project => project.typeKey === active.value))
const pages = computed(() => Math.max(1, Math.ceil(filtered.value.length / 6)))
const visible = computed(() => filtered.value.slice((currentPage.value - 1) * 6, currentPage.value * 6))
const rangeLabel = computed(() => {
  const start = filtered.value.length ? (currentPage.value - 1) * 6 + 1 : 0
  const end = start + visible.value.length - (visible.value.length ? 1 : 0)
  return `Hiển thị ${start}–${end} trong ${filtered.value.length} mục`
})
function pick(key: (typeof filters)[number]['key']) { active.value = key; currentPage.value = 1 }
useSeoMeta({ title: () => `${page.value?.title} | Keybolts`, description: () => page.value?.subtitle })
</script>

<template>
  <div v-if="page">
    <PageHero :eyebrow="page.eyebrow" :title="page.title" :subtitle="page.subtitle" :breadcrumb="[{ label: 'Trang chủ', url: '/' }, { label: 'Dự án', url: '/du-an' }]" />
    <main class="mx-auto max-w-[var(--container-max)] px-[clamp(20px,4vw,48px)] py-[clamp(40px,4.5vw,64px)]">
      <div class="mb-8 flex flex-wrap justify-center gap-2.5">
        <button v-for="filter in filters" :key="filter.key" type="button" class="text-caption cursor-pointer rounded-sm border px-5.5 py-3 font-bold tracking-[.08em] uppercase" :class="filter.key === active ? 'border-charcoal-900 bg-charcoal-900 text-gold-200' : 'border-neutral-300 bg-transparent text-text'" @click="pick(filter.key)">{{ filter.label }}</button>
      </div>
      <div class="grid grid-cols-1 gap-[clamp(16px,1.8vw,26px)] md:grid-cols-2 lg:grid-cols-3"><PageProjectCard v-for="project in visible" :key="project.id" :project="project" /></div>
      <div class="mt-11 flex flex-wrap items-center justify-center gap-2">
        <button type="button" class="h-11 w-11 border border-neutral-300" :disabled="currentPage === 1" @click="currentPage--">←</button>
        <button v-for="number in pages" :key="number" type="button" class="h-11 min-w-11 border px-3 font-bold" :class="number === currentPage ? 'border-charcoal-900 bg-charcoal-900 text-gold-200' : 'border-neutral-300'" @click="currentPage = number">{{ number }}</button>
        <button type="button" class="h-11 w-11 border border-neutral-300" :disabled="currentPage === pages" @click="currentPage++">→</button>
      </div>
      <p class="text-caption text-text-muted mt-4 text-center">{{ rangeLabel }}</p>
    </main>
  </div>
</template>
