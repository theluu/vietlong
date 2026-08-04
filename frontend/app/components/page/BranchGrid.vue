<script setup lang="ts">
import type { Branch } from '~/types/page'

/**
 * The prototypes draw this list two ways: hairline cells on the about page,
 * separated cards on the dealer and contact pages. Same data, so one component
 * with a variant rather than two near-identical files.
 */
withDefaults(defineProps<{
  eyebrow: string
  title: string
  branches: Branch[]
  variant?: 'cards' | 'hairline'
}>(), { variant: 'cards' })
</script>

<template>
  <section class="mx-auto max-w-[var(--container-max)] px-[clamp(20px,4vw,48px)] py-[clamp(48px,5vw,72px)]">
    <PageSectionHeading :eyebrow="eyebrow" :title="title" />

    <div
      class="kb-branch-grid"
      :class="variant === 'hairline'
        ? 'kb-branch-grid--hairline border-border bg-border gap-px border'
        : 'gap-[18px]'"
    >
      <div
        v-for="branch in branches"
        :key="branch.id"
        class="bg-background flex flex-col gap-[9px] p-[26px]"
        :class="variant === 'hairline'
          ? ''
          : 'border-border hover:border-brass-500 hover:shadow-floating border transition duration-200 ease-in-out'"
      >
        <span class="text-brass-700 text-[10px] font-bold tracking-[0.18em] uppercase">{{ branch.tag }}</span>
        <span class="text-heading font-bold">{{ branch.name }}</span>
        <span class="text-caption text-text-muted leading-[1.7]">{{ branch.address }}</span>
        <a
          :href="`tel:${branch.phoneTel}`"
          class="border-border text-body text-brass-700 mt-auto border-t pt-[12px] font-bold no-underline"
        >{{ branch.phoneDisplay }}</a>
      </div>
    </div>
  </section>
</template>
