<script setup lang="ts">
import type { ResponsiveImage } from '~/types/page'
// Explicit import (not just relying on Nuxt auto-import): this component is
// rendered directly by vitest via @vue/server-renderer, outside Nuxt's build
// pipeline, where auto-import doesn't run.
import { computed } from 'vue'

// Gốc của template là <picture>, nhưng class mà nơi gọi truyền vào là để tạo
// dáng cho chính tấm ảnh — `object-cover`, `size-full` đặt nhầm lên <picture>
// sẽ không có tác dụng gì. Nên tắt kế thừa tự động và tự gắn $attrs vào <img>.
defineOptions({ inheritAttrs: false })

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
  <picture v-if="image">
    <source :srcset="image.srcsetAvif" :sizes="sizes" type="image/avif">
    <img
      v-bind="$attrs"
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
  </picture>
</template>
