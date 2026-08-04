<script setup lang="ts">
/**
 * Google's branding rules require either their floating badge or this
 * attribution. The badge only appears once api.js has loaded, and the script
 * is deliberately deferred to first submit, so the page would otherwise carry
 * neither. Rendered only when a site key is configured — with none, no
 * reCAPTCHA call is ever made and the claim would be false.
 */
const { recaptcha } = useSite()
const configured = computed(() =>
  recaptcha.value.enabled && !!(recaptcha.value.siteKey || useRuntimeConfig().public.recaptchaSiteKey))
</script>

<template>
  <p v-if="configured" class="text-caption text-text-muted m-0 leading-[1.7]">
    Trang này được bảo vệ bởi reCAPTCHA — áp dụng
    <a href="https://policies.google.com/privacy" target="_blank" rel="noopener" class="text-brass-700 underline">Chính sách bảo mật</a>
    và
    <a href="https://policies.google.com/terms" target="_blank" rel="noopener" class="text-brass-700 underline">Điều khoản dịch vụ</a>
    của Google.
  </p>
</template>
