<script setup lang="ts">
defineProps<{ title: string; eyebrow?: string; description?: string; total: number; breadcrumb: { label: string; url: string }[] }>()
</script>

<template>
  <section class="relative overflow-hidden bg-charcoal-900 text-white">
    <div class="mx-auto grid min-h-[500px] max-w-[var(--container-max)] lg:grid-cols-[1.05fr_.95fr]">
      <div class="relative z-10 flex flex-col justify-end px-[clamp(20px,4vw,48px)] py-[clamp(54px,7vw,96px)]">
        <LayoutBreadcrumb :items="breadcrumb" class="absolute top-6 left-[clamp(20px,4vw,48px)] [&_a]:text-white/55 [&_span]:text-white/70" />
        <span class="text-eyebrow text-gold-200 mb-5 font-bold tracking-[.24em] uppercase">— {{ eyebrow }}</span>
        <h1 class="m-0 max-w-[650px] text-[clamp(40px,4.5vw,64px)] leading-[1.08] font-bold tracking-[-.035em]">{{ title }}</h1>
        <p class="text-heading mt-6 mb-7 max-w-[610px] leading-loose text-white/65">{{ description }}</p>
        <div class="grid max-w-[600px] grid-cols-3 border-t border-white/20">
          <div v-for="stat in [{v:total,l:'Mã sản phẩm'}, {v:'08',l:'Danh mục'}, {v:'5–10',l:'Năm bảo hành'}]" :key="stat.l" class="border-r border-white/15 py-5 pr-5 last:border-r-0 [&+&]:pl-8"><strong class="text-display text-gold-200 block">{{ stat.v }}</strong><span class="text-caption text-white/45 uppercase tracking-[.12em]">{{ stat.l }}</span></div>
        </div>
      </div>
      <div class="relative hidden lg:block">
        <img src="/images/kb-1700-xl-pvd.webp" alt="Khóa đồng Keybolts" width="1448" height="2048" loading="lazy" decoding="async" class="absolute top-[18%] left-0 h-[54%] w-[48%] border-[16px] border-white object-cover">
        <img src="/images/khoa-thong-minh-t28.webp" alt="Khóa thông minh Keybolts" width="1600" height="1348" loading="lazy" decoding="async" class="absolute top-[26%] right-0 h-[40%] w-[47%] border-[14px] border-white object-cover">
        <img src="/images/-r3-0183-copy.webp" alt="Phụ kiện cửa Keybolts" width="1600" height="1067" loading="lazy" decoding="async" class="absolute right-0 bottom-[-12%] h-[38%] w-[47%] border-[14px] border-white object-cover">
      </div>
    </div>
  </section>
</template>
