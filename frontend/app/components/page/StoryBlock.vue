<script setup lang="ts">
withDefaults(defineProps<{
  eyebrow: string
  title: string
  body: string
  credentials: string[]
  /** Two staggered shots beside the copy. Omit and the column is dropped. */
  images?: string[]
}>(), { images: () => [] })
</script>

<template>
  <section class="bg-background py-[clamp(48px,5vw,80px)]">
    <div class="kb-story-grid mx-auto max-w-[var(--container-max)] items-center gap-[clamp(32px,4vw,64px)] px-[clamp(20px,4vw,48px)]">
      <div class="flex flex-col gap-[18px]">
        <span class="text-eyebrow text-brass-700 font-bold tracking-[0.22em] uppercase">{{ eyebrow }}</span>
        <h2 class="text-display-lg m-0 leading-[1.15] font-bold tracking-[-0.02em]">{{ title }}</h2>

        <!-- Pre-rendered by Drupal's text format. -->
        <div class="text-body kb-prose text-text-muted leading-[1.9]" v-html="body" />

        <div v-if="credentials.length" class="kb-credentials gap-[14px] pt-[8px]">
          <div v-for="item in credentials" :key="item" class="flex items-start gap-[10px]">
            <svg class="text-brass-700 mt-[3px] shrink-0" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6"><path d="M20 6 9 17l-5-5"/></svg>
            <span class="text-caption text-text leading-[1.6] font-bold">{{ item }}</span>
          </div>
        </div>
      </div>

      <div v-if="images.length" class="grid grid-cols-2 gap-[14px]">
        <div
          v-for="(src, i) in images.slice(0, 2)"
          :key="src"
          class="group border-border relative aspect-[3/4] overflow-hidden border bg-white"
          :class="i === 0 ? 'mt-[28px]' : ''"
        >
          <img :src="src" :alt="title" class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.05]" loading="lazy">
        </div>
      </div>
    </div>
  </section>
</template>
