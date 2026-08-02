<script setup lang="ts">
import { ARTICLES, PROJECT_TYPES } from '~/utils/homeContent'
</script>

<template>
  <section id="tin-tuc" class="bg-surface py-20">
    <div class="mx-auto grid max-w-[var(--container-max)] gap-8 px-[clamp(20px,4vw,48px)] lg:grid-cols-2">
      <div class="bg-background p-7 shadow-sm md:p-9">
        <div class="mb-7 flex items-end justify-between border-b border-border pb-5">
          <div><span class="text-eyebrow text-brass-700 font-bold tracking-[0.2em] uppercase">Ứng dụng</span><h2 class="text-display mt-2 mb-0 font-bold">Dự án &amp; công trình</h2></div>
          <NuxtLink to="/du-an" class="text-caption text-brass-700 font-bold uppercase no-underline">Xem tất cả →</NuxtLink>
        </div>
        <NuxtLink v-for="proj in PROJECT_TYPES" :key="proj.key" to="/du-an" class="group grid grid-cols-[120px_1fr] gap-5 border-b border-border py-5 text-inherit no-underline last:border-0">
          <img :src="proj.image" :alt="proj.title" class="h-24 w-full object-cover">
          <div><strong class="text-heading group-hover:text-brass-700">{{ proj.title }}</strong><p class="text-caption text-text-muted mt-2 mb-0 line-clamp-2 leading-relaxed">{{ proj.desc }}</p></div>
        </NuxtLink>
      </div>

      <div class="bg-background p-7 shadow-sm md:p-9">
        <div class="mb-7 flex items-end justify-between border-b border-border pb-5">
          <div><span class="text-eyebrow text-brass-700 font-bold tracking-[0.2em] uppercase">Kiến thức</span><h2 class="text-display mt-2 mb-0 font-bold">Tư vấn lựa chọn</h2></div>
          <NuxtLink to="/tin-tuc" class="text-caption text-brass-700 font-bold uppercase no-underline">Xem tất cả →</NuxtLink>
        </div>
        <NuxtLink v-for="art in ARTICLES" :key="art.key" to="/tin-tuc" class="group grid grid-cols-[100px_1fr] gap-5 border-b border-border py-4 text-inherit no-underline last:border-0">
          <img :src="art.image" :alt="art.title" class="h-20 w-full bg-surface object-cover">
          <div><span class="text-eyebrow text-brass-700 font-bold uppercase">{{ art.category }}</span><strong class="text-body mt-1 block leading-snug group-hover:text-brass-700">{{ art.title }}</strong></div>
        </NuxtLink>
      </div>
    </div>
  </section>
</template>
