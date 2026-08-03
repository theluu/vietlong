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
  <footer class="bg-charcoal-900 text-white">
    <div
      class="mx-auto grid max-w-[var(--container-max)] grid-cols-1 gap-10 px-[clamp(20px,4vw,48px)] py-14 md:grid-cols-[1.4fr_1fr_1fr_1.2fr]"
    >
      <!-- Brand -->
      <div class="flex flex-col gap-4">
        <img
          src="https://keybolts.com.vn/sites/all/themes/khoavietlong/images/logoweb.png"
          alt="Keybolts"
          class="h-[58px] w-fit max-w-full object-contain"
        >
        <p class="text-body m-0 leading-relaxed text-white/60">
          Nhập khẩu &amp; phân phối khóa cửa, khóa thông minh và phụ kiện cửa cao cấp cho
          nhà ở, biệt thự, khách sạn và công trình trên toàn quốc.
        </p>
        <span class="text-caption text-white/45">
          Công ty TNHH XNK<br>Khóa Cửa Việt Long
        </span>
        <div class="mt-2 flex flex-col gap-3">
          <span class="text-eyebrow text-gold-200 font-bold tracking-[0.16em] uppercase">Sản phẩm có mặt tại</span>
          <div class="flex flex-wrap gap-3">
            <a href="https://www.facebook.com/khoacuacaocapvietlong/" target="_blank" rel="noopener" aria-label="Facebook" class="grid size-11 place-items-center rounded-full bg-[#1877f2] text-white no-underline transition hover:-translate-y-1">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M22 12A10 10 0 1 0 10.4 22v-7H8v-3h2.4V9.7c0-2.5 1.5-3.9 3.8-3.9 1.1 0 2.2.2 2.2.2v2.5h-1.3c-1.2 0-1.6.8-1.6 1.6V12h2.8l-.4 3h-2.4v7A10 10 0 0 0 22 12Z"/></svg>
            </a>
            <a href="https://www.youtube.com" target="_blank" rel="noopener" aria-label="YouTube" class="grid size-11 place-items-center rounded-full bg-[#ff0000] text-white no-underline transition hover:-translate-y-1">
              <svg width="23" height="23" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M23.5 6.2a3 3 0 0 0-2.1-2.1C19.5 3.6 12 3.6 12 3.6s-7.5 0-9.4.5A3 3 0 0 0 .5 6.2 31 31 0 0 0 0 12a31 31 0 0 0 .5 5.8 3 3 0 0 0 2.1 2.1c1.9.5 9.4.5 9.4.5s7.5 0 9.4-.5a3 3 0 0 0 2.1-2.1A31 31 0 0 0 24 12a31 31 0 0 0-.5-5.8ZM9.6 15.6V8.4L15.8 12l-6.2 3.6Z"/></svg>
            </a>
            <a href="https://zalo.me/19009018" target="_blank" rel="noopener" aria-label="Zalo" class="grid size-11 place-items-center rounded-full bg-[#0068ff] text-[11px] font-bold text-white no-underline transition hover:-translate-y-1">Zalo</a>
            <a href="https://shopee.vn" target="_blank" rel="noopener" aria-label="Shopee" class="grid size-11 place-items-center rounded-full bg-[#ee4d2d] text-[11px] font-bold text-white no-underline transition hover:-translate-y-1">S</a>
          </div>
          <a href="http://online.gov.vn" target="_blank" rel="noopener" class="mt-1 flex w-fit items-center gap-3 border border-white/20 px-4 py-2.5 text-white no-underline hover:border-gold-200">
            <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M12 3 3 7v6c0 5 3.8 7.8 9 8 5.2-.2 9-3 9-8V7l-9-4Z"/><path d="m8 12 2.5 2.5L16 9"/></svg>
            <span class="flex flex-col"><span class="text-[10px] text-white/55 uppercase">Đã thông báo</span><strong class="text-eyebrow tracking-[0.1em] uppercase">Bộ Công Thương</strong></span>
          </a>
        </div>
      </div>

      <!-- Products -->
      <div class="flex flex-col gap-[14px]">
        <span class="text-eyebrow text-gold-200 font-bold tracking-[0.18em] uppercase">
          Sản phẩm
        </span>
        <NuxtLink
          v-for="label in productLinks"
          :key="label"
          to="/san-pham"
          class="text-body text-white/60 no-underline hover:text-gold-200"
        >{{ label }}</NuxtLink>
      </div>

      <!-- Support -->
      <div class="flex flex-col gap-[14px]">
        <span class="text-eyebrow text-gold-200 font-bold tracking-[0.18em] uppercase">
          Hỗ trợ
        </span>
        <NuxtLink
          v-for="label in supportLinks"
          :key="label"
          to="/chinh-sach"
          class="text-body text-white/60 no-underline hover:text-gold-200"
        >{{ label }}</NuxtLink>
      </div>

      <!-- Contact -->
      <div class="flex flex-col gap-[14px]">
        <span class="text-eyebrow text-gold-200 font-bold tracking-[0.18em] uppercase">
          Liên hệ
        </span>
        <a :href="HOTLINE_TEL" class="text-heading flex items-center gap-2 text-gold-200 font-bold no-underline">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>{{ HOTLINE }}
        </a>
        <a
          href="mailto:khoacuavietlong@gmail.com"
          class="text-body text-white/60 no-underline hover:text-gold-200"
        >khoacuavietlong@gmail.com</a>
        <span class="text-body text-white/60">
          Khu phố Lê Hồng Phong, P. Đông Ngàn, TP. Từ Sơn, Bắc Ninh
        </span>
        <span class="text-eyebrow mt-2 text-gold-200 font-bold tracking-[0.18em] uppercase">
          Giờ làm việc
        </span>
        <span class="text-body text-white/60">T2 – T7: 8:00 – 17:30</span>
      </div>
    </div>

    <div class="border-t border-white/10">
      <div
        class="text-caption mx-auto flex max-w-[var(--container-max)] flex-col gap-3 px-[clamp(20px,4vw,48px)] py-6 text-white/45 md:flex-row md:items-center md:justify-between"
      >
        <span>
          © 2026 Công ty TNHH XNK Khóa Cửa Việt Long — Keybolts. Bảo lưu mọi quyền.
        </span>
        <span class="flex gap-6">
          <NuxtLink to="/chinh-sach" class="text-white/45 no-underline hover:text-gold-200">
            Điều khoản
          </NuxtLink>
          <NuxtLink to="/chinh-sach" class="text-white/45 no-underline hover:text-gold-200">
            Bảo mật
          </NuxtLink>
          <NuxtLink to="/" class="text-white/45 no-underline hover:text-gold-200">
            Sitemap
          </NuxtLink>
        </span>
      </div>
    </div>
  </footer>
</template>
