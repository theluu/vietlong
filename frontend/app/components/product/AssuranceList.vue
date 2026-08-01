<template>
  <div
    class="kb-assurance-grid gap-px border border-border bg-border"
  >
    <div
      v-for="item in [
        { title: 'Giao 2–5 ngày', desc: 'Toàn quốc, từ 4 kho miền Bắc' },
        { title: 'Bảo hành 5–10 năm', desc: 'Phiếu bảo hành theo bộ' },
        { title: 'Hỗ trợ lắp đặt', desc: 'Hướng dẫn kỹ thuật miễn phí' },
      ]"
      :key="item.title"
      class="flex flex-col gap-2 bg-background p-5"
    >
      <span class="text-caption text-brass-700 font-bold tracking-[0.1em] uppercase">
        {{ item.title }}
      </span>
      <span class="text-caption text-text-muted leading-relaxed">{{ item.desc }}</span>
    </div>
  </div>
</template>
