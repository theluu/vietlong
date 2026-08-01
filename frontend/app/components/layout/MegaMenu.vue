<script setup lang="ts">
const { megaMenuOpen } = useSiteChrome()

/*
 * Labels come from the prototype. They point at the bare listing for now:
 * the listing filters on taxonomy term IDs (see productFilterState), which are
 * not known at build time, and a `?q=` param would be silently ignored there.
 * Task 19 wires these to real category IDs from the homepage endpoint.
 */
const columns = [
  {
    title: 'Khóa cửa',
    links: [
      'Khóa thông minh',
      'Khóa vân tay',
      'Khóa khách sạn',
      'Khóa tay gạt đồng',
      'Khóa chống trộm',
    ],
  },
  {
    title: 'Phụ kiện',
    links: ['Bản lề', 'Chốt cửa Cremone', 'Phụ kiện cửa', 'Phụ kiện tủ bếp'],
  },
]
</script>

<template>
  <div
    v-if="megaMenuOpen"
    class="shadow-floating absolute top-full -left-10 z-[60] grid w-[720px] max-w-[84vw] grid-cols-[1fr_1fr_220px] gap-9 border-t-[3px] border-gold-200 bg-background px-[34px] py-8 text-text"
  >
    <div v-for="col in columns" :key="col.title" class="flex flex-col gap-[14px]">
      <span
        class="text-eyebrow text-brass-700 font-bold tracking-[0.18em] uppercase"
      >{{ col.title }}</span>
      <NuxtLink
        v-for="link in col.links"
        :key="link"
        to="/san-pham"
        class="text-body text-text no-underline hover:text-brass-700"
      >{{ link }}</NuxtLink>
    </div>

    <NuxtLink
      to="/san-pham"
      class="text-body text-brass-700 flex items-end font-bold no-underline"
    >
      Bộ sưu tập đồng →
    </NuxtLink>
  </div>
</template>
