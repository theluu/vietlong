<script setup lang="ts">
const productLinks = [
  'Khóa thông minh',
  'Khóa vân tay',
  'Khóa khách sạn',
  'Khóa đồng nhập khẩu',
  'Bản lề & phụ kiện',
  'Chốt cửa Cremone',
]

const supportLinks = [
  'Cam kết chất lượng',
  'Chính sách bảo hành',
  'Giao nhận hàng',
  'Đổi trả hàng',
  'Câu hỏi thường gặp',
  'Trở thành đại lý',
]
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
          <span class="text-eyebrow tracking-[0.16em] text-white/50 uppercase">Công ty TNHH XNK</span>
          <strong class="text-body tracking-[0.01em]">Khóa Cửa Việt Long</strong>
        </div>
        <div class="mt-[4px] flex flex-col gap-[14px]">
          <span class="text-eyebrow text-gold-200 font-bold tracking-[0.2em] uppercase">Sản phẩm có mặt tại</span>
          <div class="flex flex-wrap gap-[12px]">
            <a href="https://www.facebook.com/khoacuacaocapvietlong/" target="_blank" rel="noopener" aria-label="Facebook" class="grid size-[44px] place-items-center rounded-full bg-[#1877f2] text-white no-underline transition hover:-translate-y-1">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M22 12a10 10 0 1 0-11.6 9.9v-7H7.9V12h2.5V9.8c0-2.5 1.5-3.9 3.8-3.9 1.1 0 2.2.2 2.2.2v2.5h-1.3c-1.2 0-1.6.8-1.6 1.6V12h2.8l-.4 2.9h-2.4v7A10 10 0 0 0 22 12Z"/></svg>
            </a>
            <a href="https://www.youtube.com" target="_blank" rel="noopener" aria-label="YouTube" class="grid size-[44px] place-items-center rounded-full bg-[#ff0000] text-white no-underline transition hover:-translate-y-1">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M23 12s0-3.6-.5-5.3c-.3-1-1-1.7-2-2C18.8 4.2 12 4.2 12 4.2s-6.8 0-8.5.5c-1 .3-1.7 1-2 2C1 8.4 1 12 1 12s0 3.6.5 5.3c.3 1 1 1.7 2 2 1.7.5 8.5.5 8.5.5s6.8 0 8.5-.5c1-.3 1.7-1 2-2 .5-1.7.5-5.3.5-5.3ZM9.8 15.5v-7l6 3.5-6 3.5Z"/></svg>
            </a>
            <a href="https://zalo.me/19009018" target="_blank" rel="noopener" aria-label="Zalo" class="grid size-[44px] place-items-center rounded-full bg-[#0068ff] text-[11px] font-bold text-white no-underline transition hover:-translate-y-1">Zalo</a>
            <a href="https://shopee.vn" target="_blank" rel="noopener" aria-label="Shopee" class="grid size-[44px] place-items-center rounded-full bg-[#ee4d2d] text-white no-underline transition hover:-translate-y-1"><svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg></a>
            <a href="https://www.lazada.vn/" target="_blank" rel="noopener" aria-label="Lazada" class="grid size-[44px] place-items-center rounded-full bg-[#f57224] text-[11px] font-bold text-white no-underline transition hover:-translate-y-1">LZD</a>
          </div>
          <a href="https://online.gov.vn" target="_blank" rel="noopener" class="mt-[6px] inline-flex w-fit items-center gap-[12px] rounded-sm bg-white px-[16px] py-[10px] no-underline">
            <span class="grid size-[34px] flex-none place-items-center rounded-full bg-[#d0021b] text-white"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M20 6 9 17l-5-5"/></svg></span>
            <span class="flex flex-col gap-px"><strong class="text-caption tracking-[0.06em] text-[#d0021b] uppercase">Đã đăng ký</strong><strong class="text-eyebrow tracking-[0.1em] text-charcoal-900 uppercase">Bộ Công Thương</strong></span>
          </a>
        </div>
      </div>

      <!-- Products -->
      <div class="flex flex-col gap-[16px]">
        <span class="text-eyebrow text-gold-200 font-bold tracking-[0.2em] uppercase">
          Sản phẩm
        </span>
        <NuxtLink
          v-for="label in productLinks"
          :key="label"
          to="/san-pham"
          class="text-body text-white/62 no-underline hover:text-white"
        >{{ label }}</NuxtLink>
      </div>

      <!-- Support -->
      <div class="flex flex-col gap-[16px]">
        <span class="text-eyebrow text-gold-200 font-bold tracking-[0.2em] uppercase">
          Hỗ trợ
        </span>
        <NuxtLink
          v-for="label in supportLinks"
          :key="label"
          to="/chinh-sach"
          class="text-body text-white/62 no-underline hover:text-white"
        >{{ label }}</NuxtLink>
      </div>

      <!-- Contact -->
      <div class="flex flex-col gap-[16px]">
        <span class="text-eyebrow text-gold-200 font-bold tracking-[0.2em] uppercase">
          Liên hệ
        </span>
        <a :href="HOTLINE_TEL" class="text-[clamp(var(--text-display),2.4vw,31px)] leading-[1.1] font-bold tracking-[-0.01em] text-gold-200 no-underline">{{ HOTLINE }}</a>
        <a
          href="mailto:khoacuavietlong@gmail.com"
          class="text-body text-white/62 no-underline hover:text-gold-200"
        >khoacuavietlong@gmail.com</a>
        <span class="text-body leading-[1.7] text-white/62">
          Khu phố Lê Hồng Phong, P. Đông Ngàn, TP. Từ Sơn, Bắc Ninh
        </span>
        <div class="mt-[8px] flex flex-col gap-[8px] border-t border-white/14 pt-[18px]"><span class="text-eyebrow tracking-[0.16em] text-white/50 uppercase">Giờ làm việc</span><span class="text-body text-white/78">T2 – T7: 8:00 – 17:30</span></div>
      </div>
    </div>

    <div class="border-t border-white/10">
      <div
        class="text-caption mx-auto flex max-w-[var(--container-max)] flex-col gap-[20px] px-[clamp(20px,4vw,48px)] py-[20px] tracking-[0.04em] text-white/50 md:flex-row md:items-center md:justify-between"
      >
        <span>
          © 2026 Công ty TNHH XNK Khóa Cửa Việt Long — Keybolts. Bảo lưu mọi quyền.
        </span>
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
