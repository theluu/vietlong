<script setup lang="ts">
import { fetchPolicies } from '~/services/pages'

const { data } = await useAsyncData('page:policies', () => fetchPolicies())
const page = computed(() => data.value?.data)

if (!page.value) {
  throw createError({ statusCode: 404, statusMessage: 'Không tìm thấy trang', fatal: true })
}

const active = ref(page.value.sections[0]?.key ?? '')
const current = computed(() => page.value!.sections.find(s => s.key === active.value) ?? page.value!.sections[0])

useSeoMeta({
  title: () => `${page.value?.title} | Keybolts`,
  description: () => page.value?.subtitle,
})
useHead({ link: [{ rel: 'canonical', href: 'https://keybolts.com.vn/chinh-sach' }] })
</script>

<template>
  <div v-if="page">
    <PageHero
      :eyebrow="page.eyebrow"
      :title="page.title"
      :subtitle="page.subtitle"
      :breadcrumb="[
        { label: 'Trang chủ', url: '/' },
        { label: 'Chính sách', url: '/chinh-sach' },
      ]"
    />

    <div class="mx-auto grid max-w-[var(--container-max)] gap-8 px-[clamp(20px,4vw,48px)] py-12 lg:grid-cols-[260px_minmax(0,1fr)]">
      <aside class="flex flex-col gap-4">
        <nav class="flex flex-col border border-border">
          <button
            v-for="s in page.sections"
            :key="s.key"
            type="button"
            class="text-body cursor-pointer border-none border-l-2 px-5 py-3 text-left transition"
            :class="s.key === active
              ? 'border-brass-500 bg-surface text-text font-bold'
              : 'border-transparent bg-background text-text-muted hover:bg-surface'"
            @click="active = s.key"
          >{{ s.label }}</button>
        </nav>

        <div class="flex flex-col gap-2 bg-charcoal-900 p-6 text-white">
          <span class="text-caption text-gold-200 font-bold tracking-[0.14em] uppercase">
            {{ page.supportTitle }}
          </span>
          <a :href="HOTLINE_TEL" class="text-heading text-gold-200 font-bold no-underline">{{ HOTLINE }}</a>
          <span class="text-caption text-white/60">{{ page.supportNote }}</span>
        </div>
      </aside>

      <article v-if="current" class="flex flex-col gap-5">
        <span class="text-eyebrow text-brass-700 font-bold tracking-[0.18em] uppercase">{{ current.eyebrow }}</span>
        <h2 class="text-display m-0 font-bold tracking-[-0.02em]">{{ current.title }}</h2>
        <p class="text-body text-text-muted m-0 leading-relaxed">{{ current.intro }}</p>

        <dl class="m-0 flex flex-col gap-px bg-border">
          <div
            v-for="it in current.items"
            :key="it.k"
            class="grid gap-2 bg-background p-5 md:grid-cols-[190px_minmax(0,1fr)]"
          >
            <dt class="text-body font-bold">{{ it.k }}</dt>
            <dd class="text-body text-text-muted m-0 leading-relaxed">{{ it.v }}</dd>
          </div>
        </dl>

        <p v-if="current.note" class="text-caption text-text-muted m-0 border-l-2 border-brass-500 pl-4 leading-relaxed">
          {{ current.note }}
        </p>
      </article>
    </div>
  </div>
</template>
