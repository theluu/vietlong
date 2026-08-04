<script setup lang="ts">
// Fetched once here so the header, footer and every hotline link on the page
// share a single request rather than one each.
await useSiteFetch()
</script>

<template>
  <div>
    <NuxtRouteAnnouncer />
    <NuxtLayout>
      <NuxtPage />
    </NuxtLayout>
  </div>
</template>
