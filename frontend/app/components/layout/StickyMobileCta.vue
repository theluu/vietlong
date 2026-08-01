<script setup lang="ts">
// The prototype gates this on the mobile breakpoint alone — the search overlay
// (z-90) and mega menu simply paint over it.
const { isMobile } = useViewport()
</script>

<template>
  <template v-if="isMobile">
    <!-- Spacer so the bar never covers the end of the page. -->
    <div class="h-[62px]" />

    <div
      class="fixed right-0 bottom-0 left-0 z-[60] flex border-t border-gold-200/28 bg-charcoal-900"
    >
      <a
        :href="HOTLINE_TEL"
        class="text-caption flex flex-1 flex-col items-center gap-1 py-[11px] text-white no-underline"
      >
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z" />
        </svg>
        Gọi
      </a>

      <a
        href="https://zalo.me/19009018"
        rel="noopener"
        class="text-caption flex flex-1 flex-col items-center gap-1 border-l border-white/14 py-[11px] text-white no-underline"
      >
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8z" />
        </svg>
        Zalo
      </a>

      <NuxtLink
        to="/lien-he"
        class="text-caption flex flex-1 flex-col items-center gap-1 border-l border-white/14 bg-gold-200 py-[11px] font-bold text-charcoal-900 no-underline"
      >
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M4 4h16v12H5.17L4 17.17z" />
        </svg>
        Nhận tư vấn
      </NuxtLink>
    </div>
  </template>
</template>
