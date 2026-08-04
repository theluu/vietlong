<script setup lang="ts">
/**
 * One badge per social channel. Brand colour and glyph are keyed off the icon
 * an editor picks, so adding a channel in /admin needs no code change — only
 * an unknown key falls back to a neutral badge showing the label.
 */
defineProps<{ icon: string; label: string; url: string }>()

const BRAND: Record<string, string> = {
  facebook: 'bg-[#1877f2]',
  youtube: 'bg-[#ff0000]',
  zalo: 'bg-[#0068ff]',
  tiktok: 'bg-[#010101]',
  instagram: 'bg-[#e1306c]',
  linkedin: 'bg-[#0a66c2]',
}
</script>

<template>
  <a
    :href="url"
    target="_blank"
    rel="noopener"
    :aria-label="label"
    class="grid size-[44px] place-items-center rounded-full text-white no-underline transition hover:-translate-y-1"
    :class="BRAND[icon] ?? 'bg-white/16'"
  >
    <svg v-if="icon === 'facebook'" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M22 12a10 10 0 1 0-11.6 9.9v-7H7.9V12h2.5V9.8c0-2.5 1.5-3.9 3.8-3.9 1.1 0 2.2.2 2.2.2v2.5h-1.3c-1.2 0-1.6.8-1.6 1.6V12h2.8l-.4 2.9h-2.4v7A10 10 0 0 0 22 12Z"/></svg>
    <svg v-else-if="icon === 'youtube'" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M23 12s0-3.6-.5-5.3c-.3-1-1-1.7-2-2C18.8 4.2 12 4.2 12 4.2s-6.8 0-8.5.5c-1 .3-1.7 1-2 2C1 8.4 1 12 1 12s0 3.6.5 5.3c.3 1 1 1.7 2 2 1.7.5 8.5.5 8.5.5s6.8 0 8.5-.5c1-.3 1.7-1 2-2 .5-1.7.5-5.3.5-5.3ZM9.8 15.5v-7l6 3.5-6 3.5Z"/></svg>
    <svg v-else-if="icon === 'tiktok'" width="19" height="19" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M16 2h-3v14a3 3 0 1 1-3-3v-3a6 6 0 1 0 6 6V9.6A7 7 0 0 0 21 11V8a4 4 0 0 1-5-4Z"/></svg>
    <svg v-else-if="icon === 'instagram'" width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1"/></svg>
    <svg v-else-if="icon === 'linkedin'" width="19" height="19" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M4.98 3.5a2.5 2.5 0 1 1 0 5 2.5 2.5 0 0 1 0-5ZM3 9h4v12H3V9Zm7 0h3.8v1.7h.05A4.2 4.2 0 0 1 17.6 8.7c4 0 4.7 2.6 4.7 6V21h-4v-5.5c0-1.3 0-3-1.9-3s-2.2 1.4-2.2 2.9V21h-4V9Z"/></svg>
    <span v-else class="text-[11px] font-bold">{{ label.slice(0, 4) }}</span>
  </a>
</template>
