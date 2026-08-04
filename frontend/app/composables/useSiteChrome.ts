/** Shared open/close state for the header's three overlapping surfaces. */
export const useSiteChrome = () => {
  const mobileNavOpen = useState('chrome:mobileNav', () => false)
  const megaMenuOpen = useState('chrome:megaMenu', () => false)
  const searchOpen = useState('chrome:search', () => false)

  // Only one surface may be open at a time.
  const closeAll = () => {
    mobileNavOpen.value = false
    megaMenuOpen.value = false
    searchOpen.value = false
  }

  const openSearch = () => {
    closeAll()
    searchOpen.value = true
  }

  const toggleMobileNav = () => {
    const next = !mobileNavOpen.value
    closeAll()
    mobileNavOpen.value = next
  }

  const anyOpen = computed(
    () => mobileNavOpen.value || megaMenuOpen.value || searchOpen.value,
  )

  return {
    mobileNavOpen,
    megaMenuOpen,
    searchOpen,
    anyOpen,
    closeAll,
    openSearch,
    toggleMobileNav,
  }
}
