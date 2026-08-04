<script setup lang="ts">
import type { Branch } from '~/types/page'

const props = defineProps<{ branches: Branch[] }>()

const active = ref(0)

/**
 * The embed and the directions link are both derived from the address, so an
 * editor only ever maintains one field per branch.
 */
const mapSrc = computed(() => {
  const address = props.branches[active.value]?.address ?? ''
  return `https://maps.google.com/maps?q=${encodeURIComponent(address)}&z=15&output=embed`
})

const directions = (branch: Branch) =>
  branch.mapUrl || `https://www.google.com/maps/dir/?api=1&destination=${encodeURIComponent(branch.address)}`
</script>

<template>
  <section id="map" class="border-border bg-background border-t py-[clamp(48px,5vw,76px)]">
    <div class="mx-auto max-w-[var(--container-max)] px-[clamp(20px,4vw,48px)]">
      <PageSectionHeading
        eyebrow="Bản đồ"
        title="Tìm showroom gần bạn"
        intro="Chọn một cơ sở để xem vị trí trên bản đồ — bấm “Chỉ đường” để mở dẫn đường trên điện thoại."
        rules
      />

      <div class="kb-map-grid border-border overflow-hidden border">
        <div class="kb-track bg-surface flex max-h-[340px] flex-col overflow-y-auto md:max-h-[520px]">
          <button
            v-for="(branch, i) in branches"
            :key="branch.id"
            type="button"
            :aria-pressed="i === active"
            class="border-border flex cursor-pointer flex-col gap-[6px] border-0 border-b border-l-[3px] px-[22px] py-[20px] text-left transition-colors duration-200 ease-in-out"
            :class="i === active ? 'border-l-brass-500 bg-background' : 'border-l-transparent bg-transparent hover:bg-background/60'"
            @click="active = i"
          >
            <span class="text-brass-700 text-[10px] font-bold tracking-[0.18em] uppercase">{{ branch.tag }}</span>
            <span class="text-body leading-[1.35] font-bold">{{ branch.name }}</span>
            <span class="text-caption text-text-muted leading-[1.65]">{{ branch.address }}</span>
            <span class="mt-[4px] flex items-center gap-[16px]">
              <a :href="`tel:${branch.phoneTel}`" class="text-caption text-brass-700 font-bold no-underline" @click.stop>{{ branch.phoneDisplay }}</a>
              <a
                :href="directions(branch)"
                target="_blank"
                rel="noopener"
                class="text-caption text-charcoal-900 hover:text-brass-700 inline-flex items-center gap-[6px] font-bold tracking-[0.06em] uppercase no-underline"
                @click.stop
              >
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 11l19-9-9 19-2-8-8-2z"/></svg>
                Chỉ đường
              </a>
            </span>
          </button>
        </div>

        <div class="bg-surface relative min-h-[340px] md:min-h-[520px]">
          <iframe
            :src="mapSrc"
            title="Bản đồ showroom Keybolts"
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade"
            class="absolute inset-0 block h-full w-full border-0"
          />
        </div>
      </div>
    </div>
  </section>
</template>
