<script setup lang="ts">
import { submitLead, type LeadPayload } from '~/services/pages'
import { normalisePhone, validateLead } from '~/utils/leadForm'

const props = defineProps<{
  source: LeadPayload['source']
  title: string
  desc: string
  successTitle: string
  successDesc: string
}>()

const state = reactive({ name: '', phone: '', message: '' })
const website = ref('')          // honeypot
const errors = ref<string[]>([])
const sending = ref(false)
const sent = ref(false)
const failed = ref(false)

const invalid = (field: string) => errors.value.includes(field)

async function submit() {
  errors.value = validateLead(state)
  if (errors.value.length) return
  sending.value = true
  failed.value = false
  try {
    await submitLead({
      name: state.name.trim(),
      phone: normalisePhone(state.phone),
      message: state.message.trim(),
      source: props.source,
      website: website.value,
    })
    sent.value = true
  }
  catch {
    failed.value = true
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
}
</script>

<template>
  <div class="bg-background p-8 text-text">
    <div v-if="sent" class="flex flex-col gap-4">
      <span class="text-display font-bold">{{ successTitle }}</span>
      <p class="text-body text-text-muted m-0">{{ successDesc }}</p>
      <button
        type="button"
        class="text-body w-fit cursor-pointer rounded-sm bg-charcoal-900 px-6 py-3 font-bold text-white"
        @click="reset"
      >Gửi yêu cầu khác</button>
    </div>

    <form v-else class="flex flex-col gap-4" novalidate @submit.prevent="submit">
      <div class="flex flex-col gap-1">
        <span class="text-heading font-bold">{{ title }}</span>
        <p class="text-caption text-text-muted m-0 leading-relaxed">{{ desc }}</p>
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

      <label class="flex flex-col gap-2">
        <span class="text-caption text-text-muted">Họ tên</span>
        <input
          v-model="state.name"
          class="text-body rounded-sm border px-4 py-3"
          :class="invalid('name') ? 'border-danger' : 'border-border'"
        >
      </label>

      <label class="flex flex-col gap-2">
        <span class="text-caption text-text-muted">Số điện thoại</span>
        <input
          v-model="state.phone"
          inputmode="tel"
          class="text-body rounded-sm border px-4 py-3"
          :class="invalid('phone') ? 'border-danger' : 'border-border'"
        >
      </label>

      <label class="flex flex-col gap-2">
        <span class="text-caption text-text-muted">Nội dung</span>
        <textarea v-model="state.message" rows="3" class="text-body rounded-sm border border-border px-4 py-3" />
      </label>

      <p v-if="errors.length" class="text-caption text-danger m-0">
        Vui lòng nhập họ tên và số điện thoại.
      </p>
      <p v-if="failed" class="text-caption text-danger m-0">
        Không gửi được. Vui lòng gọi {{ HOTLINE }}.
      </p>

      <button
        type="submit"
        :disabled="sending"
        class="text-body cursor-pointer rounded-sm bg-gold-200 px-8 py-4 font-bold tracking-[0.06em] text-charcoal-900 uppercase disabled:opacity-60"
      >{{ sending ? 'Đang gửi…' : 'Gửi yêu cầu' }}</button>
    </form>
  </div>
</template>
