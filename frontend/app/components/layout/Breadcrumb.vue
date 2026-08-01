<script setup lang="ts">
const props = defineProps<{
  items: { label: string; url: string }[]
}>()

const site = 'https://keybolts.com.vn'

// BreadcrumbList so search engines render the trail in the result snippet.
useHead(() => ({
  script: [
    {
      type: 'application/ld+json',
      innerHTML: JSON.stringify({
        '@context': 'https://schema.org',
        '@type': 'BreadcrumbList',
        itemListElement: props.items.map((item, i) => ({
          '@type': 'ListItem',
          position: i + 1,
          name: item.label,
          item: `${site}${item.url}`,
        })),
      }),
    },
  ],
}))
</script>

<template>
  <nav aria-label="Breadcrumb" class="text-caption text-text-muted">
    <ol class="m-0 flex list-none flex-wrap items-center gap-2 p-0">
      <li v-for="(item, i) in items" :key="item.url" class="flex items-center gap-2">
        <NuxtLink
          v-if="i < items.length - 1"
          :to="item.url"
          class="text-text-muted no-underline hover:text-brass-700"
        >{{ item.label }}</NuxtLink>
        <span v-else aria-current="page" class="text-text">{{ item.label }}</span>
        <span v-if="i < items.length - 1" aria-hidden="true">/</span>
      </li>
    </ol>
  </nav>
</template>
