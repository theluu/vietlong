<script setup lang="ts">
import type { ArticleCompareRow } from '~/types/page'

defineProps<{ id: string; title: string; rows: ArticleCompareRow[] }>()

const columns = ['Loại cửa', 'Độ dày', 'Kiểu khóa nên dùng', 'Dự phòng'] as const
</script>

<template>
  <section :id="id" class="flex scroll-mt-[140px] flex-col gap-[16px]">
    <h2 class="text-display m-0 leading-[1.3] font-bold tracking-[-0.01em]">{{ title }}</h2>
    <div class="kb-track border-border overflow-x-auto border">
      <table class="w-full min-w-[560px] table-fixed border-collapse text-left">
        <colgroup>
          <col class="w-[27.5%]"><col class="w-[25%]"><col class="w-[25%]"><col class="w-[22.5%]">
        </colgroup>
        <thead>
          <tr class="bg-charcoal-900 text-white">
            <th
              v-for="column in columns"
              :key="column"
              class="text-caption px-[18px] py-[14px] font-bold tracking-[0.1em] uppercase"
            >{{ column }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(row, i) in rows" :key="row.door" class="border-border border-t" :class="i % 2 ? 'bg-surface' : 'bg-background'">
            <td class="text-body px-[18px] py-[16px] font-bold">{{ row.door }}</td>
            <td class="text-body text-text-muted px-[18px] py-[16px]">{{ row.thickness }}</td>
            <td class="text-body text-text-muted px-[18px] py-[16px]">{{ row.lock }}</td>
            <td class="text-body text-text-muted px-[18px] py-[16px]">{{ row.backup }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </section>
</template>
