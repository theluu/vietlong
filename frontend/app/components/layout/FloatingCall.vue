<template>
  <a
    :href="HOTLINE_TEL"
    :aria-label="`Gọi ${HOTLINE}`"
    class="kb-floating-call fixed bottom-[22px] left-[22px] z-[70] grid size-[62px] place-items-center rounded-full bg-[#d0021b] text-white no-underline shadow-[0_10px_26px_rgba(208,2,27,.45)]"
  >
    <svg class="kb-phone-shake" width="26" height="26" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
  </a>
</template>
