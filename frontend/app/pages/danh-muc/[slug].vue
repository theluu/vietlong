<script setup lang="ts">
import { fetchCategory } from '~/services/products'

const route = useRoute()
const id = computed(() => String(route.params.slug))

const { data } = await useAsyncData(
  () => `category:${id.value}`,
  () => fetchCategory(id.value),
)

const branch = computed(() => data.value?.data)

if (!branch.value) {
  throw createError({ statusCode: 404, statusMessage: 'Không tìm thấy danh mục', fatal: true })
}

/*
 * Only a top category earns a page of its own, and it is a place to choose a
 * direction — not a grid. Everything below is a listing, and the listing
 * lives at /san-pham where the category tree stays on screen; sending a
 * visitor to a second grid without that tree would strand them.
 */
if (branch.value.parent !== 0) {
  await navigateTo(`/san-pham?category=${id.value}`, { redirectCode: 301, replace: true })
}

const name = computed(() => branch.value!.name)

useSeoMeta({
  title: () => `${name.value} — Keybolts`,
  description: () =>
    branch.value?.desc
    || `Danh mục ${name.value} của Keybolts — nhập khẩu chính hãng, đạt chứng nhận CE-CFF, bảo hành 5–10 năm.`,
})

useHead(() => ({
  link: [{ rel: 'canonical', href: `https://keybolts.com.vn${route.path}` }],
}))
</script>

<template>
  <div v-if="branch">
    <ProductListingHero
      :title="branch.name"
      eyebrow="Danh mục"
      :description="branch.desc"
      :total="branch.total"
      :breadcrumb="[
        { label: 'Trang chủ', url: '/' },
        { label: 'Sản phẩm', url: '/san-pham' },
        { label: branch.name, url: `/danh-muc/${branch.id}` },
      ]"
    />

    <div class="mx-auto max-w-[var(--container-max)] px-[clamp(20px,4vw,48px)] py-[clamp(40px,5vw,72px)]">
      <h2 class="text-display m-0 font-bold tracking-[-0.01em]">Chọn loại phù hợp</h2>
      <p class="text-body text-text-muted mt-3 mb-9 max-w-[620px] leading-relaxed">
        Mỗi nhóm dưới đây là một loại sản phẩm khác nhau. Chọn nhóm đúng trước, rồi lọc
        tiếp theo nhu cầu và tính năng ở trang danh sách.
      </p>

      <div class="kb-choice-grid gap-[clamp(16px,2vw,28px)]">
        <NuxtLink
          v-for="child in branch.children"
          :key="child.id"
          :to="`/san-pham?category=${child.id}`"
          class="group flex flex-col border border-border bg-background text-inherit no-underline transition hover:-translate-y-1 hover:border-brass-500 hover:shadow-floating"
        >
          <span class="relative block aspect-[4/3] overflow-hidden border-b border-border bg-white">
            <UiResponsiveImage
              :image="child.image"
              :alt="child.name"
              sizes="(min-width: 1024px) 33vw, 100vw"
              class="h-full w-full object-contain p-6 transition-transform duration-500 group-hover:scale-105"
            />
          </span>
          <span class="flex flex-1 flex-col gap-[10px] p-[22px]">
            <span class="text-heading font-bold text-text">{{ child.name }}</span>
            <span v-if="child.desc" class="text-body text-text-muted leading-relaxed">
              {{ child.desc }}
            </span>
            <span class="text-caption text-text-muted mt-auto pt-3">
              {{ child.count }} sản phẩm
            </span>
            <span class="text-caption text-brass-700 font-bold">
              Xem {{ child.name.toLowerCase() }} →
            </span>
          </span>
        </NuxtLink>
      </div>

      <div class="mt-10 flex flex-wrap items-center gap-4 border-t border-border pt-7">
        <span class="text-body text-text-muted">Không chắc nên chọn loại nào?</span>
        <NuxtLink
          to="/lien-he"
          class="text-body text-brass-700 font-bold no-underline hover:underline"
        >Gửi kích thước và loại cửa, chúng tôi chọn giúp →</NuxtLink>
        <NuxtLink
          :to="`/san-pham?category=${branch.id}`"
          class="text-body text-text-muted no-underline hover:text-brass-700"
        >Hoặc xem tất cả {{ branch.total }} sản phẩm</NuxtLink>
      </div>
    </div>
  </div>
</template>

<style scoped>
.kb-choice-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(min(100%, 280px), 1fr));
}
</style>
