<script setup lang="ts">
import { searchProducts } from '~/services/products'

/**
 * The header overlay has always pushed to /tim-kiem on submit, but the page
 * never existed — pressing Enter in the search box landed on a 404.
 */
const PER_PAGE = 12

const route = useRoute()
const router = useRouter()

const query = computed(() => String(route.query.q ?? '').trim())
const page = computed(() => Math.max(1, Number(route.query.page) || 1))

const { data, pending } = await useAsyncData(
  () => `search:${query.value}:${page.value}`,
  () => (query.value ? searchProducts(query.value, page.value, PER_PAGE) : Promise.resolve(null)),
  { watch: [query, page] },
)

const results = computed(() => data.value?.data ?? [])
const total = computed(() => data.value?.meta?.total ?? 0)
const pageCount = computed(() => Math.max(1, Math.ceil(total.value / PER_PAGE)))

const heading = computed(() => {
  if (!query.value) return 'Nhập từ khóa để tìm sản phẩm'
  if (pending.value) return 'Đang tìm…'
  return total.value
    ? `${total.value} kết quả cho “${query.value}”`
    : `Không tìm thấy kết quả cho “${query.value}”`
})

// Keep the term in the URL so a result page can be shared or reloaded.
const term = ref(query.value)
watch(query, value => term.value = value)

const submit = () => {
  const q = term.value.trim()
  if (q) router.push({ path: '/tim-kiem', query: { q } })
}

const goPage = (n: number) => {
  router.push({ path: '/tim-kiem', query: { q: query.value, page: n > 1 ? n : undefined } })
}

const { hotline: HOTLINE, hotlineTel: HOTLINE_TEL } = useSite()

useSeoMeta({
  title: () => (query.value ? `Tìm kiếm: ${query.value} | Keybolts` : 'Tìm kiếm | Keybolts'),
  description: 'Tìm khóa cửa, model và phụ kiện trong toàn bộ danh mục Keybolts.',
  // Search result pages are near-infinite and thin; keeping them out of the
  // index protects the catalogue pages that should rank instead.
  robots: 'noindex, follow',
})
</script>

<template>
  <div>
    <PageCenteredHero
      eyebrow="Tìm kiếm"
      title="Tìm sản phẩm"
      subtitle="Tra cứu theo tên, model hoặc loại khóa — không dấu cũng tìm được."
    />

    <section class="bg-background py-[clamp(40px,4.5vw,64px)]">
      <div class="mx-auto max-w-[var(--container-max)] px-[clamp(20px,4vw,48px)]">
        <LayoutBreadcrumb
          :items="[
            { label: 'Trang chủ', url: '/' },
            { label: 'Tìm kiếm', url: '/tim-kiem' },
          ]"
        />

        <form class="mt-[24px] flex flex-wrap gap-[10px]" @submit.prevent="submit">
          <input
            v-model="term"
            type="search"
            aria-label="Từ khóa tìm kiếm"
            placeholder="Tìm khóa cửa, model, phụ kiện…"
            class="text-body min-w-0 flex-1 border border-border bg-background px-[16px] py-[13px] text-text outline-none focus:border-brass-500"
          >
          <button
            type="submit"
            class="text-caption cursor-pointer rounded-sm bg-charcoal-900 px-[26px] py-[13px] font-bold tracking-[0.08em] text-gold-200 uppercase transition hover:bg-brass-700"
          >Tìm kiếm</button>
        </form>

        <p class="text-heading mt-[28px] mb-[24px] font-bold text-text">{{ heading }}</p>

        <div v-if="results.length" class="kb-product-grid gap-[clamp(16px,1.8vw,26px)]">
          <ProductCard v-for="product in results" :key="product.id" :product="product" />
        </div>

        <div
          v-else-if="query && !pending"
          class="flex flex-col items-start gap-[14px] border border-border bg-surface px-[26px] py-[46px]"
        >
          <p class="text-body text-text-muted m-0">
            Thử từ khóa ngắn hơn, hoặc bỏ dấu — ví dụ “khoa van tay”.
          </p>
          <a :href="HOTLINE_TEL" class="text-body text-brass-700 font-bold no-underline">
            Gọi {{ HOTLINE }} để được tư vấn →
          </a>
        </div>

        <div v-if="pageCount > 1" class="mt-[40px] flex justify-center">
          <ProductPagination
            :page="page"
            :total="total"
            :per-page="PER_PAGE"
            @go="goPage"
          />
        </div>
      </div>
    </section>
  </div>
</template>
