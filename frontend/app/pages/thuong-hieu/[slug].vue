<script setup lang="ts">
import { fetchProducts } from '~/services/products'

const route = useRoute()
const id = computed(() => String(route.params.slug))

const { data } = await useAsyncData(
  () => `brand-term:${id.value}`,
  () => fetchProducts({ brand: id.value }),
)

const name = computed(() => data.value?.facets?.brand?.[id.value]?.label)

if (!name.value) {
  throw createError({ statusCode: 404, statusMessage: 'Không tìm thấy thương hiệu', fatal: true })
}

useSeoMeta({
  title: () => `${name.value} — Keybolts`,
  description: () => `Sản phẩm thương hiệu ${name.value} phân phối bởi Keybolts — nhập khẩu chính hãng, bảo hành 5–10 năm.`,
})

useHead(() => ({
  link: [{ rel: 'canonical', href: `https://keybolts.com.vn${route.path}` }],
}))
</script>

<template>
  <ProductListing
    :title="name!"
    eyebrow="Thương hiệu"
    :description="`Toàn bộ sản phẩm thương hiệu ${name}.`"
    :locked-filter="{ axis: 'brand', value: id }"
    :breadcrumb="[
      { label: 'Trang chủ', url: '/' },
      { label: 'Sản phẩm', url: '/san-pham' },
      { label: name!, url: `/thuong-hieu/${id}` },
    ]"
  />
</template>
