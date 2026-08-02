<script setup lang="ts">
import { SOLUTIONS } from '~/utils/homeContent'
</script>

<template>
  <section id="du-an" class="mx-auto max-w-[var(--container-max)] px-[clamp(20px,4vw,48px)] py-16">
    <div class="text-center"><span class="text-eyebrow text-brass-700 font-bold tracking-[0.24em] uppercase">Giải pháp</span>
    <h2 class="text-display-lg mt-3 mb-4 font-bold tracking-[-0.03em]">Chọn theo loại công trình</h2>
    <p class="text-heading text-text-muted mx-auto max-w-[720px] font-light leading-relaxed">
      Chưa biết model nào phù hợp? Bắt đầu từ loại công trình bạn đang làm.
    </p><div class="mt-6 flex justify-center gap-2"><span class="grid size-10 place-items-center border border-border">←</span><span class="grid size-10 place-items-center border border-border">→</span></div></div>

    <div class="mt-10 grid grid-cols-1 gap-6 md:grid-cols-3">
      <NuxtLink
        v-for="sol in SOLUTIONS.slice(0, 3)"
        :key="sol.key"
        to="/san-pham"
        class="group flex flex-col overflow-hidden border border-border bg-background text-inherit no-underline transition hover:shadow-floating"
      >
        <div class="relative aspect-[16/10] overflow-hidden"><img :src="sol.image" :alt="sol.title" class="size-full object-cover transition duration-500 group-hover:scale-105"><div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent"/><span class="text-display absolute bottom-5 left-6 font-bold text-white">{{ sol.title }}</span></div>
        <div class="flex flex-1 flex-col gap-3 p-6"><span class="text-caption text-text-muted leading-relaxed">{{ sol.desc }}</span>
        <span class="mt-auto flex flex-wrap gap-2 pt-2">
          <span
            v-for="tag in sol.tags"
            :key="tag"
            class="text-caption rounded-sm border border-border px-3 py-1 text-text-muted"
          >{{ tag }}</span>
        </span></div>
      </NuxtLink>
    </div>
  </section>
</template>
