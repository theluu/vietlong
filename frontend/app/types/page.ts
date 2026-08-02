export interface Branch {
  id: number
  name: string
  tag: string
  address: string
  phoneDisplay: string
  phoneTel: string
  mapUrl: string
}

export interface CtaLink { label: string; url: string }
export interface Fact { number: string; label: string }
export interface NumberedItem { number: string; title: string; desc: string }
export interface Segment { title: string; desc: string; ctaLabel?: string; ctaUrl?: string; image?: string }
export interface ValueItem { title: string; desc: string }

export interface AboutPage {
  eyebrow: string
  title: string
  subtitle: string
  heroImage: string
  heroCaption: string
  ctaPrimary: CtaLink
  ctaSecondary: CtaLink
  facts: Fact[]
  storyEyebrow: string
  storyTitle: string
  storyBody: string
  credentials: string[]
  segments: Segment[]
  steps: NumberedItem[]
  values: ValueItem[]
}

export interface DealersPage {
  eyebrow: string
  title: string
  subtitle: string
  benefits: NumberedItem[]
  criteria: string[]
  formTitle: string
  formDesc: string
  successTitle: string
  successDesc: string
}

export interface ContactChannel {
  label: string
  value: string
  note: string
  ctaLabel?: string
  ctaUrl?: string
}

export interface ContactPage {
  eyebrow: string
  title: string
  subtitle: string
  channels: ContactChannel[]
  companyName: string
  companyAddress: string
  responseTitle: string
  responseBody: string
  formTitle: string
  formDesc: string
  successTitle: string
  successDesc: string
}

export interface PolicyItem { k: string; v: string }
export interface PolicySection {
  key: string
  label: string
  eyebrow: string
  title: string
  intro: string
  note: string
  items: PolicyItem[]
}
export interface PoliciesPage {
  eyebrow: string
  title: string
  subtitle: string
  sections: PolicySection[]
  supportTitle: string
  supportNote: string
}
