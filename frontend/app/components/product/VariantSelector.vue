<script setup lang="ts">
import type { VariantOption } from '~/types/product'

defineProps<{
  options: VariantOption[]
  currentKey: string
  kind: 'finish' | 'size'
}>()
</script>

<template>
  <div class="flex flex-wrap gap-3">
    <template v-for="opt in options" :key="opt.key">
      <!--
        An unavailable variant has no sibling node to link to. Render it
        disabled rather than as a link to nowhere.
      -->
      <span
        v-if="!opt.available || !opt.slug"
        class="text-caption flex cursor-not-allowed flex-col gap-1 rounded-sm border border-border bg-surface px-4 py-3 text-text-muted opacity-50"
        aria-disabled="true"
      >
        <span class="flex items-center gap-2">
          <span
            v-if="kind === 'finish'"
            class="h-[13px] w-[13px] rounded-full kb-swatch-ring"
            :style="{ background: opt.swatch || '#ccc' }"
          />
          {{ opt.label }}
        </span>
        <span v-if="opt.note" class="text-[11px]">{{ opt.note }}</span>
      </span>

      <NuxtLink
        v-else
        :to="`/${opt.slug}`"
        :data-variant-key="opt.key"
        class="text-caption flex flex-col gap-1 rounded-sm border px-4 py-3 no-underline transition"
        :class="opt.key === currentKey
          ? 'border-charcoal-900 bg-charcoal-900 text-gold-200 font-bold'
          : 'border-border bg-background text-text hover:border-brass-500'"
        :aria-current="opt.key === currentKey ? 'true' : undefined"
      >
        <span class="flex items-center gap-2">
          <span
            v-if="kind === 'finish'"
            class="h-[13px] w-[13px] rounded-full kb-swatch-ring"
            :style="{ background: opt.swatch || '#ccc' }"
          />
          {{ opt.label }}
        </span>
        <span v-if="opt.note" class="text-[11px] opacity-70">{{ opt.note }}</span>
      </NuxtLink>
    </template>
  </div>
</template>
