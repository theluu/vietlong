<script setup lang="ts">
import { submitLead, type LeadPayload } from '~/services/pages'
import { normalisePhone, RECAPTCHA_ACTIONS, validateLead } from '~/utils/leadForm'

const props = withDefaults(defineProps<{
  source: LeadPayload['source']
  title: string
  desc: string
  successTitle: string
  successDesc: string
  submitLabel?: string
}>(), { submitLabel: 'Gửi yêu cầu' })

const { execute } = useRecaptcha()

const state = reactive({ name: '', phone: '', message: '' })
const website = ref('')          // honeypot
const errors = ref<string[]>([])
const sending = ref(false)
const sent = ref(false)
const failed = ref(false)
const blocked = ref(false)

const invalid = (field: string) => errors.value.includes(field)

async function submit() {
  errors.value = validateLead(state)
  if (errors.value.length) return
  sending.value = true
  failed.value = false
  blocked.value = false
  try {
    const action = RECAPTCHA_ACTIONS[props.source]
    const token = await execute(action)
    await submitLead({
      name: state.name.trim(),
      phone: normalisePhone(state.phone),
      message: state.message.trim(),
      source: props.source,
      website: website.value,
      recaptchaToken: token ?? undefined,
      recaptchaAction: action,
    })
    sent.value = true
  }
  catch (error) {
    // 422 with errors:["recaptcha"] means Google scored the visitor as a bot.
    const codes = (error as { data?: { errors?: string[] } })?.data?.errors ?? []
    blocked.value = codes.includes('recaptcha')
    failed.value = !blocked.value
  }
  finally {
    sending.value = false
  }
}

function reset() {
  state.name = ''
  state.phone = ''
  state.message = ''
  errors.value = []
  sent.value = false
  failed.value = false
  blocked.value = false
}

const fieldClass = (field: string) => [
  'text-body bg-background text-text px-[16px] py-[14px] border outline-none transition-colors duration-200 ease-in-out focus:border-brass-500',
  invalid(field) ? 'border-danger' : 'border-border',
]
</script>

<template>
  <div v-if="sent" class="flex h-full flex-col justify-center gap-[14px]">
    <span class="text-display font-bold">{{ successTitle }}</span>
    <p class="text-body text-text-muted m-0 leading-[1.8]">{{ successDesc }}</p>
    <button
      type="button"
      class="border-border text-caption hover:bg-background w-fit cursor-pointer rounded-sm border bg-transparent px-[24px] py-[12px] tracking-[0.08em] uppercase transition-colors"
      @click="reset"
    >Gửi yêu cầu khác</button>
  </div>

  <form v-else class="flex flex-col gap-[18px]" novalidate @submit.prevent="submit">
    <div class="mb-[2px] flex flex-col gap-[6px]">
      <span class="text-heading font-bold">{{ title }}</span>
      <span class="text-caption text-text-muted leading-[1.7]">{{ desc }}</span>
    </div>

    <!-- Honeypot: off-screen, not display:none, so bots that skip hidden
         fields still fill it. Never shown to real users. -->
    <input
      v-model="website"
      type="text"
      name="website"
      tabindex="-1"
      autocomplete="off"
      aria-hidden="true"
      class="absolute -left-[9999px] h-0 w-0 opacity-0"
    >

    <label class="flex flex-col gap-[8px]">
      <span class="text-text-muted text-[10px] font-bold tracking-[0.16em] uppercase">Họ tên</span>
      <input v-model="state.name" type="text" placeholder="Nguyễn Văn A" :class="fieldClass('name')">
    </label>

    <label class="flex flex-col gap-[8px]">
      <span class="text-text-muted text-[10px] font-bold tracking-[0.16em] uppercase">Số điện thoại</span>
      <input v-model="state.phone" type="tel" inputmode="tel" placeholder="09xx xxx xxx" :class="fieldClass('phone')">
    </label>

    <label class="flex flex-col gap-[8px]">
      <span class="text-text-muted text-[10px] font-bold tracking-[0.16em] uppercase">Nội dung</span>
      <textarea v-model="state.message" rows="4" placeholder="Nhu cầu, loại cửa hoặc khu vực của bạn" :class="[fieldClass('message'), 'resize-y']" />
    </label>

    <p v-if="errors.length" class="text-caption text-danger m-0">
      Vui lòng nhập họ tên và số điện thoại.
    </p>
    <p v-if="blocked" class="text-caption text-danger m-0">
      Không xác thực được yêu cầu. Vui lòng tải lại trang hoặc gọi {{ HOTLINE }}.
    </p>
    <p v-if="failed" class="text-caption text-danger m-0">
      Không gửi được. Vui lòng gọi {{ HOTLINE }}.
    </p>

    <button
      type="submit"
      :disabled="sending"
      class="text-body bg-charcoal-900 text-gold-200 hover:bg-neutral-700 flex cursor-pointer items-center justify-center gap-[10px] rounded-sm border-none py-[17px] font-bold tracking-[0.08em] uppercase transition-colors disabled:opacity-60"
    >
      {{ sending ? 'Đang gửi…' : submitLabel }}
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/><path d="m13 6 6 6-6 6"/></svg>
    </button>
  </form>
</template>
