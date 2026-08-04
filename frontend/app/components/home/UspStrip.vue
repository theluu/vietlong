<script setup lang="ts">
const home = useHome()
const usps = computed(() => home.value.usps)
</script>

<template>
  <div class="border-b border-border bg-background">
    <div
      class="mx-auto grid max-w-[var(--container-max)] grid-cols-1 px-[clamp(20px,4vw,48px)] sm:grid-cols-2 lg:grid-cols-4"
    >
      <div
        v-for="(u, i) in usps"
        :key="u.title"
        class="flex items-center gap-4 border-b border-border py-[26px] sm:px-[26px] lg:border-b-0"
        :class="[
          i < usps.length - 1 ? 'lg:border-r lg:border-border' : '',
          i === 0 ? 'sm:pl-0' : '',
          i === usps.length - 1 ? 'sm:pr-0' : '',
        ]"
      >
        <svg v-if="i === 0" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" class="flex-none text-brass-500" aria-hidden="true"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/></svg>
        <svg v-else-if="i === 1" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" class="flex-none text-brass-500" aria-hidden="true"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
        <svg v-else-if="i === 2" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" class="flex-none text-brass-500" aria-hidden="true"><path d="M3 14h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H4a1 1 0 0 1-1-1v-6a9 9 0 1 1 18 0v6a1 1 0 0 1-1 1h-2a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3"/></svg>
        <svg v-else width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" class="flex-none text-brass-500" aria-hidden="true"><path d="M12 2 4 6v6c0 5 3.4 7.6 8 10 4.6-2.4 8-5 8-10V6Z"/><path d="m9 12 2 2 4-4"/></svg>
        <div class="flex flex-col gap-[3px]">
          <span class="text-body font-bold tracking-[0.01em]">{{ u.title }}</span>
          <span class="text-caption text-text-muted">{{ u.desc }}</span>
        </div>
      </div>
    </div>
  </div>
</template>
