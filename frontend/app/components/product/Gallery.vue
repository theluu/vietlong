<script setup lang="ts">
const props = defineProps<{
  images: { url: string; alt: string }[]
  name: string
}>()

const active = ref(0)
// A variant switch is real navigation, so reset when the images change.
watch(() => props.images, () => { active.value = 0 })

const current = computed(() => props.images[active.value] ?? null)
</script>

<template>
  <div class="grid gap-4 sm:grid-cols-[84px_minmax(0,1fr)]">
    <div v-if="images.length > 1" class="order-2 flex gap-3 overflow-x-auto sm:order-1 sm:flex-col">
      <button
        v-for="(img, i) in images"
        :key="img.url"
        type="button"
        class="h-20 w-20 flex-none cursor-pointer border bg-white p-1"
        :class="i === active ? 'border-brass-500' : 'border-border hover:border-brass-500'"
        :aria-label="`Ảnh ${i + 1}`" :aria-pressed="i === active" @click="active = i"
      ><img :src="img.url" :alt="img.alt || name" class="h-full w-full object-contain"></button>
    </div>
    <div class="order-1 sm:order-2">
    <div class="relative aspect-square border border-border bg-white p-5">
      <img
        v-if="current"
        :src="current.url"
        :alt="current.alt || name"
        class="h-full w-full object-contain p-5"
      >
      <span
        class="text-caption absolute top-4 left-4 bg-charcoal-900 px-3 py-[5px] font-bold tracking-[0.14em] text-gold-200 uppercase"
      >Chứng nhận CE-CFF</span>
    </div>

    <p class="text-caption text-text-muted m-0 pt-3 text-right">⌕ &nbsp; Ảnh thật sản phẩm</p>
    </div>
  </div>
</template>
