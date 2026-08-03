<script setup lang="ts">
import type { ProductCard } from '~/types/product'
import { FEATURED_TABS } from '~/utils/homeContent'

const props = defineProps<{ featured: Record<string, ProductCard[]> }>()

// Every group ships in the one /homepage response, so switching is instant.
const tabs = computed(() => FEATURED_TABS.filter(t => (props.featured[t.key]?.length ?? 0) > 0))
const active = ref(tabs.value[0]?.key ?? 'dong')
const items = computed(() => props.featured[active.value] ?? [])
const track = ref<HTMLElement | null>(null)
const scroll = (direction: number) => {
  const card = track.value?.firstElementChild as HTMLElement | null
  track.value?.scrollBy({ left: direction * ((card?.offsetWidth ?? 260) + 24) * 2, behavior: 'smooth' })
}
</script>

<template>
  <section class="bg-surface">
    <div class="mx-auto max-w-[var(--container-max)] px-[clamp(20px,4vw,48px)] py-16">
      <div class="text-center">
        <span class="text-eyebrow text-brass-700 font-bold tracking-[0.24em] uppercase">Bán chạy</span>
        <h2 class="text-display-lg mt-3 mb-7 font-bold tracking-[-0.03em]">Sản phẩm nổi bật</h2>
        <div class="mb-7 flex justify-center gap-2">
          <button type="button" aria-label="Sản phẩm trước" class="grid size-11 cursor-pointer place-items-center border border-border bg-background text-xl hover:border-brass-500 hover:bg-charcoal-900 hover:text-white" @click="scroll(-1)">←</button>
          <button type="button" aria-label="Sản phẩm tiếp theo" class="grid size-11 cursor-pointer place-items-center border border-border bg-background text-xl hover:border-brass-500 hover:bg-charcoal-900 hover:text-white" @click="scroll(1)">→</button>
        </div>
      </div>

      <div role="tablist" class="mb-9 flex flex-wrap justify-center gap-x-8 gap-y-3 border-b border-border">
        <button
          v-for="t in tabs"
          :key="t.key"
          type="button"
          role="tab"
          :aria-selected="active === t.key"
          :data-tab="t.key"
          class="text-caption -mb-px cursor-pointer border-x-0 border-t-0 border-b-2 bg-transparent px-1 py-4 font-bold tracking-[0.06em] uppercase transition"
          :class="active === t.key
            ? 'text-charcoal-900 border-brass-600'
            : 'text-text-muted border-transparent hover:text-charcoal-900'"
          @click="active = t.key"
        >{{ t.label }}</button>
      </div>

      <div ref="track" class="kb-track flex snap-x snap-mandatory gap-6 overflow-x-auto scroll-smooth pb-2">
        <NuxtLink v-for="p in items" :key="p.id" :to="`/${p.slug}`" class="group flex w-[clamp(238px,22vw,310px)] flex-none snap-start flex-col bg-background p-4 text-inherit no-underline shadow-sm">
          <div class="aspect-[4/4.35] overflow-hidden bg-surface p-5">
            <img v-if="p.image" :src="p.image.url" :alt="p.image.alt" class="size-full object-contain transition duration-500 group-hover:scale-105">
          </div>
          <span class="text-eyebrow mt-5 text-brass-700 font-bold tracking-[0.14em] uppercase">{{ p.category?.name }}</span>
          <strong class="text-heading mt-2 min-h-[3.2em] leading-snug">{{ p.name }}</strong>
          <span class="text-caption text-text-muted mt-1">{{ p.model }}<template v-if="p.finish"> · {{ p.finish.name }}</template></span>
          <span class="text-caption mt-5 w-fit bg-charcoal-900 px-5 py-3 font-bold tracking-[0.08em] text-white uppercase">Liên hệ tư vấn</span>
        </NuxtLink>
      </div>
    </div>
  </section>
</template>
