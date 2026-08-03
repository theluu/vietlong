<script setup lang="ts">
const { anyOpen, closeAll } = useSiteChrome()
const route = useRoute()

// Lock body scroll while any header surface is open.
watch(anyOpen, (open) => {
  if (import.meta.client) {
    document.body.style.overflow = open ? 'hidden' : ''
  }
})

// A surface left open across a route change would trap the user.
watch(() => route.fullPath, () => closeAll())

onUnmounted(() => {
  if (import.meta.client) document.body.style.overflow = ''
})
</script>

<template>
  <div class="min-h-screen overflow-x-hidden bg-background text-text">
    <LayoutTopBar />
    <LayoutMainBar />

    <main>
      <slot />
    </main>

    <LayoutSiteFooter />
    <LayoutFloatingCall />
    <LayoutStickyMobileCta />
    <LayoutSearchOverlay />
  </div>
</template>
