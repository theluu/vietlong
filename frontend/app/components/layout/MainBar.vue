<script setup lang="ts">
const { isMobile, isWide } = useViewport()
const { megaMenuOpen, openSearch, toggleMobileNav } = useSiteChrome()

// The prototype shows the logo tagline only on a wide desktop viewport.
const showLogoTagline = computed(() => !isMobile.value && isWide.value)
const [firstNav, ...restNav] = NAV_ITEMS
</script>

<template>
  <div
    class="shadow-floating sticky top-0 z-50 border-b border-gold-200/22 bg-charcoal-900 text-white"
  >
    <div
      class="mx-auto flex max-w-[var(--container-max)] items-center justify-between gap-[clamp(16px,2.4vw,40px)] px-[clamp(20px,4vw,48px)] py-4"
    >
      <NuxtLink to="/" class="flex min-w-0 items-center gap-4 no-underline">
        <span class="text-display text-gold-200 font-bold tracking-[-0.02em]">Keybolts</span>
        <span
          v-if="showLogoTagline"
          class="flex flex-col gap-[3px] border-l border-white/20 pl-4"
        >
          <span
            class="text-caption text-gold-200 font-bold tracking-[0.2em] whitespace-nowrap uppercase"
          >Premium Hardware</span>
          <span class="text-caption whitespace-nowrap text-white/55 tracking-[0.05em]">
            Khóa cửa &amp; phụ kiện nhập khẩu
          </span>
        </span>
      </NuxtLink>

      <!-- Desktop nav -->
      <div
        v-if="!isMobile"
        class="flex flex-shrink-0 items-center gap-[clamp(10px,1.6vw,26px)]"
      >
        <div
          class="relative"
          @mouseenter="megaMenuOpen = true"
          @mouseleave="megaMenuOpen = false"
        >
          <NuxtLink
            :to="firstNav.to"
            class="text-heading flex items-center gap-[7px] border-b-2 border-transparent px-1 py-3 font-bold whitespace-nowrap text-white no-underline hover:border-gold-200 hover:text-gold-200"
          >
            {{ firstNav.label }}
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">
              <path d="m6 9 6 6 6-6" />
            </svg>
          </NuxtLink>
          <LayoutMegaMenu />
        </div>

        <NuxtLink
          v-for="item in restNav"
          :key="item.to"
          :to="item.to"
          class="text-heading border-b-2 border-transparent px-1 py-3 font-bold whitespace-nowrap text-white no-underline hover:border-gold-200 hover:text-gold-200"
        >{{ item.label }}</NuxtLink>

        <button
          type="button"
          aria-label="Tìm kiếm"
          class="flex cursor-pointer border-none bg-transparent p-0 text-white"
          @click="openSearch"
        >
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8" /><path d="m21 21-4.3-4.3" />
          </svg>
        </button>

        <NuxtLink
          to="/lien-he"
          class="text-body rounded-sm bg-gold-200 px-[26px] py-[13px] font-bold tracking-[0.06em] whitespace-nowrap text-charcoal-900 uppercase no-underline hover:bg-white"
        >Nhận tư vấn</NuxtLink>
      </div>

      <!-- Mobile actions -->
      <div v-else class="flex flex-shrink-0 items-center gap-5">
        <a :href="HOTLINE_TEL" aria-label="Gọi hotline" class="flex text-white">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z" />
          </svg>
        </a>
        <button
          type="button"
          aria-label="Tìm kiếm"
          class="flex cursor-pointer border-none bg-transparent p-0 text-white"
          @click="openSearch"
        >
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8" /><path d="m21 21-4.3-4.3" />
          </svg>
        </button>
        <button
          type="button"
          aria-label="Mở menu"
          class="flex cursor-pointer border-none bg-transparent p-0 text-white"
          @click="toggleMobileNav"
        >
          <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M4 12h16" /><path d="M4 6h16" /><path d="M4 18h16" />
          </svg>
        </button>
      </div>
    </div>

    <LayoutMobileNavPanel />
  </div>
</template>
