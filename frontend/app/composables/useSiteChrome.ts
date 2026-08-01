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

/**
 * Primary navigation, in the prototype's order.
 *
 * Homepage.html and Product Detail.html still show an older five-item nav
 * (Giải pháp / Kiến thức). They are the two oldest exports; the other eight
 * prototypes — including the most recently edited — all carry the six items
 * below, so this is the current design.
 */
export const NAV_ITEMS = [
  { label: 'Sản phẩm', to: '/san-pham' },
  { label: 'Giới thiệu', to: '/gioi-thieu' },
  { label: 'Dự án', to: '/du-an' },
  { label: 'Tin tức', to: '/tin-tuc' },
  { label: 'Đại lý', to: '/dai-ly' },
  { label: 'Liên hệ', to: '/lien-he' },
] as const

export const HOTLINE = '1900 9018'
export const HOTLINE_TEL = 'tel:19009018'
