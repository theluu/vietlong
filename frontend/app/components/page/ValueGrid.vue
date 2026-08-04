<script setup lang="ts">
import type { ValueItem } from '~/types/page'

defineProps<{ eyebrow: string; title: string; values: ValueItem[] }>()

/**
 * The prototype gives each commitment its own icon, but the API returns only a
 * title and a description. Mapping by position keeps the design without adding
 * a Drupal field for what is decoration.
 */
const ICONS = ['shield', 'award', 'truck', 'headset'] as const
const iconFor = (index: number) => ICONS[index % ICONS.length]
</script>

<template>
  <section class="bg-charcoal-900 py-[clamp(48px,5vw,80px)] text-white">
    <div class="mx-auto max-w-[var(--container-max)] px-[clamp(20px,4vw,48px)]">
      <div class="mb-[44px] flex flex-col items-center gap-[14px] text-center">
        <span class="text-eyebrow text-gold-200 flex items-center gap-[14px] font-bold tracking-[0.22em] uppercase">
          <span class="bg-gold-200 h-[2px] w-[36px]"/>{{ eyebrow }}
          <span class="bg-gold-200 h-[2px] w-[36px]"/>
        </span>
        <h2 class="text-display-lg m-0 font-bold tracking-[-0.02em]">{{ title }}</h2>
      </div>

      <div class="kb-value-grid gap-[clamp(16px,1.6vw,24px)]">
        <div
          v-for="(value, i) in values"
          :key="value.title"
          class="hover:border-gold-200 flex flex-col gap-[12px] border border-white/16 p-[30px] transition duration-200 ease-in-out hover:bg-white/4"
        >
          <span class="border-gold-200/40 text-gold-200 flex h-[44px] w-[44px] items-center justify-center rounded-full border">
            <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
              <template v-if="iconFor(i) === 'shield'"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></template>
              <template v-else-if="iconFor(i) === 'award'"><circle cx="12" cy="8" r="6"/><path d="m8.21 13.89-1.2 7.11L12 18l4.99 3-1.2-7.12"/></template>
              <template v-else-if="iconFor(i) === 'truck'"><path d="M1 3h15v13H1z"/><path d="M16 8h4l3 3v5h-7z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></template>
              <template v-else><path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/></template>
            </svg>
          </span>
          <span class="text-heading font-bold">{{ value.title }}</span>
          <span class="text-caption leading-[1.8] text-white/70">{{ value.desc }}</span>
        </div>
      </div>
    </div>
  </section>
</template>
