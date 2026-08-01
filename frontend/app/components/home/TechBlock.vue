<script setup lang="ts">
import { TECH_FEATURES } from '~/utils/homeContent'
</script>

<template>
  <section class="bg-charcoal-900 text-white">
    <div
      class="mx-auto grid max-w-[var(--container-max)] gap-10 px-[clamp(20px,4vw,48px)] py-16 lg:grid-cols-2"
    >
      <div class="flex flex-col gap-5">
        <span class="text-eyebrow text-gold-200 font-bold tracking-[0.24em] uppercase">
          Công nghệ
        </span>
        <h2 class="text-display-lg m-0 font-bold tracking-[-0.03em]">
          Khóa vân tay thế hệ mới
        </h2>
        <p class="text-heading m-0 leading-relaxed font-light text-white/75">
          Tích hợp nhận diện vân tay, mã số và thẻ từ — mở cửa trong một giây, an toàn hơn
          khóa cơ truyền thống.
        </p>
        <NuxtLink
          to="/san-pham"
          class="text-body mt-2 inline-flex w-fit rounded-sm bg-gold-200 px-8 py-4 font-bold tracking-[0.06em] text-charcoal-900 uppercase no-underline hover:bg-white"
        >Xem dòng khóa thông minh</NuxtLink>
      </div>

      <ul class="m-0 flex list-none flex-col gap-4 p-0">
        <li
          v-for="feat in TECH_FEATURES"
          :key="feat"
          class="text-body flex gap-3 border-b border-white/12 pb-4 text-white/75"
        >
          <span class="text-gold-200 font-bold">✓</span>{{ feat }}
        </li>
      </ul>
    </div>
  </section>
</template>
