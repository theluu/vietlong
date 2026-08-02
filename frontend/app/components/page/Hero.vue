<script setup lang="ts">
import type { CtaLink } from '~/types/page'

defineProps<{
  eyebrow: string
  title: string
  subtitle: string
  image?: string
  caption?: string
  ctaPrimary?: CtaLink
  ctaSecondary?: CtaLink
  breadcrumb: { label: string; url: string }[]
}>()

const isExternal = (url: string) => /^(https?:|tel:|mailto:)/.test(url)
</script>

<template>
  <section class="bg-charcoal-900 text-white">
    <div class="mx-auto max-w-[var(--container-max)] px-[clamp(20px,4vw,48px)] pt-6">
      <LayoutBreadcrumb :items="breadcrumb" />
    </div>

    <div class="mx-auto grid max-w-[var(--container-max)] gap-10 px-[clamp(20px,4vw,48px)] py-14 lg:grid-cols-2">
      <div class="flex flex-col justify-center gap-5">
        <span class="text-eyebrow text-gold-200 font-bold tracking-[0.24em] uppercase">{{ eyebrow }}</span>
        <h1 class="text-display-lg m-0 font-bold tracking-[-0.03em]">{{ title }}</h1>
        <p class="text-heading m-0 leading-relaxed font-light text-white/75">{{ subtitle }}</p>

        <div v-if="ctaPrimary?.url || ctaSecondary?.url" class="mt-2 flex flex-wrap gap-4">
          <component
            :is="isExternal(ctaPrimary!.url) ? 'a' : resolveComponent('NuxtLink')"
            v-if="ctaPrimary?.url"
            :href="isExternal(ctaPrimary.url) ? ctaPrimary.url : undefined"
            :to="isExternal(ctaPrimary.url) ? undefined : ctaPrimary.url"
            class="text-body rounded-sm bg-gold-200 px-8 py-4 font-bold tracking-[0.06em] text-charcoal-900 uppercase no-underline hover:bg-white"
          >{{ ctaPrimary.label }}</component>

          <component
            :is="isExternal(ctaSecondary!.url) ? 'a' : resolveComponent('NuxtLink')"
            v-if="ctaSecondary?.url"
            :href="isExternal(ctaSecondary.url) ? ctaSecondary.url : undefined"
            :to="isExternal(ctaSecondary.url) ? undefined : ctaSecondary.url"
            class="text-body rounded-sm border border-white/30 px-8 py-4 tracking-[0.06em] text-white uppercase no-underline hover:border-gold-200 hover:text-gold-200"
          >{{ ctaSecondary.label }}</component>
        </div>
      </div>

      <div v-if="image" class="flex flex-col gap-3">
        <img :src="image" :alt="caption || title" class="w-full object-cover" fetchpriority="high">
        <span v-if="caption" class="text-caption text-white/60">{{ caption }}</span>
      </div>
    </div>
  </section>
</template>
