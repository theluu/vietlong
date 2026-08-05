<script setup lang="ts">
import { fetchHomepage } from '~/services/homepage'
const { hotline: HOTLINE } = useSite()

const { data } = await useAsyncData('homepage', () => fetchHomepage())

const payload = computed(() => data.value?.data)
const categories = computed(() => payload.value?.categories ?? [])
const featured = computed(() => payload.value?.featured ?? {})

// Reuse the first featured product's photo as the hero image so the LCP
// element is a real catalogue shot rather than a placeholder.
const heroImage = computed(() => {
  // The whole image object, not its url: UiResponsiveImage needs the srcsets
  // too, and a bare string reads as truthy while rendering nothing.
  //
  // This used to pin the 6y7a5717 photograph by filename. The CSV import
  // replaced the catalogue, that file is gone, and a hero that depends on one
  // filename surviving an import is a hero that breaks on the next one.
  return Object.values(featured.value).flat().find(p => p.image)?.image ?? null
})

useSeoMeta({
  title: 'Keybolts — Khóa cửa & phụ kiện nhập khẩu chính hãng',
  // A getter, and `.value`: this runs in <script setup> where a ref is not
  // unwrapped the way it would be inside the template, and the hotline arrives
  // from the API after this line first evaluates.
  description: () =>
    `Khóa đồng, khóa vân tay, khóa thông minh, khóa thẻ từ khách sạn và phụ kiện cửa nhập khẩu. Đạt chứng nhận CE-CFF, bảo hành 5–10 năm. Hotline ${HOTLINE.value}.`,
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
