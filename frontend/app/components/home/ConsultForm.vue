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
  <section class="bg-surface px-[clamp(20px,4vw,48px)] py-20">
    <div class="mx-auto grid max-w-[1120px] overflow-hidden bg-background shadow-floating lg:grid-cols-[0.9fr_1.1fr]">
      <div class="relative flex flex-col gap-5 overflow-hidden bg-charcoal-900 p-10 text-white md:p-14">
        <div class="pointer-events-none absolute -right-32 -bottom-44 size-[430px] rounded-full bg-[radial-gradient(circle,rgba(195,155,82,.38),transparent_66%)]" />
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

      <div class="bg-background p-10 text-text md:p-14">
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
            <span class="text-caption font-bold text-text-muted">Họ tên</span>
            <input
              v-model="name"
              required
              placeholder="Nguyễn Văn An"
              class="text-body border border-border px-4 py-3.5 outline-none focus:border-brass-500"
            >
          </label>
          <label class="flex flex-col gap-2">
            <span class="text-caption font-bold text-text-muted">Số điện thoại</span>
            <input
              v-model="phone"
              required
              inputmode="tel"
              placeholder="09xx xxx xxx"
              class="text-body border border-border px-4 py-3.5 outline-none focus:border-brass-500"
            >
          </label>
          <label class="flex flex-col gap-2">
            <span class="text-caption font-bold text-text-muted">Nhu cầu / loại cửa</span>
            <textarea v-model="note" rows="3" placeholder="Ví dụ: khóa cho cửa gỗ biệt thự..." class="text-body border border-border px-4 py-3 outline-none focus:border-brass-500" />
          </label>
          <button
            type="submit"
            class="text-body cursor-pointer bg-charcoal-900 px-8 py-4 font-bold tracking-[0.06em] text-white uppercase"
          >Gửi yêu cầu tư vấn</button>
        </form>
      </div>
    </div>
  </section>
</template>
