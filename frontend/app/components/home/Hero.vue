<script setup lang="ts">
import type { ResponsiveImage } from '~/types/page'

const props = defineProps<{ image?: ResponsiveImage | null }>()

const home = useHome()
const hero = computed(() => home.value.hero)
const titleLines = computed(() => heroTitleParts(hero.value.title))

// An editor's banner images when there are any; otherwise the borrowed
// product photo the hero showed before this field existed, so an empty
// field never leaves the panel blank.
const slides = computed<ResponsiveImage[]>(() =>
  hero.value.images?.length ? hero.value.images : (props.image ? [props.image] : []))

const track = ref<HTMLElement | null>(null)
const index = ref(0)

const goTo = (n: number, smooth = true) => {
  const el = track.value
  if (!el || !slides.value.length) return
  const i = (n + slides.value.length) % slides.value.length
  el.scrollTo({ left: i * el.clientWidth, behavior: smooth ? 'smooth' : 'auto' })
  index.value = i
}

// Native horizontal swipe comes free from scroll-snap, so read the position
// back off the scroller rather than tracking touches by hand.
let frame = 0
const onScroll = () => {
  cancelAnimationFrame(frame)
  frame = requestAnimationFrame(() => {
    const el = track.value
    if (el?.clientWidth) index.value = Math.round(el.scrollLeft / el.clientWidth)
  })
}

// Autoplay, paused while a visitor is reading or interacting with it.
const paused = ref(false)
let timer: ReturnType<typeof setInterval> | undefined
const stop = () => clearInterval(timer)
const start = () => {
  stop()
  if (slides.value.length < 2) return
  if (import.meta.client && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return
  timer = setInterval(() => {
    if (!paused.value && !document.hidden) goTo(index.value + 1)
  }, 5000)
}

onMounted(() => {
  start()
  track.value?.addEventListener('scroll', onScroll, { passive: true })
})
onBeforeUnmount(() => {
  stop()
  cancelAnimationFrame(frame)
  track.value?.removeEventListener('scroll', onScroll)
})
watch(() => slides.value.length, () => { index.value = 0; start() })
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
          <span class="text-eyebrow text-gold-200 font-bold tracking-[0.24em] uppercase">{{ hero.eyebrow }}</span>
        </div>

        <h1
          class="m-0 text-[clamp(40px,5.4vw,72px)] leading-[1.02] font-bold tracking-[-0.035em] text-balance"
        >
          <template v-for="(line, li) in titleLines" :key="li">
            <template v-for="(part, pi) in line" :key="pi">
              <span v-if="part.gradient" class="kb-hero-gradient">{{ part.text }}</span>
              <template v-else>{{ part.text }}</template>
            </template>
            <br v-if="li < titleLines.length - 1">
          </template>
        </h1>

        <p class="text-heading m-0 max-w-[430px] leading-[1.75] font-light text-white/75">
{{ hero.subtitle }}</p>

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

      <div
        class="relative min-h-[clamp(360px,46vw,620px)]"
        role="group"
        aria-roledescription="Băng chuyền ảnh"
        aria-label="Ảnh banner"
        @pointerenter="paused = true"
        @pointerleave="paused = false"
        @focusin="paused = true"
        @focusout="paused = false"
      >
        <div
          ref="track"
          class="kb-track kb-hero-frame absolute inset-0 flex snap-x snap-mandatory overflow-x-auto overflow-y-hidden bg-neutral-700"
        >
          <div v-for="(slide, i) in slides" :key="i" class="relative w-full flex-none snap-start">
            <UiResponsiveImage
              :image="slide"
              :alt="slide.alt || 'Khóa cửa cao cấp Keybolts'"
              sizes="(min-width: 768px) 50vw, 100vw"
              :priority="i === 0"
              class="h-full w-full object-cover"
            />
          </div>
        </div>
        <div
          class="pointer-events-none absolute inset-0"
          style="background: linear-gradient(90deg, var(--color-charcoal-900) 0%, rgba(40,45,48,0.35) 26%, transparent 55%)"
        />

        <template v-if="slides.length > 1">
          <button
            v-for="dir in [-1, 1]"
            :key="dir"
            type="button"
            :aria-label="dir < 0 ? 'Ảnh trước' : 'Ảnh sau'"
            class="absolute top-1/2 grid size-[44px] -translate-y-1/2 cursor-pointer place-items-center border border-white/25 bg-charcoal-900/45 text-white backdrop-blur-sm transition hover:border-gold-200 hover:bg-charcoal-900/80 hover:text-gold-200"
            :class="dir < 0 ? 'left-4' : 'right-4'"
            @click="goTo(index + dir)"
          >
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
              <path :d="dir < 0 ? 'm15 18-6-6 6-6' : 'm9 18 6-6-6-6'" />
            </svg>
          </button>

          <div class="absolute bottom-[26px] left-[26px] flex gap-[9px]">
            <button
              v-for="(slide, i) in slides"
              :key="i"
              type="button"
              :aria-label="`Ảnh ${i + 1}`"
              :aria-current="i === index ? 'true' : undefined"
              class="h-[7px] cursor-pointer rounded-full border-none transition-all duration-300"
              :class="i === index ? 'w-[26px] bg-gold-200' : 'w-[7px] bg-white/45 hover:bg-white/75'"
              @click="goTo(i)"
            />
          </div>
        </template>
        <div
          class="absolute right-0 bottom-0 flex border-t border-l border-gold-200/30 bg-charcoal-900/86 backdrop-blur-sm"
        >
          <div
            v-for="(s, i) in hero.stats"
            :key="s.label"
            class="flex flex-col gap-1 px-7 py-[22px]"
            :class="i < hero.stats.length - 1 ? 'border-r border-white/14' : ''"
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
