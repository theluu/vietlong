<script setup lang="ts">
import { fetchHomepage } from '~/services/homepage'

const { data } = await useAsyncData('homepage', () => fetchHomepage())

const payload = computed(() => data.value?.data)
const categories = computed(() => payload.value?.categories ?? [])
const featured = computed(() => payload.value?.featured ?? {})

// Reuse the first featured product's photo as the hero image so the LCP
// element is a real catalogue shot rather than a placeholder.
const heroImage = computed(
  () => Object.values(featured.value).flat().find(p => p.image)?.image?.url ?? null,
)

useSeoMeta({
  title: 'Keybolts — Khóa cửa & phụ kiện nhập khẩu chính hãng',
  description:
    'Khóa đồng, khóa vân tay, khóa thông minh, khóa thẻ từ khách sạn và phụ kiện cửa nhập khẩu. Đạt chứng nhận CE-CFF, bảo hành 5–10 năm. Hotline 1900 9018.',
})
</script>

<template>
  <div>
    <HomeHero :image="heroImage" />
    <HomeUspStrip />
    <HomeCategoryGrid :categories="categories" />
    <HomeFeaturedTabs :featured="featured" />
    <HomeSolutionGrid />
    <HomeTechBlock />
    <HomeContentPanels />
    <HomeConsultForm />
    <HomeBranchList />
  </div>
</template>
