<script setup lang="ts">
import { SOLUTIONS } from '~/utils/homeContent'
</script>

<template>
  <section id="du-an" class="mx-auto max-w-[var(--container-max)] px-[clamp(20px,4vw,48px)] py-16">
    <span class="text-eyebrow text-brass-700 font-bold tracking-[0.24em] uppercase">Giải pháp</span>
    <h2 class="text-display-lg mt-3 mb-4 font-bold tracking-[-0.03em]">Chọn theo loại công trình</h2>
    <p class="text-heading text-text-muted m-0 max-w-[720px] font-light leading-relaxed">
      Chưa biết model nào phù hợp? Bắt đầu từ loại công trình bạn đang làm.
    </p>

    <div class="mt-10 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
      <NuxtLink
        v-for="sol in SOLUTIONS"
        :key="sol.key"
        to="/san-pham"
        class="flex flex-col gap-3 border border-border p-6 text-inherit no-underline transition hover:border-brass-500 hover:shadow-floating"
      >
        <span class="text-heading font-bold">{{ sol.title }}</span>
        <span class="text-caption text-text-muted leading-relaxed">{{ sol.desc }}</span>
        <span class="mt-2 flex flex-wrap gap-2">
          <span
            v-for="tag in sol.tags"
            :key="tag"
            class="text-caption rounded-sm border border-border px-3 py-1 text-text-muted"
          >{{ tag }}</span>
        </span>
      </NuxtLink>
    </div>
  </section>
</template>
