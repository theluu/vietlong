<script setup lang="ts">
import type { HomeCategory } from '~/services/homepage'

defineProps<{ categories: HomeCategory[] }>()

const track = ref<HTMLElement | null>(null)
const scroll = (direction: number) => {
  const card = track.value?.firstElementChild as HTMLElement | null
  track.value?.scrollBy({ left: direction * ((card?.offsetWidth ?? 286) + 24) * 2, behavior: 'smooth' })
}
</script>

<template>
  <section id="categories" class="bg-[var(--color-background)] py-[clamp(60px,7vw,104px)]">
    <div class="mx-auto max-w-[var(--container-max)] px-[clamp(20px,4vw,48px)]">
      <div class="mb-[44px] flex flex-col items-center gap-[26px] text-center">
        <div class="flex max-w-[780px] flex-col items-center gap-[14px]">
          <div class="flex items-center gap-[14px]">
            <span class="h-px w-[34px] bg-brass-500" />
            <span class="text-eyebrow text-brass-700 font-bold tracking-[0.24em] uppercase">Danh mục</span>
          </div>
          <h2 class="m-0 text-[clamp(var(--text-display),3.6vw,var(--text-display-lg))] leading-[1.1] font-bold tracking-[-0.032em]">Khám phá sản phẩm</h2>
          <p class="text-heading text-text m-0 font-semibold leading-[1.7]">
            Chọn theo loại cửa và nhu cầu sử dụng — mỗi dòng sản phẩm có nhiều kích thước và màu hoàn thiện.
          </p>
        </div>
        <div class="flex items-center gap-[12px]">
          <button type="button" aria-label="Trước" class="grid size-[52px] cursor-pointer place-items-center border border-neutral-300 bg-transparent text-charcoal-900 transition hover:border-charcoal-900 hover:bg-charcoal-900 hover:text-gold-200" @click="scroll(-1)"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m15 18-6-6 6-6"/></svg></button>
          <button type="button" aria-label="Sau" class="grid size-[52px] cursor-pointer place-items-center border border-neutral-300 bg-transparent text-charcoal-900 transition hover:border-charcoal-900 hover:bg-charcoal-900 hover:text-gold-200" @click="scroll(1)"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m9 18 6-6-6-6"/></svg></button>
        </div>
      </div>

      <div ref="track" class="kb-track flex snap-x snap-mandatory gap-[24px] overflow-x-auto scroll-smooth pb-[8px]">
      <NuxtLink
        v-for="cat in categories"
        :key="cat.id"
        :to="{ path: '/san-pham', query: { category: String(cat.id) } }"
        class="kb-home-category-card group block min-w-0 snap-start overflow-hidden bg-charcoal-900 text-inherit no-underline"
      >
        <span class="kb-hero-frame relative block aspect-[3/3.6] w-full overflow-hidden">
          <UiResponsiveImage :image="cat.image" :alt="cat.name" sizes="(min-width: 1024px) 25vw, 50vw" class="absolute inset-0 h-full w-full bg-[var(--color-surface)] object-cover transition duration-500 group-hover:scale-105" />
          <span class="absolute inset-0 bg-[linear-gradient(to_top,rgba(40,45,48,.94)_0%,rgba(40,45,48,.42)_42%,rgba(40,45,48,.05)_72%)]" />
          <span class="text-caption text-gold-200 absolute top-[20px] left-[22px] font-bold tracking-[0.2em]">{{ cat.number }}</span>
          <span class="absolute right-0 bottom-0 left-0 flex flex-col gap-[7px] px-[22px] py-[24px]">
            <span class="text-display font-bold leading-[1.16] tracking-[-0.02em] text-white">{{ cat.name }}</span>
            <span v-if="cat.desc" class="text-caption leading-[1.6] text-white/72">{{ cat.desc }}</span>
            <span class="text-eyebrow text-gold-200 mt-[10px] flex items-center gap-[8px] font-bold tracking-[0.16em] uppercase">Khám phá <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M5 12h14"/><path d="m13 6 6 6-6 6"/></svg></span>
          </span>
        </span>
      </NuxtLink>
      </div>
    </div>
  </section>
</template>
