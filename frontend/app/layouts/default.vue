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
  <!-- `clip` not `hidden`: overflow-x:hidden makes this a scroll container in
       both axes, which silently kills the header's position:sticky. `clip`
       stops the sideways scroll without creating one. -->
  <div class="min-h-screen overflow-x-clip bg-background text-text">
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
