interface ReCaptcha {
  ready: (cb: () => void) => void
  execute: (siteKey: string, options: { action: string }) => Promise<string>
}

let scriptPromise: Promise<void> | null = null

/**
 * reCAPTCHA v3 without a Nuxt module.
 *
 * v3 has no widget — the only thing a visitor ever sees is Google's floating
 * badge, and that appears only once api.js has loaded. So pages carrying a
 * form call `preload()` on mount: without it the page shows no sign that it
 * is protected, which both looks broken and leaves the badge missing.
 *
 * Every failure path resolves to `null` instead of throwing: a visitor must
 * never be blocked from sending a lead because Google's script did not load.
 * The server treats a missing token as "unverified" and lets it through.
 */
export function useRecaptcha() {
  // From the API first so an administrator can rotate the key in /admin and
  // have it live on the next request; the build-time env var stays as a
  // fallback for environments provisioned before the admin form existed.
  const { recaptcha } = useSite()
  const siteKey = computed(() => {
    if (recaptcha.value.enabled === false) return ''
    return recaptcha.value.siteKey || String(useRuntimeConfig().public.recaptchaSiteKey || '')
  })

  function loadScript(): Promise<void> {
    if (scriptPromise) return scriptPromise
    scriptPromise = new Promise<void>((resolve, reject) => {
      const script = document.createElement('script')
      script.src = `https://www.google.com/recaptcha/api.js?render=${encodeURIComponent(siteKey.value)}`
      script.async = true
      script.onload = () => resolve()
      script.onerror = () => reject(new Error('reCAPTCHA script failed to load'))
      document.head.appendChild(script)
    }).catch((error) => {
      scriptPromise = null // let the next submit retry
      throw error
    })
    return scriptPromise
  }

  async function execute(action: string): Promise<string | null> {
    if (!siteKey.value || import.meta.server) return null
    try {
      await loadScript()
      const grecaptcha = (window as unknown as { grecaptcha?: ReCaptcha }).grecaptcha
      if (!grecaptcha) return null
      return await new Promise<string | null>((resolve) => {
        grecaptcha.ready(() => {
          grecaptcha.execute(siteKey.value, { action })
            .then(resolve)
            .catch(() => resolve(null))
        })
      })
    }
    catch {
      return null
    }
  }

  /** Loads the script so Google's badge appears. Safe to call repeatedly. */
  function preload(): void {
    if (!siteKey.value || import.meta.server) return
    loadScript().catch(() => {})
  }

  return { execute, preload }
}
