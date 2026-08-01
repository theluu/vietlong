<script setup lang="ts">
import { fetchHomepage } from '~/services/homepage'

const { megaMenuOpen } = useSiteChrome()

/*
 * Real category IDs, shared across every page via a stable useAsyncData key so
 * the header costs one request per render, not one per page.
 */
const { data } = await useAsyncData('mega-menu-categories', () => fetchHomepage())
const categories = computed(() => data.value?.data?.categories ?? [])

// The prototype splits the menu into locks and accessories; the vocabulary is
// ordered the same way, so the split follows the catalogue's own weights.
const locks = computed(() => categories.value.slice(0, 5))
const accessories = computed(() => categories.value.slice(5))
</script>

<template>
  <div
    v-if="megaMenuOpen"
    class="shadow-floating absolute top-full -left-10 z-[60] grid w-[720px] max-w-[84vw] grid-cols-[1fr_1fr_220px] gap-9 border-t-[3px] border-gold-200 bg-background px-[34px] py-8 text-text"
  >
    <div class="flex flex-col gap-[14px]">
      <span class="text-eyebrow text-brass-700 font-bold tracking-[0.18em] uppercase">
        Khóa cửa
      </span>
      <NuxtLink
        v-for="cat in locks"
        :key="cat.id"
        :to="`/danh-muc/${cat.id}`"
        class="text-body text-text no-underline hover:text-brass-700"
      >{{ cat.name }}</NuxtLink>
    </div>

    <div class="flex flex-col gap-[14px]">
      <span class="text-eyebrow text-brass-700 font-bold tracking-[0.18em] uppercase">
        Phụ kiện
      </span>
      <NuxtLink
        v-for="cat in accessories"
        :key="cat.id"
        :to="`/danh-muc/${cat.id}`"
        class="text-body text-text no-underline hover:text-brass-700"
      >{{ cat.name }}</NuxtLink>
    </div>

    <NuxtLink
      to="/san-pham"
      class="text-body text-brass-700 flex items-end font-bold no-underline"
    >
      Bộ sưu tập đồng →
    </NuxtLink>
  </div>
</template>
