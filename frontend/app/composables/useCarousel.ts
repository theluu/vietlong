/**
 * The scroll behaviour behind the homepage's horizontal card tracks.
 *
 * The three tracks each had their own copy of `scrollBy`, and none of them
 * asked whether there was anything to scroll — so on a wide screen with a
 * short track the arrows were two buttons that visibly did nothing. The
 * enabled state lives here so a track that fits says so.
 */
export function useCarousel(fallbackCardWidth = 280) {
  const track = ref<HTMLElement | null>(null)
  const canPrev = ref(false)
  const canNext = ref(false)

  /** Re-read the track's scroll position. Safe to call as often as you like. */
  const sync = () => {
    const el = track.value
    if (!el) return
    const max = el.scrollWidth - el.clientWidth
    // Fractional card widths leave scrollLeft a hair short of max, so compare
    // with a pixel of slack rather than exactly.
    canPrev.value = el.scrollLeft > 1
    canNext.value = max > 1 && el.scrollLeft < max - 1
  }

  const scroll = (direction: number) => {
    const el = track.value
    if (!el) return
    const card = el.firstElementChild as HTMLElement | null
    const gap = 24
    el.scrollBy({ left: direction * ((card?.offsetWidth ?? fallbackCardWidth) + gap) * 2, behavior: 'smooth' })
  }

  /** Jump back to the first card — for when the track's contents are replaced. */
  const reset = () => {
    track.value?.scrollTo({ left: 0 })
    nextTick(sync)
  }

  onMounted(() => {
    const el = track.value
    if (!el) return
    sync()
    el.addEventListener('scroll', sync, { passive: true })
    // A viewport resize changes how many cards fit, which changes scrollWidth.
    const observer = new ResizeObserver(sync)
    observer.observe(el)
    onBeforeUnmount(() => {
      el.removeEventListener('scroll', sync)
      observer.disconnect()
    })
  })

  return { track, canPrev, canNext, scroll, sync, reset, buttonClass: CAROUSEL_BUTTON_CLASS }
}

/**
 * Classes shared by every prev/next button, including the disabled state.
 *
 * `pointer-events-none` is what keeps the hover treatment from firing on a
 * button that cannot do anything.
 */
const CAROUSEL_BUTTON_CLASS =
  'grid size-[52px] cursor-pointer place-items-center border border-neutral-300 text-charcoal-900 transition '
  + 'hover:border-charcoal-900 hover:bg-charcoal-900 hover:text-gold-200 '
  + 'disabled:pointer-events-none disabled:opacity-35'
