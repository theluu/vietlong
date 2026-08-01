<script setup lang="ts">
const DESCRIPTION =
  'Toàn bộ danh mục Keybolts — khóa đồng, khóa thông minh, khóa vân tay, khóa khách sạn, chốt Cremone và phụ kiện cửa gỗ. Tất cả đều đạt chứng nhận CE-CFF, bảo hành 5–10 năm.'

useSeoMeta({
  title: 'Khóa cửa & phụ kiện nhập khẩu — Keybolts',
  description: DESCRIPTION,
})

// Self-referencing canonical so filter query parameters never split ranking
// signals across dozens of near-identical URLs.
useHead({ link: [{ rel: 'canonical', href: 'https://keybolts.com.vn/san-pham' }] })
</script>

<template>
  <ProductListing
    title="Khóa cửa &amp; phụ kiện nhập khẩu"
    eyebrow="Bộ sưu tập"
    :description="DESCRIPTION"
    :breadcrumb="[
      { label: 'Trang chủ', url: '/' },
      { label: 'Sản phẩm', url: '/san-pham' },
    ]"
  />
</template>
