<script setup lang="ts">
import { fetchProducts } from '~/services/products'
import { fromQuery, toQuery, type FilterState } from '~/utils/productFilterState'
import type { ListMeta } from '~/types/product'

const { hotline: HOTLINE, hotlineTel: HOTLINE_TEL } = useSite()
const props = defineProps<{
  title: string
  eyebrow?: string
  description?: string
  /** Pins one axis: pre-applied to every query and hidden from the sidebar. */
  lockedFilter?: { axis: 'brand' | 'category'; value: string }
  breadcrumb: { label: string; url: string }[]
}>()

const route = useRoute()
const router = useRouter()

const state = computed<FilterState>(() => {
  const base = fromQuery(route.query as Record<string, unknown>)
  if (props.lockedFilter) base[props.lockedFilter.axis] = props.lockedFilter.value
  return base
})

const { data, pending } = await useAsyncData(
  () => `products:${JSON.stringify(toQuery(state.value))}`,
  () => fetchProducts({ ...toQuery(state.value), page: state.value.page }),
  { watch: [state] },
)

const products = computed(() => data.value?.data ?? [])
const meta = computed<ListMeta>(
  () => (data.value?.meta as ListMeta) ?? { total: 0, page: 1, limit: 12 },
)

// The locked axis must not appear as a filter the user can change.
const facets = computed(() => {
  const all = data.value?.facets ?? {}
  if (!props.lockedFilter) return all
  const { [props.lockedFilter.axis]: _omit, ...rest } = all
  return rest
})

const activeCatName = computed(() => {
  if (props.lockedFilter?.axis === 'category') return props.title
  const id = state.value.category
  return (id && data.value?.facets?.category?.[id]?.label) || 'Tất cả sản phẩm'
})

const rangeLabel = computed(() => {
  const total = meta.value.total
  if (!total) return '0 sản phẩm'
  const from = (meta.value.page - 1) * meta.value.limit + 1
  const to = Math.min(meta.value.page * meta.value.limit, total)
  return `Hiển thị ${from}–${to} trong ${total} sản phẩm`
})

function update(patch: Partial<FilterState>) {
  // Any filter change resets to page 1 — staying on page 4 of a narrower
  // result set would strand the user on an empty page.
  const next = { ...state.value, ...patch }
  if (!('page' in patch)) next.page = 1
  // The locked axis lives in the path, not the query string.
  if (props.lockedFilter) next[props.lockedFilter.axis] = ''
  router.push({ query: toQuery(next) })
}

const clearFilters = () =>
  router.push({
    query: toQuery({ brand: '', category: '', finish: '', sort: state.value.sort, page: 1 }),
  })
</script>

<template>
  <div>
    <ProductListingHero :title="title" :eyebrow="eyebrow" :description="description" :total="meta.total" :breadcrumb="breadcrumb" />

    <div
      class="kb-catalog-grid mx-auto max-w-[var(--container-max)] gap-8 px-[clamp(20px,4vw,48px)] py-12"
    >
      <ProductFilterSidebar
        :facets="facets"
        :category="lockedFilter?.axis === 'category' ? '' : state.category"
        :finish="state.finish"
        @update="update"
      />

      <div class="flex min-w-0 flex-col gap-[26px]">
        <div
          class="flex flex-wrap items-baseline justify-between gap-5 border-b border-border pb-4"
        >
          <h2 class="text-display m-0 font-bold tracking-[-0.01em]">{{ activeCatName }}</h2>
          <div class="flex items-center gap-6">
            <span class="text-caption text-text-muted tracking-[0.04em]">{{ rangeLabel }}</span>
            <ProductSortSelect
              :model-value="state.sort"
              @update:model-value="update({ sort: $event })"
            />
          </div>
        </div>

        <div v-if="pending" class="text-body text-text-muted py-12">Đang tải…</div>

        <template v-else-if="products.length">
          <div class="kb-product-grid gap-[clamp(16px,1.6vw,24px)]">
            <ProductCard v-for="p in products" :key="p.id" :product="p" />
          </div>

          <ProductPagination
            :page="meta.page"
            :total="meta.total"
            :per-page="meta.limit"
            @go="update({ page: $event })"
          />
        </template>

        <ProductEmptyState v-else @clear="clearFilters" />
      </div>
    </div>

    <div class="bg-charcoal-900 text-white">
      <div
        class="mx-auto flex max-w-[var(--container-max)] flex-col gap-6 px-[clamp(20px,4vw,48px)] py-14 md:flex-row md:items-center md:justify-between"
      >
        <div class="flex flex-col gap-3">
          <span class="text-eyebrow text-gold-200 font-bold tracking-[0.24em] uppercase">
            Dành cho nhà thầu &amp; đại lý
          </span>
          <h2 class="text-display m-0 font-bold">Báo giá theo số lượng lớn</h2>
          <p class="text-body m-0 max-w-[560px] leading-relaxed text-white/72">
            Gửi danh sách model và số lượng — bộ phận kinh doanh phản hồi trong 24 giờ làm
            việc kèm chính sách công trình.
          </p>
        </div>
        <div class="flex flex-wrap gap-4">
          <a
            :href="HOTLINE_TEL"
            class="text-body rounded-sm bg-gold-200 px-8 py-4 font-bold tracking-[0.06em] text-charcoal-900 uppercase no-underline hover:bg-white"
          >Gọi {{ HOTLINE }}</a>
          <NuxtLink
            to="/lien-he"
            class="text-body rounded-sm border border-white/30 px-8 py-4 tracking-[0.06em] text-white uppercase no-underline hover:border-gold-200 hover:text-gold-200"
          >Gửi yêu cầu</NuxtLink>
        </div>
      </div>
    </div>
  </div>
</template>
