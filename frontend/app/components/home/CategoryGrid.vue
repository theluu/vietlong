<script setup lang="ts">
import type { HomeCategory } from '~/services/homepage'

defineProps<{ categories: HomeCategory[] }>()
</script>

<template>
  <section class="mx-auto max-w-[var(--container-max)] px-[clamp(20px,4vw,48px)] py-16">
    <span class="text-eyebrow text-brass-700 font-bold tracking-[0.24em] uppercase">Danh mục</span>
    <h2 class="text-display-lg mt-3 mb-4 font-bold tracking-[-0.03em]">Khám phá sản phẩm</h2>
    <p class="text-heading text-text-muted m-0 max-w-[720px] font-light leading-relaxed">
      Chọn theo loại cửa và nhu cầu sử dụng — mỗi dòng sản phẩm có nhiều kích thước và màu
      hoàn thiện.
    </p>

    <div class="kb-related-grid mt-10 gap-[clamp(16px,1.6vw,24px)]">
      <NuxtLink
        v-for="cat in categories"
        :key="cat.id"
        :to="{ path: '/san-pham', query: { category: String(cat.id) } }"
        class="group flex flex-col gap-3 border border-border bg-background p-6 text-inherit no-underline transition hover:-translate-y-1 hover:border-brass-500 hover:shadow-floating"
      >
        <span class="text-caption text-brass-700 font-bold tracking-[0.18em]">{{ cat.number }}</span>
        <span class="text-heading font-bold">{{ cat.name }}</span>
        <span v-if="cat.desc" class="text-caption text-text-muted leading-relaxed">
          {{ cat.desc }}
        </span>
        <span class="text-caption text-brass-700 mt-auto pt-3 font-bold">Khám phá →</span>
      </NuxtLink>
    </div>
  </section>
</template>
