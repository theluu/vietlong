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
        <span class="text-display text-gold-200 font-bold">Keybolts</span>
        <p class="text-body m-0 leading-relaxed text-white/60">
          Nhập khẩu &amp; phân phối khóa cửa, khóa thông minh và phụ kiện cửa cao cấp cho
          nhà ở, biệt thự, khách sạn và công trình trên toàn quốc.
        </p>
        <span class="text-caption text-white/45">
          Công ty TNHH XNK<br>Khóa Cửa Việt Long
        </span>
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
        <a :href="HOTLINE_TEL" class="text-heading text-gold-200 font-bold no-underline">
          {{ HOTLINE }}
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
