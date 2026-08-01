<script setup lang="ts">
const name = ref('')
const phone = ref('')
const note = ref('')
const submitted = ref(false)

// The prototype only confirms locally; there is no submission endpoint yet.
const submit = () => {
  if (name.value && phone.value) submitted.value = true
}

const reset = () => {
  name.value = ''
  phone.value = ''
  note.value = ''
  submitted.value = false
}
</script>

<template>
  <section class="bg-charcoal-900 text-white">
    <div
      class="mx-auto grid max-w-[var(--container-max)] gap-10 px-[clamp(20px,4vw,48px)] py-16 lg:grid-cols-2"
    >
      <div class="flex flex-col gap-5">
        <span class="text-eyebrow text-gold-200 font-bold tracking-[0.24em] uppercase">Tư vấn</span>
        <h2 class="text-display-lg m-0 font-bold tracking-[-0.03em]">Chưa biết chọn model nào?</h2>
        <p class="text-heading m-0 leading-relaxed font-light text-white/75">
          Để lại thông tin — kỹ thuật Keybolts sẽ gọi lại và tư vấn theo đúng loại cửa, độ
          dày và nhu cầu sử dụng của bạn.
        </p>
        <div class="flex flex-col gap-1">
          <span class="text-caption tracking-[0.1em] text-white/60 uppercase">Hotline</span>
          <a :href="HOTLINE_TEL" class="text-display text-gold-200 font-bold no-underline">
            {{ HOTLINE }}
          </a>
        </div>
      </div>

      <div class="bg-background p-8 text-text">
        <div v-if="submitted" class="flex flex-col gap-4">
          <span class="text-display font-bold">Cảm ơn bạn!</span>
          <p class="text-body text-text-muted m-0">
            Đội ngũ Keybolts sẽ liên hệ lại trong thời gian sớm nhất.
          </p>
          <button
            type="button"
            class="text-body w-fit cursor-pointer rounded-sm bg-charcoal-900 px-6 py-3 font-bold text-white"
            @click="reset"
          >Gửi yêu cầu khác</button>
        </div>

        <form v-else class="flex flex-col gap-4" @submit.prevent="submit">
          <label class="flex flex-col gap-2">
            <span class="text-caption text-text-muted">Họ tên</span>
            <input
              v-model="name"
              required
              class="text-body rounded-sm border border-border px-4 py-3"
            >
          </label>
          <label class="flex flex-col gap-2">
            <span class="text-caption text-text-muted">Số điện thoại</span>
            <input
              v-model="phone"
              required
              inputmode="tel"
              class="text-body rounded-sm border border-border px-4 py-3"
            >
          </label>
          <label class="flex flex-col gap-2">
            <span class="text-caption text-text-muted">Nhu cầu / loại cửa</span>
            <textarea v-model="note" rows="3" class="text-body rounded-sm border border-border px-4 py-3" />
          </label>
          <button
            type="submit"
            class="text-body cursor-pointer rounded-sm bg-gold-200 px-8 py-4 font-bold tracking-[0.06em] text-charcoal-900 uppercase"
          >Gửi yêu cầu tư vấn</button>
        </form>
      </div>
    </div>
  </section>
</template>
