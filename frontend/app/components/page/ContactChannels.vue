<script setup lang="ts">
import type { ContactChannel } from '~/types/page'

defineProps<{ channels: ContactChannel[] }>()

/** The payload carries no icon, so pick one from the channel's own label. */
const iconFor = (label: string): 'phone' | 'chat' | 'mail' => {
  const key = label.toLowerCase()
  if (key.includes('zalo') || key.includes('chat')) return 'chat'
  if (key.includes('mail')) return 'mail'
  return 'phone'
}

/** Email addresses are long enough to overflow a 240px card. */
const isLong = (value: string) => value.length > 20
</script>

<template>
  <div class="kb-channel-grid border-border bg-border gap-px border">
    <a
      v-for="channel in channels"
      :key="channel.label"
      :href="channel.ctaUrl || '#'"
      rel="noopener"
      class="bg-background hover:bg-surface flex flex-col gap-[10px] p-[32px] text-inherit no-underline transition-colors duration-200 ease-in-out"
    >
      <span class="border-border text-brass-700 flex h-[44px] w-[44px] items-center justify-center rounded-full border">
        <svg v-if="iconFor(channel.label) === 'phone'" width="19" height="19" viewBox="0 0 24 24" fill="currentColor"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
        <svg v-else-if="iconFor(channel.label) === 'chat'" width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"/></svg>
        <svg v-else width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="4" width="20" height="16"/><path d="m2 7 10 6 10-6"/></svg>
      </span>
      <span class="text-brass-700 text-[10px] font-bold tracking-[0.18em] uppercase">{{ channel.label }}</span>
      <span class="font-bold" :class="isLong(channel.value) ? 'text-heading break-all' : 'text-display'">{{ channel.value }}</span>
      <span class="text-caption text-text-muted">{{ channel.note }}</span>
    </a>
  </div>
</template>
