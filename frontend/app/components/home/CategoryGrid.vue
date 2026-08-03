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
  <section id="categories" class="mx-auto max-w-[var(--container-max)] px-[clamp(20px,4vw,48px)] py-[clamp(60px,7vw,104px)]">
    <div class="mb-7 flex flex-col items-center gap-3.5 text-center">
    <span class="text-eyebrow text-brass-700 font-bold tracking-[0.24em] uppercase">— Danh mục</span>
    <h2 class="text-display-lg m-0 font-bold tracking-[-0.03em]">Khám phá sản phẩm</h2>
    <p class="text-heading text-text-muted m-0 max-w-[720px] font-light leading-relaxed">
      Chọn theo loại cửa và nhu cầu sử dụng — mỗi dòng sản phẩm có nhiều kích thước và màu
      hoàn thiện.
    </p>
    </div>
    <div class="mb-7 flex justify-center gap-2">
      <button type="button" aria-label="Danh mục trước" class="grid size-11 cursor-pointer place-items-center border border-border bg-background text-xl transition hover:border-brass-500 hover:bg-charcoal-900 hover:text-white" @click="scroll(-1)">←</button>
      <button type="button" aria-label="Danh mục tiếp theo" class="grid size-11 cursor-pointer place-items-center border border-border bg-background text-xl transition hover:border-brass-500 hover:bg-charcoal-900 hover:text-white" @click="scroll(1)">→</button>
    </div>

    <div ref="track" class="kb-track flex snap-x snap-mandatory gap-6 overflow-x-auto scroll-smooth pb-2">
      <NuxtLink
        v-for="cat in categories"
        :key="cat.id"
        :to="{ path: '/san-pham', query: { category: String(cat.id) } }"
        class="group relative block aspect-[3/3.6] w-[clamp(230px,21vw,286px)] flex-none snap-start overflow-hidden bg-charcoal-900 text-white no-underline"
      >
        <img v-if="cat.image" :src="cat.image" :alt="cat.name" class="absolute inset-0 h-full w-full object-cover transition duration-500 group-hover:scale-105">
        <span class="absolute inset-0 bg-gradient-to-t from-charcoal-900 via-charcoal-900/45 to-transparent" />
        <span class="text-caption text-gold-200 absolute top-5 left-5.5 font-bold tracking-[0.2em]">{{ cat.number }}</span>
        <span class="absolute right-0 bottom-0 left-0 flex flex-col gap-2 p-5.5">
          <span class="text-heading font-bold">{{ cat.name }}</span>
          <span v-if="cat.desc" class="text-caption leading-relaxed text-white/65">{{ cat.desc }}</span>
          <span class="text-caption text-gold-200 pt-2 font-bold">Khám phá →</span>
        </span>
      </NuxtLink>
    </div>
  </section>
</template>
