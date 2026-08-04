<script setup lang="ts">
const { hotline: HOTLINE, hotlineTel: HOTLINE_TEL, topbar } = useSite()
// The CE-CFF and warranty items drop out below 1200px; the hotline never does.
const { utilWide } = useViewport()
</script>

<template>
  <div class="bg-ink-900 text-text-on-dark-muted">
    <div
      class="mx-auto flex max-w-[var(--container-max)] items-center justify-between gap-8 px-[clamp(20px,4vw,48px)] py-[9px] text-caption tracking-[0.04em]"
    >
      <span class="truncate">{{ topbar.text }}</span>

      <div class="flex flex-shrink-0 items-center gap-[22px]">
        <span v-if="utilWide" class="flex items-center gap-[22px]">
          <template v-for="badge in topbar.badges" :key="badge">
            <span class="whitespace-nowrap">{{ badge }}</span>
            <span class="h-3 w-px bg-white/22" />
          </template>
        </span>

        <a
          :href="HOTLINE_TEL"
          class="text-gold-200 font-bold tracking-[0.08em] no-underline"
        >{{ HOTLINE }}</a>
      </div>
    </div>
  </div>
</template>
