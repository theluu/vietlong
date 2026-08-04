<script setup lang="ts">
import type { CtaLink, Fact } from '~/types/page'

defineProps<{
  eyebrow: string
  title: string
  subtitle: string
  image?: string
  caption?: string
  ctaPrimary?: CtaLink
  ctaSecondary?: CtaLink
  facts: Fact[]
}>()

const isExternal = (url: string) => /^(https?:|tel:|mailto:)/.test(url)
</script>

<template>
  <section class="kb-hero-glow bg-charcoal-900 relative overflow-hidden text-white">
    <div class="kb-about-hero relative mx-auto max-w-[var(--container-max)] items-center gap-[clamp(32px,4vw,64px)] px-[clamp(20px,4vw,48px)] pt-[clamp(52px,6vw,90px)]">
      <div class="flex flex-col gap-[20px]">
        <span class="text-eyebrow text-gold-200 flex items-center gap-[14px] font-bold tracking-[0.24em] uppercase">
          <span class="bg-gold-200 h-[2px] w-[36px]"/>{{ eyebrow }}
        </span>
        <h1 class="m-0 text-[clamp(var(--text-display),4.2vw,var(--text-display-xl))] leading-[1.06] font-bold tracking-[-0.03em]">{{ title }}</h1>
        <p class="text-heading m-0 max-w-[560px] leading-[1.8] font-normal text-white/82">{{ subtitle }}</p>

        <div v-if="ctaPrimary?.url || ctaSecondary?.url" class="flex flex-wrap gap-[14px] pt-[6px]">
          <!-- `tel:` links must stay plain anchors; NuxtLink would try to route
               them. Two explicit branches rather than a dynamic `:is`, which
               resolves NuxtLink too late during SSR and warns. -->
          <component
            :is="isExternal(ctaPrimary.url) ? 'a' : 'NuxtLink'"
            v-if="ctaPrimary?.url"
            v-bind="isExternal(ctaPrimary.url) ? { href: ctaPrimary.url } : { to: ctaPrimary.url }"
            class="text-body bg-gold-200 text-charcoal-900 inline-flex items-center gap-[11px] rounded-sm px-[32px] py-[16px] font-bold tracking-[0.06em] uppercase no-underline hover:bg-white"
          >
            <svg width="17" height="17" viewBox="0 0 24 24" fill="currentColor"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
            {{ ctaPrimary.label }}
          </component>

          <component
            :is="isExternal(ctaSecondary.url) ? 'a' : 'NuxtLink'"
            v-if="ctaSecondary?.url"
            v-bind="isExternal(ctaSecondary.url) ? { href: ctaSecondary.url } : { to: ctaSecondary.url }"
            class="text-body inline-flex items-center rounded-sm border border-white/40 px-[32px] py-[16px] font-bold tracking-[0.06em] text-white uppercase no-underline hover:bg-white/10"
          >{{ ctaSecondary.label }}</component>
        </div>
      </div>

      <div v-if="image" class="group border-gold-200/30 relative aspect-[4/5] overflow-hidden border bg-white">
        <img :src="image" :alt="caption || title" class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.05]" fetchpriority="high">
        <span
          v-if="caption"
          class="text-caption from-charcoal-900/92 absolute inset-x-0 bottom-0 bg-gradient-to-t to-transparent px-[22px] py-[18px] tracking-[0.06em] text-white"
        >{{ caption }}</span>
      </div>
    </div>

    <!-- The prototype keeps the numbers inside the hero, on the dark ground —
         not as a separate light band below it. -->
    <div v-if="facts.length" class="relative mt-[clamp(40px,4vw,64px)] border-t border-white/14">
      <div class="kb-fact-strip mx-auto max-w-[var(--container-max)] px-[clamp(20px,4vw,48px)]">
        <div v-for="fact in facts" :key="fact.label" class="flex flex-col gap-[6px] border-r border-white/14 px-[22px] py-[26px]">
          <span class="text-display-lg text-gold-200 leading-none font-bold">{{ fact.number }}</span>
          <span class="text-caption tracking-[0.1em] text-white/62 uppercase">{{ fact.label }}</span>
        </div>
      </div>
    </div>
  </section>
</template>
