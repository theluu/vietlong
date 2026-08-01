<script setup lang="ts">
import { fetchProducts } from '~/services/products'

const route = useRoute()
const id = computed(() => String(route.params.slug))

// The term's own name comes from the facet payload, which already carries
// labels — no extra endpoint needed.
const { data } = await useAsyncData(
  () => `category-term:${id.value}`,
  () => fetchProducts({ category: id.value }),
)

const name = computed(() => data.value?.facets?.category?.[id.value]?.label)

if (!name.value) {
  throw createError({ statusCode: 404, statusMessage: 'Không tìm thấy danh mục', fatal: true })
}

useSeoMeta({
  title: () => `${name.value} — Keybolts`,
  description: () => `Danh mục ${name.value} của Keybolts — nhập khẩu chính hãng, đạt chứng nhận CE-CFF, bảo hành 5–10 năm.`,
})

useHead(() => ({
  link: [{ rel: 'canonical', href: `https://keybolts.com.vn${route.path}` }],
}))
</script>

<template>
  <ProductListing
    :title="name!"
    eyebrow="Danh mục"
    :description="`Toàn bộ sản phẩm thuộc danh mục ${name}.`"
    :locked-filter="{ axis: 'category', value: id }"
    :breadcrumb="[
      { label: 'Trang chủ', url: '/' },
      { label: 'Sản phẩm', url: '/san-pham' },
      { label: name!, url: `/danh-muc/${id}` },
    ]"
  />
</template>
