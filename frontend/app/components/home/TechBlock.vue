<script setup lang="ts">
const home = useHome()
const tech = computed(() => home.value.tech)
</script>

<template>
  <section id="technology" class="kb-tech-radial relative overflow-hidden bg-charcoal-900 py-[clamp(64px,7vw,112px)] text-white">
    <div class="relative mx-auto grid max-w-[var(--container-max)] grid-cols-[repeat(auto-fit,minmax(340px,1fr))] items-center gap-[clamp(44px,5vw,88px)] px-[clamp(20px,4vw,48px)]">
      <div class="relative before:absolute before:-top-[24px] before:-left-[24px] before:size-[150px] before:border-t before:border-l before:border-gold-200/40 after:absolute after:-right-[24px] after:-bottom-[24px] after:size-[150px] after:border-r after:border-b after:border-gold-200/40">
        <div class="kb-tech-image relative aspect-[4/3.4] overflow-hidden bg-white"><img src="/images/khoa-thong-minh-t28.webp" alt="Khóa thông minh Keybolts T28" width="1600" height="1348" loading="lazy" decoding="async" class="size-full object-contain"></div>
      </div>
      <div class="flex flex-col gap-[24px]">
        <div class="flex items-center gap-[14px]"><span class="h-px w-[34px] bg-gold-200"/><span class="text-eyebrow text-gold-200 font-bold tracking-[0.24em] uppercase">Công nghệ</span></div>
        <h2 class="m-0 text-[clamp(var(--text-display),3.6vw,var(--text-display-lg))] leading-[1.1] font-bold tracking-[-0.032em]">
          Khóa vân tay thế hệ mới
        </h2>
        <p class="text-heading m-0 max-w-[470px] leading-[1.75] font-light text-white/74">
          Tích hợp nhận diện vân tay, mã số và thẻ từ — mở cửa trong một giây, an toàn hơn
          khóa cơ truyền thống.
        </p>
      <ul class="m-0 mt-[8px] flex list-none flex-col p-0">
        <li
          v-for="feat in tech.features"
          :key="feat"
          class="text-body flex items-center gap-[18px] border-b border-white/12 py-[18px] leading-[1.6] text-white/90"
        >
          <svg class="flex-none text-gold-200" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M20 6 9 17l-5-5"/></svg>{{ feat }}
        </li>
      </ul>
        <NuxtLink to="#categories" class="text-body mt-[14px] inline-flex w-fit items-center gap-[11px] rounded-sm bg-gold-200 px-[32px] py-[17px] font-bold tracking-[0.07em] text-charcoal-900 uppercase no-underline hover:bg-white">Xem dòng khóa thông minh <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6"><path d="M5 12h14"/><path d="m13 6 6 6-6 6"/></svg></NuxtLink>
      </div>
    </div>
  </section>
</template>
