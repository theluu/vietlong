<script setup lang="ts">
defineProps<{
  eyebrow: string
  title: string
  body: string
  phone: string
  image?: string
}>()
</script>

<template>
  <section class="bg-background py-[clamp(48px,5vw,80px)]">
    <div class="mx-auto max-w-[var(--container-max)] px-[clamp(20px,4vw,48px)]">
      <div class="kb-cta-panel border-border border">
        <div class="flex flex-col justify-center gap-[16px] p-[clamp(32px,3.6vw,52px)]">
          <span class="text-eyebrow text-brass-700 font-bold tracking-[0.22em] uppercase">{{ eyebrow }}</span>
          <h2 class="text-display-lg m-0 leading-[1.15] font-bold tracking-[-0.02em]">{{ title }}</h2>
          <p class="text-body text-text-muted m-0 leading-[1.85]">{{ body }}</p>
          <div class="flex flex-wrap gap-[14px] pt-[8px]">
            <a
              :href="`tel:${phone.replace(/\s/g, '')}`"
              class="text-body bg-charcoal-900 text-gold-200 hover:bg-neutral-700 inline-flex items-center gap-[11px] rounded-sm px-[30px] py-[16px] font-bold tracking-[0.06em] uppercase no-underline transition"
            >
              <svg width="17" height="17" viewBox="0 0 24 24" fill="currentColor"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
              {{ phone }}
            </a>
            <NuxtLink
              to="/lien-he"
              class="text-body text-charcoal-900 border-neutral-300 hover:border-brass-500 hover:bg-surface inline-flex items-center rounded-sm border px-[30px] py-[16px] font-bold tracking-[0.06em] uppercase no-underline transition"
            >Gửi yêu cầu</NuxtLink>
          </div>
        </div>
        <div v-if="image" class="group border-border relative min-h-[280px] overflow-hidden border-l bg-white">
          <img :src="image" :alt="title" class="absolute inset-0 h-full w-full object-cover transition duration-500 group-hover:scale-[1.05]" loading="lazy">
        </div>
      </div>
    </div>
  </section>
</template>
