<script setup lang="ts">
import type { Branch } from '~/types/page'
defineProps<{ eyebrow: string; title: string; branches: Branch[] }>()
</script>

<template>
  <section class="mx-auto max-w-[var(--container-max)] px-[clamp(20px,4vw,48px)] py-[clamp(48px,5vw,72px)]">
    <PageSectionHeading :eyebrow="eyebrow" :title="title" />

    <div class="kb-branch-grid gap-[18px]">
      <div
        v-for="branch in branches"
        :key="branch.id"
        class="border-border bg-background hover:border-brass-500 hover:shadow-floating flex flex-col gap-[9px] border p-[26px] transition duration-200 ease-in-out"
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
