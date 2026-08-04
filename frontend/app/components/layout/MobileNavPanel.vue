<script setup lang="ts">
const { menu } = useSite()
const { isMobile } = useViewport()
const { mobileNavOpen, closeAll } = useSiteChrome()

// The prototype gates the panel on the mobile breakpoint as well as the flag,
// so resizing to desktop while it is open cannot leave it stranded.
const open = computed(() => isMobile.value && mobileNavOpen.value)
</script>

<template>
  <div
    v-if="open"
    class="flex flex-col gap-[18px] border-t border-white/12 px-[clamp(20px,4vw,48px)] pt-5 pb-6"
  >
    <NuxtLink
      v-for="item in menu"
      :key="item.to"
      :to="item.to"
      class="text-heading font-bold text-white no-underline"
      @click="closeAll"
    >{{ item.label }}</NuxtLink>

    <NuxtLink
      to="/lien-he"
      class="text-body mt-1 inline-flex justify-center rounded-sm bg-gold-200 p-[15px] font-bold tracking-[0.06em] text-charcoal-900 uppercase no-underline"
      @click="closeAll"
    >Nhận tư vấn</NuxtLink>
  </div>
</template>
