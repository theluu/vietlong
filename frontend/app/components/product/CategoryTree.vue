<script setup lang="ts">
import type { FacetOption } from '~/types/product'

const props = defineProps<{
  /** Term id -> option, as the products endpoint sends it. */
  options: Record<string, FacetOption>
  /** Currently filtered term id, '' for the whole catalogue. */
  selected: string
}>()

const emit = defineEmits<{ (e: 'select', id: string): void }>()

interface Row {
  id: string
  label: string
  count: number
  depth: number
}

/**
 * Children of each term, siblings in the vocabulary's own order.
 *
 * The payload is keyed by term id, and integer-like keys come back out of
 * JSON.parse in ascending numeric order regardless of how they were sent, so
 * the order has to be rebuilt here from the weight each option carries.
 */
const childrenOf = computed(() => {
  const byParent = new Map<number, Array<[string, FacetOption]>>()
  for (const entry of Object.entries(props.options)) {
    const parent = entry[1].parent ?? 0
    const bucket = byParent.get(parent)
    if (bucket) bucket.push(entry)
    else byParent.set(parent, [entry])
  }
  for (const bucket of byParent.values()) {
    bucket.sort(([, a], [, b]) => (a.weight ?? 0) - (b.weight ?? 0) || a.label.localeCompare(b.label, 'vi'))
  }
  return byParent
})

/** The whole tree flattened to rows, in reading order. Nothing is hidden. */
const rows = computed(() => {
  const out: Row[] = []
  const walk = (parent: number, depth: number) => {
    for (const [id, opt] of childrenOf.value.get(parent) ?? []) {
      out.push({ id, label: opt.label, count: opt.count, depth })
      walk(Number(id), depth + 1)
    }
  }
  walk(0, 0)
  return out
})

/*
 * Every product sits under exactly one top category, so the roots add up to
 * the catalogue as it stands under the other filters — which is precisely what
 * "all products" would return.
 */
const total = computed(() =>
  (childrenOf.value.get(0) ?? []).reduce((sum, [, opt]) => sum + opt.count, 0),
)
</script>

<template>
  <div class="flex flex-col">
    <button
      type="button"
      class="kb-cat-row flex cursor-pointer items-center justify-between gap-3 border-none bg-background px-5 py-[11px] text-left hover:bg-surface"
      :class="selected === '' ? 'text-brass-700 bg-surface font-bold' : 'text-text'"
      :aria-current="selected === '' ? 'true' : undefined"
      @click="emit('select', '')"
    >
      <span class="text-body">Tất cả sản phẩm</span>
      <span class="text-caption text-text-muted">{{ total }}</span>
    </button>

    <button
      v-for="row in rows"
      :key="row.id"
      type="button"
      class="kb-cat-row flex cursor-pointer items-stretch border-none bg-background pr-5 text-left hover:bg-surface"
      :class="[
        selected === row.id ? 'text-brass-700 bg-surface font-bold' : 'text-text',
        // A rule above each group turns four top categories into four blocks
        // rather than one twenty-nine-row list.
        row.depth === 0 ? 'border-t border-border' : '',
      ]"
      :aria-current="selected === row.id ? 'true' : undefined"
      @click="emit('select', selected === row.id ? '' : row.id)"
    >
      <!-- One hairline per level of nesting. Rows sit flush, so the spacers
           join up into a continuous rule down each branch. -->
      <span
        v-for="d in row.depth"
        :key="d"
        class="w-3 flex-none border-l border-border"
        :class="d === 1 ? 'ml-5' : ''"
        aria-hidden="true"
      />
      <span
        class="flex min-w-0 flex-1 items-center justify-between gap-3"
        :class="row.depth ? 'py-[7px] pl-3' : 'py-[11px] pl-5'"
      >
        <!-- Wraps rather than truncates: a filter the reader cannot finish
             reading is not a filter. The column is only 268px at its widest
             and "Khóa tay gạt đồng thông phòng" does not fit on one line. -->
        <span
          class="min-w-0"
          :class="{
            'text-body font-bold tracking-[0.01em]': row.depth === 0,
            'text-body': row.depth === 1,
            'text-caption': row.depth === 2,
            // Muted only while unselected — the row's own brass has to win.
            'text-text-muted': row.depth === 2 && selected !== row.id,
          }"
        >{{ row.label }}</span>
        <span class="text-caption text-text-muted flex-none">{{ row.count }}</span>
      </span>
    </button>
  </div>
</template>

<style scoped>
/* The selected row carries the only accent in the panel; the rules stay quiet
   so the brass edge reads as "you are here" at a glance. Positioned so the
   edge sits above the group rule rather than under it. */
.kb-cat-row[aria-current='true'] {
  box-shadow: inset 3px 0 0 0 var(--color-brass-700);
  position: relative;
}

.kb-cat-row:focus-visible {
  outline: 2px solid var(--color-brass-700);
  outline-offset: -2px;
  position: relative;
}
</style>
