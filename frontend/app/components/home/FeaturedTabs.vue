<script setup lang="ts">
import type { ProductCard } from '~/types/product'
const props = defineProps<{ featured: Record<string, ProductCard[]> }>()

const home = useHome()
const section = computed(() => home.value.featuredSection)

// Every group ships in the one /homepage response, so switching is instant.
// An editor can add a tab whose group is empty; hide it rather than show a
// tab that opens onto nothing.
const tabs = computed(() =>
  section.value.tabs.filter(t => (props.featured[t.key]?.length ?? 0) > 0))
const active = ref('')
watchEffect(() => {
  if (!tabs.value.some(t => t.key === active.value)) {
    active.value = tabs.value[0]?.key ?? ''
  }
})
const items = computed(() => props.featured[active.value] ?? [])
const { track, canPrev, canNext, scroll, reset, buttonClass } = useCarousel(260)
// Switching tabs swaps the cards without resizing the track, so nothing else
// would tell the arrows their scroll range just changed.
watch(items, reset)
</script>

<template>
  <section id="products" class="border-y border-border bg-surface py-[clamp(60px,7vw,104px)]">
    <div class="mx-auto max-w-[var(--container-max)] px-[clamp(20px,4vw,48px)]">
      <div class="mb-[30px] flex flex-col items-center gap-[22px] text-center">
        <div class="flex max-w-[780px] flex-col items-center gap-[14px]">
          <div class="flex items-center gap-[14px]">
            <span class="h-px w-[34px] bg-brass-500" />
            <span class="text-eyebrow text-brass-700 font-bold tracking-[0.24em] uppercase">Bán chạy</span>
          </div>
          <h2 class="m-0 text-[clamp(var(--text-display),3.6vw,var(--text-display-lg))] leading-[1.1] font-bold tracking-[-0.032em]">Sản phẩm nổi bật</h2>
        </div>
        <!-- Below md the track is a grid with nothing to scroll, so the arrows
             would be two permanently disabled buttons. -->
        <div class="hidden items-center justify-center gap-[12px] md:flex">
          <button type="button" aria-label="Trước" :disabled="!canPrev" :class="[buttonClass, 'bg-background']" @click="scroll(-1)"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m15 18-6-6 6-6"/></svg></button>
          <button type="button" aria-label="Sau" :disabled="!canNext" :class="[buttonClass, 'bg-background']" @click="scroll(1)"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m9 18 6-6-6-6"/></svg></button>
        </div>
      </div>

      <!-- `justify-center` on a scroller makes the overflow at the *start*
           unreachable, so the first tab was clipped with no way to scroll back
           to it. On mobile the strip wraps instead of scrolling; from md it
           fits and centring is safe. -->
      <div class="mb-[30px] flex justify-center md:overflow-x-auto">
        <div role="tablist" class="flex flex-wrap justify-center border-b border-border shadow-[0_4px_10px_rgba(40,45,48,.04)] md:inline-flex md:min-w-max md:flex-nowrap">
          <button
            v-for="t in tabs"
            :key="t.key"
            type="button"
            role="tab"
            :aria-selected="active === t.key"
            :data-tab="t.key"
            class="text-body -mb-px cursor-pointer border-x-0 border-t-0 border-b-2 bg-transparent px-[12px] py-[10px] font-normal transition md:px-[14px] md:py-[14px]"
            :class="active === t.key
              ? 'text-charcoal-900 border-brass-500'
              : 'text-text-muted border-transparent hover:text-charcoal-900'"
            @click="active = t.key"
          >{{ t.label }}</button>
        </div>
      </div>

      <div ref="track" class="kb-track grid grid-cols-2 gap-[16px] md:flex md:snap-x md:snap-mandatory md:gap-[24px] md:overflow-x-auto md:scroll-smooth md:pb-[8px]">
        <article v-for="p in items" :key="p.id" class="kb-home-product-card group flex min-w-0 snap-start flex-col border border-border bg-background text-inherit transition hover:-translate-y-[6px] hover:border-neutral-300 hover:shadow-floating">
          <NuxtLink :to="`/${p.slug}`" class="kb-product-image-frame aspect-square overflow-hidden bg-white text-inherit no-underline">
            <UiResponsiveImage :image="p.image" sizes="(min-width: 1024px) 300px, 70vw" class="size-full object-contain transition duration-500 group-hover:scale-105" />
          </NuxtLink>
          <div class="flex flex-1 flex-col gap-[9px] border-t border-border px-[14px] py-[16px] md:px-[20px] md:py-[22px]">
            <span class="text-eyebrow text-neutral-600 leading-[1.5] tracking-[0.14em] uppercase">{{ p.category?.name }}</span>
            <strong class="text-heading min-h-[2.8em] leading-[1.4] tracking-[-0.015em]">{{ p.name }}</strong>
            <span class="text-caption font-bold text-neutral-700 leading-[1.6]">{{ p.model }}<template v-if="p.finish"> · {{ p.finish.name }}</template></span>
            <a href="#consultation" class="text-caption mt-auto rounded-sm bg-charcoal-900 py-[14px] text-center font-bold tracking-[0.1em] text-white uppercase no-underline transition hover:bg-brass-700">Liên hệ tư vấn</a>
          </div>
        </article>
      </div>
    </div>
  </section>
</template>
