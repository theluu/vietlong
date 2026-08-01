<script setup lang="ts">
import { suggestProducts } from '~/services/products'
import type { ProductCard } from '~/types/product'

const { searchOpen, closeAll } = useSiteChrome()
const router = useRouter()

const query = ref('')
const results = ref<ProductCard[]>([])
const pending = ref(false)
const input = ref<HTMLInputElement | null>(null)

/** The element focus returns to when the overlay closes. */
let lastFocused: HTMLElement | null = null

const QUICK_TAGS = ['Khóa vân tay', 'Khóa đồng', 'Khóa khách sạn', 'Cremone', 'Bản lề']

const trimmed = computed(() => query.value.trim())
const heading = computed(() =>
  trimmed.value ? `Kết quả (${results.value.length})` : 'Gợi ý phổ biến',
)
const noResults = computed(
  () => trimmed.value.length > 0 && !pending.value && results.value.length === 0,
)

let timer: ReturnType<typeof setTimeout> | undefined

// 300ms debounce so typing does not fire a request per keystroke.
watch(query, (value) => {
  clearTimeout(timer)
  const q = value.trim()
  if (!q) {
    results.value = []
    pending.value = false
    return
  }
  pending.value = true
  timer = setTimeout(async () => {
    try {
      const res = await suggestProducts(q)
      // Ignore a response that lost the race with newer input.
      if (q === query.value.trim()) results.value = res.data
    }
    catch {
      results.value = []
    }
    finally {
      if (q === query.value.trim()) pending.value = false
    }
  }, 300)
})

watch(searchOpen, async (open) => {
  if (open) {
    lastFocused = document.activeElement as HTMLElement | null
    await nextTick()
    input.value?.focus()
  }
  else {
    query.value = ''
    results.value = []
    lastFocused?.focus()
    lastFocused = null
  }
})

const onKeydown = (e: KeyboardEvent) => {
  if (e.key === 'Escape' && searchOpen.value) closeAll()
}

onMounted(() => window.addEventListener('keydown', onKeydown))
onUnmounted(() => {
  clearTimeout(timer)
  if (import.meta.client) window.removeEventListener('keydown', onKeydown)
})

const submit = () => {
  if (!trimmed.value) return
  closeAll()
  router.push({ path: '/tim-kiem', query: { q: trimmed.value } })
}

const go = (card: ProductCard) => {
  closeAll()
  router.push(`/${card.slug}`)
}
</script>

<template>
  <div
    v-if="searchOpen"
    class="fixed inset-0 z-[90] flex flex-col items-center bg-[rgba(20,23,25,0.68)] px-5 pt-[clamp(70px,12vh,140px)] pb-10 backdrop-blur-[10px]"
  >
    <!-- Backdrop: clicking outside the panel closes. -->
    <div class="absolute inset-0" @click="closeAll" />

    <div
      class="shadow-floating relative w-full max-w-[720px] bg-background p-7"
      role="dialog"
      aria-modal="true"
      aria-label="Tìm kiếm sản phẩm"
    >
      <form @submit.prevent="submit">
        <input
          ref="input"
          v-model="query"
          type="search"
          placeholder="Tìm khóa cửa, model, phụ kiện…"
          class="text-heading w-full border-b border-border bg-transparent px-1 py-3 text-text outline-none focus:border-brass-500"
        >
      </form>

      <p class="text-eyebrow text-brass-700 mt-6 font-bold tracking-[0.18em] uppercase">
        {{ heading }}
      </p>

      <ul v-if="results.length" class="mt-3 flex list-none flex-col p-0">
        <li v-for="card in results" :key="card.id">
          <button
            type="button"
            class="flex w-full cursor-pointer items-center gap-4 border-none border-b border-border bg-transparent px-1 py-3 text-left hover:bg-surface"
            @click="go(card)"
          >
            <img
              v-if="card.image"
              :src="card.image.url"
              :alt="card.image.alt"
              class="h-12 w-12 flex-shrink-0 object-contain"
              loading="lazy"
            >
            <span class="flex min-w-0 flex-col">
              <span class="text-body truncate font-bold text-text">{{ card.name }}</span>
              <span class="text-caption text-text-muted truncate">{{ card.model }}</span>
            </span>
          </button>
        </li>
      </ul>

      <div v-else-if="noResults" class="mt-4 flex flex-col gap-2">
        <p class="text-body text-text-muted m-0">Không tìm thấy sản phẩm phù hợp.</p>
        <a :href="HOTLINE_TEL" class="text-body text-brass-700 font-bold no-underline">
          Gọi {{ HOTLINE }} để được tư vấn →
        </a>
      </div>

      <div v-else class="mt-3 flex flex-wrap gap-3">
        <button
          v-for="tag in QUICK_TAGS"
          :key="tag"
          type="button"
          class="text-caption text-text-muted cursor-pointer rounded-sm border border-border bg-transparent px-4 py-2 hover:border-brass-500 hover:text-brass-700"
          @click="query = tag"
        >{{ tag }}</button>
      </div>
    </div>
  </div>
</template>
