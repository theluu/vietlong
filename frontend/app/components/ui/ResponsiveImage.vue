<script setup lang="ts">
import type { ResponsiveImage } from '~/types/page'
// Explicit import (not just relying on Nuxt auto-import): this component is
// rendered directly by vitest via @vue/server-renderer, outside Nuxt's build
// pipeline, where auto-import doesn't run.
import { computed } from 'vue'

const props = withDefaults(defineProps<{
  image: ResponsiveImage | null | undefined
  /** Bề rộng ô ảnh thật sự chiếm, để trình duyệt chọn đúng file trong srcset. */
  sizes: string
  /** Ảnh trong màn hình đầu: tải ngay thay vì đợi cuộn tới. */
  priority?: boolean
  /** Đè alt khi ngữ cảnh mô tả ảnh chính xác hơn dữ liệu. */
  alt?: string
}>(), { priority: false })

const altText = computed(() => props.alt ?? props.image?.alt ?? '')
</script>

<template>
  <img
    v-if="image"
    :src="image.url"
    :srcset="image.srcset"
    :sizes="sizes"
    :width="image.width"
    :height="image.height"
    :alt="altText"
    :loading="priority ? 'eager' : 'lazy'"
    :fetchpriority="priority ? 'high' : undefined"
    decoding="async"
  >
</template>
