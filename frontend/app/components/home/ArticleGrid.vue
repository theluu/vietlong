<script setup lang="ts">
import { ARTICLES } from '~/utils/homeContent'
</script>

<template>
  <section class="bg-surface">
    <div class="mx-auto max-w-[var(--container-max)] px-[clamp(20px,4vw,48px)] py-16">
      <span class="text-eyebrow text-brass-700 font-bold tracking-[0.24em] uppercase">
        Kiến thức
      </span>
      <h2 class="text-display-lg mt-3 mb-10 font-bold tracking-[-0.03em]">Tư vấn lựa chọn</h2>

      <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <NuxtLink
          v-for="art in ARTICLES"
          :key="art.key"
          to="/tin-tuc"
          class="flex flex-col gap-2 border border-border bg-background p-6 text-inherit no-underline transition hover:border-brass-500"
        >
          <span class="text-caption text-brass-700 font-bold tracking-[0.14em] uppercase">
            {{ art.category }}
          </span>
          <span class="text-heading font-bold leading-snug">{{ art.title }}</span>
          <span class="text-caption text-text-muted leading-relaxed">{{ art.summary }}</span>
        </NuxtLink>
      </div>
    </div>
  </section>
</template>
