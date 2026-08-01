/**
 * One source of truth for the breakpoint behaviour the prototype encodes,
 * rather than media queries scattered across components.
 *
 * `width` starts at a desktop value so SSR renders the desktop layout; the
 * real width lands on mount.
 */
export function useViewport() {
  const width = ref(1440)

  const update = () => {
    width.value = window.innerWidth
  }

  onMounted(() => {
    update()
    window.addEventListener('resize', update, { passive: true })
  })

  onUnmounted(() => {
    if (import.meta.client) {
      window.removeEventListener('resize', update)
    }
  })

  return {
    width,
    // Thresholds mirror the prototype's own state flags.
    isMobile: computed(() => width.value < 992),
    isWide: computed(() => width.value >= 1300),
    utilWide: computed(() => width.value >= 1200),
  }
}
