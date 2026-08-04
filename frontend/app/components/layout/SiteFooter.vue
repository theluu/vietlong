<script setup lang="ts">
const { hotline: HOTLINE, hotlineTel: HOTLINE_TEL, contact, footer, social } = useSite()
</script>

<template>
  <footer id="site-footer" class="overflow-hidden border-t-2 border-gold-200 bg-charcoal-900 text-white">
    <div
      class="mx-auto grid max-w-[var(--container-max)] grid-cols-1 gap-[clamp(28px,3vw,52px)] px-[clamp(20px,4vw,48px)] pt-[clamp(52px,6vw,80px)] pb-[44px] md:grid-cols-[1.5fr_1fr_1fr_1.2fr]"
    >
      <!-- Brand -->
      <div class="flex min-w-[240px] flex-col gap-[20px]">
        <img
          src="https://keybolts.com.vn/sites/all/themes/khoavietlong/images/logoweb.png"
          alt="Keybolts"
          class="h-[56px] w-auto max-w-full self-start object-contain"
        >
        <p class="text-body m-0 max-w-[340px] leading-[1.8] text-white/62">
          Nhập khẩu &amp; phân phối khóa cửa, khóa thông minh và phụ kiện cửa cao cấp cho
          nhà ở, biệt thự, khách sạn và công trình trên toàn quốc.
        </p>
        <div class="flex flex-col gap-[10px] border-t border-white/14 pt-[18px]">
          <span class="text-eyebrow tracking-[0.16em] text-white/50 uppercase">{{ contact.companyName.replace(contact.companyShort, "").trim() }}</span>
          <strong class="text-body tracking-[0.01em]">{{ contact.companyShort }}</strong>
        </div>
        <div class="mt-[4px] flex flex-col gap-[14px]">
          <span class="text-eyebrow text-gold-200 font-bold tracking-[0.2em] uppercase">Sản phẩm có mặt tại</span>
          <div class="flex flex-wrap gap-[12px]">
            <LayoutSocialIcon
              v-for="item in social"
              :key="item.label"
              :icon="item.icon"
              :label="item.label"
              :url="item.url"
            />
          </div>
          <a href="https://online.gov.vn" target="_blank" rel="noopener" class="mt-[6px] inline-flex w-fit items-center gap-[12px] rounded-sm bg-white px-[16px] py-[10px] no-underline">
            <span class="grid size-[34px] flex-none place-items-center rounded-full bg-[#d0021b] text-white"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M20 6 9 17l-5-5"/></svg></span>
            <span class="flex flex-col gap-px"><strong class="text-caption tracking-[0.06em] text-[#d0021b] uppercase">Đã đăng ký</strong><strong class="text-eyebrow tracking-[0.1em] text-charcoal-900 uppercase">Bộ Công Thương</strong></span>
          </a>
        </div>
      </div>

      <!-- Link columns -->
      <div v-for="column in footer.columns" :key="column.title" class="flex flex-col gap-[16px]">
        <span class="text-eyebrow text-gold-200 font-bold tracking-[0.2em] uppercase">{{ column.title }}</span>
        <NuxtLink
          v-for="link in column.links"
          :key="link.label"
          :to="link.to"
          class="text-body text-white/62 no-underline hover:text-white"
        >{{ link.label }}</NuxtLink>
      </div>

      <!-- Contact -->
      <div class="flex flex-col gap-[16px]">
        <span class="text-eyebrow text-gold-200 font-bold tracking-[0.2em] uppercase">
          Liên hệ
        </span>
        <a :href="HOTLINE_TEL" class="text-[clamp(var(--text-display),2.4vw,31px)] leading-[1.1] font-bold tracking-[-0.01em] text-gold-200 no-underline">{{ HOTLINE }}</a>
        <a
          :href="`mailto:${contact.email}`"
          class="text-body text-white/62 no-underline hover:text-gold-200"
        >{{ contact.email }}</a>
        <span class="text-body leading-[1.7] text-white/62">{{ contact.address }}</span>
        <div class="mt-[8px] flex flex-col gap-[8px] border-t border-white/14 pt-[18px]"><span class="text-eyebrow tracking-[0.16em] text-white/50 uppercase">Giờ làm việc</span><span v-for="hours in contact.workingHours" :key="hours" class="text-body text-white/78">{{ hours }}</span></div>
      </div>
    </div>

    <div class="border-t border-white/10">
      <div
        class="text-caption mx-auto flex max-w-[var(--container-max)] flex-col gap-[20px] px-[clamp(20px,4vw,48px)] py-[20px] tracking-[0.04em] text-white/50 md:flex-row md:items-center md:justify-between"
      >
        <span>{{ footer.copyright }}</span>
        <span class="flex gap-6">
          <NuxtLink to="/chinh-sach" class="text-white/50 no-underline hover:text-gold-200">
            Điều khoản
          </NuxtLink>
          <NuxtLink to="/chinh-sach" class="text-white/50 no-underline hover:text-gold-200">
            Bảo mật
          </NuxtLink>
          <NuxtLink to="/" class="text-white/50 no-underline hover:text-gold-200">
            Sitemap
          </NuxtLink>
        </span>
      </div>
    </div>
  </footer>
</template>
