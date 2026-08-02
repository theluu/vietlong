<script setup lang="ts">
defineProps<{ eyebrow: string; title: string; body: string; credentials: string[] }>()
</script>

<template>
  <section class="mx-auto grid max-w-[var(--container-max)] gap-10 px-[clamp(20px,4vw,48px)] py-16 lg:grid-cols-2">
    <div>
      <span class="text-eyebrow text-brass-700 font-bold tracking-[0.24em] uppercase">{{ eyebrow }}</span>
      <h2 class="text-display-lg mt-3 font-bold tracking-[-0.03em]">{{ title }}</h2>
    </div>
    <div class="flex flex-col gap-6">
      <!-- Pre-rendered by Drupal's text format. -->
      <div class="text-body kb-prose leading-relaxed text-text-muted" v-html="body" />
      <ul class="m-0 flex list-none flex-col gap-3 p-0">
        <li v-for="c in credentials" :key="c" class="text-body flex gap-3 border-b border-border pb-3">
          <span class="text-brass-700 font-bold">✓</span>{{ c }}
        </li>
      </ul>
    </div>
  </section>
</template>
