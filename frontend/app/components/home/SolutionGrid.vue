<script setup lang="ts">
import { SOLUTIONS } from '~/utils/homeContent'

const track = ref<HTMLElement | null>(null)
const scroll = (direction: number) => {
  const card = track.value?.firstElementChild as HTMLElement | null
  track.value?.scrollBy({ left: direction * ((card?.offsetWidth ?? 340) + 24) * 2, behavior: 'smooth' })
}
</script>

<template>
  <section id="solutions" class="bg-background py-[clamp(60px,7vw,104px)]">
    <div class="mx-auto max-w-[var(--container-max)] px-[clamp(20px,4vw,48px)]">
      <div class="mb-[44px] flex flex-col items-center gap-[26px] text-center">
        <div class="flex max-w-[780px] flex-col items-center gap-[14px]">
          <div class="flex items-center gap-[14px]"><span class="h-px w-[34px] bg-brass-500"/><span class="text-eyebrow text-brass-700 font-bold tracking-[0.24em] uppercase">Giải pháp</span></div>
          <h2 class="m-0 text-[clamp(var(--text-display),3.6vw,var(--text-display-lg))] leading-[1.1] font-bold tracking-[-0.032em]">Chọn theo loại công trình</h2>
          <p class="text-heading text-text-muted m-0 font-light leading-[1.7]">Chưa biết model nào phù hợp? Bắt đầu từ loại công trình bạn đang làm.</p>
        </div>
        <div class="flex justify-center gap-[12px]"><button type="button" aria-label="Trước" class="grid size-[52px] cursor-pointer place-items-center border border-neutral-300 bg-transparent transition hover:border-charcoal-900 hover:bg-charcoal-900 hover:text-gold-200" @click="scroll(-1)"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m15 18-6-6 6-6"/></svg></button><button type="button" aria-label="Sau" class="grid size-[52px] cursor-pointer place-items-center border border-neutral-300 bg-transparent transition hover:border-charcoal-900 hover:bg-charcoal-900 hover:text-gold-200" @click="scroll(1)"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m9 18 6-6-6-6"/></svg></button></div>
      </div>

    <div ref="track" class="kb-track flex snap-x snap-mandatory gap-[24px] overflow-x-auto scroll-smooth pb-[8px]">
      <NuxtLink
        v-for="sol in SOLUTIONS"
        :key="sol.key"
        to="/san-pham"
        class="kb-home-solution-card group flex min-w-0 snap-start flex-col overflow-hidden border border-border bg-background text-inherit no-underline transition hover:-translate-y-[6px] hover:shadow-floating"
      >
        <div class="kb-product-image-frame relative aspect-[16/10] overflow-hidden bg-surface"><img :src="sol.image" :alt="sol.title" class="size-full object-cover transition duration-500 group-hover:scale-105"><div class="absolute inset-0 bg-[linear-gradient(to_top,rgba(40,45,48,.9),rgba(40,45,48,.05)_60%)]"/><span class="text-display absolute bottom-[18px] left-[22px] font-bold tracking-[-0.025em] text-white">{{ sol.title }}</span></div>
        <div class="flex flex-1 flex-col gap-[16px] px-[20px] py-[22px]"><span class="text-body text-text-muted flex-1 leading-[1.7]">{{ sol.desc }}</span>
        <span class="flex flex-wrap gap-[8px]">
          <span
            v-for="tag in sol.tags"
            :key="tag"
            class="text-caption border border-border bg-surface px-[12px] py-[6px] tracking-[0.03em] text-text"
          >{{ tag }}</span>
        </span></div>
      </NuxtLink>
    </div></div>
  </section>
</template>
