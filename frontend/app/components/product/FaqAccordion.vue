<script setup lang="ts">
defineProps<{ faqs: { q: string; a: string }[] }>()
const open = ref<number | null>(0)
</script>

<template>
  <div class="flex flex-col">
    <div v-for="(faq, i) in faqs" :key="faq.q" class="border-b border-border">
      <!-- The question is a heading that happens to be operable, not a bare
           control: it is what someone scanning the page for an answer looks
           for, and it belongs under the section's own H2. -->
      <h3 class="m-0">
        <button
          type="button"
          class="text-heading flex w-full cursor-pointer items-center justify-between gap-4 border-none bg-transparent px-0 py-4 text-left font-bold text-text"
          :aria-expanded="open === i"
          @click="open = open === i ? null : i"
        >
          {{ faq.q }}
          <span class="text-brass-700 flex-shrink-0">{{ open === i ? '−' : '+' }}</span>
        </button>
      </h3>
      <p v-if="open === i" class="text-body text-text-muted m-0 pb-5 leading-relaxed">
        {{ faq.a }}
      </p>
    </div>
  </div>
</template>
