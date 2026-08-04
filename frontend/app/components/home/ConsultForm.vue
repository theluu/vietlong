<script setup lang="ts">
import { submitLead } from '~/services/pages'
import { normalisePhone, RECAPTCHA_ACTIONS, validateLead } from '~/utils/leadForm'

const { execute } = useRecaptcha()

const name = ref('')
const phone = ref('')
const note = ref('')
const submitted = ref(false)
const sending = ref(false)
const failed = ref(false)
const blocked = ref(false)
const throttled = ref(false)

const submit = async () => {
  if (validateLead({ name: name.value, phone: phone.value, message: note.value }).length) return
  sending.value = true
  failed.value = false
  blocked.value = false
  throttled.value = false
  try {
    const token = await execute(RECAPTCHA_ACTIONS.consult)
    await submitLead({
      name: name.value.trim(),
      phone: normalisePhone(phone.value),
      message: note.value.trim(),
      source: 'consult',
      recaptchaToken: token ?? undefined,
      recaptchaAction: RECAPTCHA_ACTIONS.consult,
    })
    submitted.value = true
  }
  catch (error) {
    const codes = (error as { data?: { errors?: string[] } })?.data?.errors ?? []
    blocked.value = codes.includes('recaptcha')
    throttled.value = codes.includes('flood')
    failed.value = !blocked.value && !throttled.value
  }
  finally {
    sending.value = false
  }
}

const reset = () => {
  name.value = ''
  phone.value = ''
  note.value = ''
  submitted.value = false
  failed.value = false
  blocked.value = false
  throttled.value = false
}
</script>

<template>
  <section id="consultation" class="border-t border-border bg-surface py-[clamp(60px,7vw,104px)]">
    <div class="mx-auto max-w-[var(--container-max)] px-[clamp(20px,4vw,48px)]">
    <div class="grid overflow-hidden border border-border bg-background shadow-floating lg:grid-cols-2">
      <div class="relative flex flex-col justify-center gap-[18px] overflow-hidden bg-charcoal-900 p-[clamp(34px,4vw,56px)] text-white">
        <div class="pointer-events-none absolute -top-[30%] -right-[20%] h-[160%] w-[70%] bg-[radial-gradient(circle,rgba(198,145,72,.3),transparent_62%)]" />
        <div class="relative flex items-center gap-[14px]"><span class="h-px w-[34px] bg-gold-200"/><span class="text-eyebrow text-gold-200 font-bold tracking-[0.24em] uppercase">Tư vấn</span></div>
        <h2 class="m-0 text-[clamp(var(--text-display),3vw,var(--text-display-lg))] leading-[1.12] font-bold tracking-[-0.03em]">Chưa biết chọn model nào?</h2>
        <p class="text-heading m-0 leading-[1.75] font-light text-white/74">
          Để lại thông tin — kỹ thuật Keybolts sẽ gọi lại và tư vấn theo đúng loại cửa, độ
          dày và nhu cầu sử dụng của bạn.
        </p>
        <div class="relative mt-4 border-t border-white/15 pt-[26px]">
          <a :href="HOTLINE_TEL" class="flex items-center gap-4 text-white no-underline">
            <span class="grid size-[46px] flex-none place-items-center border border-gold-200/40 text-gold-200"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg></span><span class="flex flex-col gap-0.5"><span class="text-eyebrow tracking-[0.14em] text-white/60 uppercase">Hotline</span><strong class="text-display leading-none">{{ HOTLINE }}</strong></span>
          </a>
        </div>
      </div>

      <div class="bg-background p-[clamp(34px,4vw,56px)] text-text">
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

        <form v-else class="flex flex-col gap-5" @submit.prevent="submit">
          <label class="flex flex-col gap-[6px]">
            <span class="text-body text-text">Họ tên</span>
            <input
              v-model="name"
              required
              placeholder="Nguyễn Văn A"
              class="text-body h-[48px] border border-border px-[15px] outline-none focus:border-brass-500"
            >
          </label>
          <label class="flex flex-col gap-[6px]">
            <span class="text-body text-text">Số điện thoại</span>
            <input
              v-model="phone"
              required
              inputmode="tel"
              placeholder="09xx xxx xxx"
              class="text-body h-[48px] border border-border px-[15px] outline-none focus:border-brass-500"
            >
          </label>
          <label class="flex flex-col gap-[6px]">
            <span class="text-body text-text">Nhu cầu / loại cửa</span>
            <textarea v-model="note" rows="3" placeholder="Ví dụ: cửa gỗ 45mm, cần khóa vân tay cho căn hộ" class="text-body border border-border px-[15px] py-[9px] outline-none focus:border-brass-500" />
          </label>
          <button
            type="submit"
            :disabled="sending"
            class="text-body mt-2 flex cursor-pointer items-center justify-center gap-[11px] bg-charcoal-900 px-8 py-[19px] font-bold tracking-[0.07em] text-gold-200 uppercase hover:bg-brass-700 disabled:opacity-60"
          >{{ sending ? 'Đang gửi…' : 'Gửi yêu cầu tư vấn' }}<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" aria-hidden="true"><path d="M5 12h14"/><path d="m13 6 6 6-6 6"/></svg></button>
          <p v-if="blocked" class="text-caption text-danger m-0">Không xác thực được yêu cầu. Vui lòng tải lại trang hoặc gọi {{ HOTLINE }}.</p>
          <p v-if="throttled" class="text-caption text-danger m-0">Đã gửi quá nhiều lần từ địa chỉ này. Vui lòng thử lại sau ít phút hoặc gọi {{ HOTLINE }}.</p>
          <p v-if="failed" class="text-caption text-danger m-0">Không gửi được. Vui lòng gọi {{ HOTLINE }}.</p>
        </form>
      </div>
    </div>
    </div>
  </section>
</template>
