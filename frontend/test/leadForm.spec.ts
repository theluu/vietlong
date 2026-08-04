import { describe, expect, it } from 'vitest'
import { normalisePhone, RECAPTCHA_ACTIONS, validateLead } from '../app/utils/leadForm'

describe('validateLead', () => {
  it('requires name and phone', () => {
    expect(validateLead({ name: '', phone: '', message: '' })).toEqual(['name', 'phone'])
  })

  it('treats whitespace as empty', () => {
    expect(validateLead({ name: '   ', phone: '\t', message: '' })).toEqual(['name', 'phone'])
  })

  it('passes when both are present', () => {
    expect(validateLead({ name: 'Nguyễn Văn A', phone: '0912411309', message: '' })).toEqual([])
  })
})

describe('normalisePhone', () => {
  it('strips the separators the design displays', () => {
    expect(normalisePhone('0912.411.309')).toBe('0912411309')
    expect(normalisePhone('0968 689 112')).toBe('0968689112')
    expect(normalisePhone('(0981) 255-215')).toBe('0981255215')
  })

  it('keeps a leading + for international numbers', () => {
    expect(normalisePhone('+84 912 411 309')).toBe('+84912411309')
  })
})

describe('RECAPTCHA_ACTIONS', () => {
  it('gives every lead source its own action name', () => {
    const actions = Object.values(RECAPTCHA_ACTIONS)
    expect(actions).toHaveLength(3)
    expect(new Set(actions).size).toBe(3)
  })

  it('names actions after the form, which is what Google reports on', () => {
    expect(RECAPTCHA_ACTIONS.contact).toBe('contact_form')
    expect(RECAPTCHA_ACTIONS.dealer).toBe('dealer_form')
    expect(RECAPTCHA_ACTIONS.consult).toBe('consult_form')
  })
})
