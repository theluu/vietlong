<script setup lang="ts">
import { fetchHomepage } from '~/services/homepage'

const { megaMenuOpen } = useSiteChrome()

/*
 * Real category IDs, shared across every page via a stable useAsyncData key so
 * the header costs one request per render, not one per page.
 */
const { data } = await useAsyncData('mega-menu-categories', () => fetchHomepage())

/*
 * One column per top category, its second level beneath. The vocabulary used
 * to be flat, so the menu split it by position — the first five were locks,
 * the rest accessories. It is a real tree now, and the tree names its own
 * groups. The third level belongs to the listing's own sidebar; four columns
 * already fill the panel.
 *
 * A top category leads to its landing page, which asks which kind you want
 * before showing anything — a lock for a wooden door and one for an
 * aluminium-and-glass door are different products, and a merged grid makes
 * the visitor sort that out by eye. Everything below leads straight to the
 * listing, where the tree stays on screen to narrow further.
 *
 * Two by two, not one row of four. The panel hangs off the "Sản phẩm" link
 * in the middle of the header, so the space to its right is whatever the
 * remaining nav items leave — about 720px at any viewport. Four columns
 * needed 880 and ran off the right edge of the screen.
 */
const columns = computed(() => data.value?.data?.categories ?? [])
</script>

<template>
  <div
    v-if="megaMenuOpen"
    class="shadow-floating absolute top-full -left-10 z-[60] grid w-[720px] max-w-[84vw] grid-cols-2 gap-x-9 gap-y-7 border-t-[3px] border-gold-200 bg-background px-[34px] py-8 text-text"
  >
    <div v-for="col in columns" :key="col.id" class="flex min-w-0 flex-col gap-[14px]">
      <NuxtLink
        :to="`/danh-muc/${col.id}`"
        class="text-eyebrow text-brass-700 font-bold tracking-[0.18em] uppercase no-underline"
      >{{ col.name }}</NuxtLink>
      <NuxtLink
        v-for="child in col.children"
        :key="child.id"
        :to="`/san-pham?category=${child.id}`"
        class="text-body text-text no-underline hover:text-brass-700"
      >{{ child.name }}</NuxtLink>
    </div>

    <NuxtLink
      to="/san-pham"
      class="text-body text-brass-700 col-span-2 border-t border-border pt-5 font-bold no-underline"
    >
      Bộ sưu tập đồng →
    </NuxtLink>
  </div>
</template>
