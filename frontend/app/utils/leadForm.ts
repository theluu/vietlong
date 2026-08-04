/**
 * Google reports reCAPTCHA stats per action, so each form gets its own name.
 * The server rejects a token whose action does not match the form it arrived on.
 */
export const RECAPTCHA_ACTIONS = {
  contact: 'contact_form',
  dealer: 'dealer_form',
  consult: 'consult_form',
} as const

export interface LeadState {
  name: string
  phone: string
  message: string
}

/** Returns the names of the invalid fields; empty means valid. */
export function validateLead(state: LeadState): string[] {
  const errors: string[] = []
  if (!state.name.trim()) errors.push('name')
  if (!state.phone.trim()) errors.push('phone')
  return errors
}

/**
 * The design prints numbers as 0912.411.309, and people paste them with
 * spaces, dots, dashes and brackets. Send the server digits only.
 */
export function normalisePhone(raw: string): string {
  const trimmed = raw.trim()
  const plus = trimmed.startsWith('+') ? '+' : ''
  return plus + trimmed.replace(/\D/g, '')
}
