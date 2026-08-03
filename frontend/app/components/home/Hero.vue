<script setup lang="ts">
import { HERO_STATS } from '~/utils/homeContent'

defineProps<{ image?: string | null }>()
</script>

<template>
  <section class="relative overflow-hidden bg-charcoal-900 text-white">
    <!-- Radial gold wash from the prototype. -->
    <div
      class="pointer-events-none absolute top-[-15%] left-[38%] h-[130%] w-[60%]"
      style="background: radial-gradient(ellipse at 40% 45%, rgba(198,145,72,0.32), transparent 60%)"
    />

    <div class="relative mx-auto grid max-w-[var(--container-max)] items-stretch md:grid-cols-2">
      <div
        class="flex flex-col justify-center gap-[26px] px-[clamp(20px,4vw,48px)] py-[clamp(56px,7vw,110px)]"
      >
        <div class="flex items-center gap-4">
          <span class="h-[2px] w-12 bg-gold-200" />
          <span class="text-eyebrow text-gold-200 font-bold tracking-[0.24em] uppercase">
            Keybolts Collection
          </span>
        </div>

        <h1
          class="m-0 text-[clamp(40px,5.4vw,72px)] leading-[1.02] font-bold tracking-[-0.035em] text-balance"
        >
          Khóa cửa<br>
          <span class="kb-hero-gradient">đẳng cấp</span> cho<br>
          từng công trình
        </h1>

        <p class="text-heading m-0 max-w-[430px] leading-[1.75] font-light text-white/75">
          Khóa đồng, khóa vân tay, khóa thẻ từ khách sạn và phụ kiện cửa nhập khẩu —
          tuyển chọn theo từng loại cửa, bảo hành 5–10 năm.
        </p>

        <div class="mt-1 flex flex-wrap gap-4">
          <NuxtLink
            to="#categories"
            class="text-body inline-flex items-center gap-3 rounded-sm bg-gold-200 px-[34px] py-[18px] font-bold tracking-[0.07em] text-charcoal-900 uppercase no-underline transition hover:-translate-y-0.5 hover:bg-white"
          >Xem bộ sưu tập<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" aria-hidden="true"><path d="M5 12h14"/><path d="m13 6 6 6-6 6"/></svg></NuxtLink>
          <NuxtLink
            to="#consultation"
            class="text-body inline-flex items-center rounded-sm border border-white/30 px-[34px] py-[18px] tracking-[0.07em] text-white uppercase no-underline hover:border-gold-200 hover:text-gold-200"
          >Tư vấn miễn phí</NuxtLink>
        </div>
      </div>

      <div class="relative min-h-[clamp(360px,46vw,620px)]">
        <div class="kb-hero-frame absolute inset-0 overflow-hidden bg-neutral-700">
          <img
            v-if="image"
            :src="image"
            alt="Khóa cửa cao cấp Keybolts"
            class="h-full w-full object-cover"
            fetchpriority="high"
          >
        </div>
        <div
          class="pointer-events-none absolute inset-0"
          style="background: linear-gradient(90deg, var(--color-charcoal-900) 0%, rgba(40,45,48,0.35) 26%, transparent 55%)"
        />
        <div
          class="absolute right-0 bottom-0 flex border-t border-l border-gold-200/30 bg-charcoal-900/86 backdrop-blur-sm"
        >
          <div
            v-for="(s, i) in HERO_STATS"
            :key="s.label"
            class="flex flex-col gap-1 px-7 py-[22px]"
            :class="i < HERO_STATS.length - 1 ? 'border-r border-white/14' : ''"
          >
            <span class="text-display text-gold-200 leading-none font-bold tracking-[-0.03em]">
              {{ s.value }}
            </span>
            <span class="text-caption tracking-[0.06em] text-white/66">{{ s.label }}</span>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
