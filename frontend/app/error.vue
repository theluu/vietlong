<script setup lang="ts">
import type { NuxtError } from '#app'

const props = defineProps<{ error: NuxtError }>()

const isNotFound = computed(() => props.error?.statusCode === 404)

const title = computed(() => (isNotFound.value ? 'Không tìm thấy trang' : 'Đã xảy ra lỗi'))
const message = computed(() =>
  isNotFound.value
    ? 'Trang bạn tìm không tồn tại hoặc đã được chuyển đi. Thử xem danh mục sản phẩm hoặc gọi hotline để được tư vấn.'
    : 'Hệ thống đang gặp sự cố. Vui lòng thử lại sau ít phút hoặc gọi hotline để được hỗ trợ ngay.',
)

useHead({ title: () => `${title.value} | Keybolts` })

// A soft 404 that returns 200 gets the page indexed. clearError restores the
// real status by navigating rather than swallowing it.
const goHome = () => clearError({ redirect: '/' })
</script>

<template>
  <div class="flex min-h-screen flex-col bg-charcoal-900 text-white">
    <div class="mx-auto flex w-full max-w-[var(--container-max)] flex-1 flex-col justify-center gap-6 px-[clamp(20px,4vw,48px)] py-20">
      <span class="text-eyebrow text-gold-200 font-bold tracking-[0.24em] uppercase">
        Lỗi {{ error?.statusCode ?? 500 }}
      </span>

      <h1 class="text-[clamp(40px,5.4vw,72px)] m-0 leading-[1.02] font-bold tracking-[-0.035em]">
        <span class="kb-hero-gradient">{{ title }}</span>
      </h1>

      <p class="text-heading m-0 max-w-[520px] leading-relaxed font-light text-white/75">
        {{ message }}
      </p>

      <div class="mt-2 flex flex-wrap gap-4">
        <button
          type="button"
          class="text-body cursor-pointer rounded-sm bg-gold-200 px-8 py-4 font-bold tracking-[0.06em] text-charcoal-900 uppercase"
          @click="goHome"
        >Về trang chủ</button>
        <NuxtLink
          to="/san-pham"
          class="text-body rounded-sm border border-white/30 px-8 py-4 tracking-[0.06em] text-white uppercase no-underline hover:border-gold-200 hover:text-gold-200"
        >Xem sản phẩm</NuxtLink>
        <a
          :href="HOTLINE_TEL"
          class="text-body rounded-sm border border-white/30 px-8 py-4 tracking-[0.06em] text-white uppercase no-underline hover:border-gold-200 hover:text-gold-200"
        >Gọi {{ HOTLINE }}</a>
      </div>
    </div>
  </div>
</template>
