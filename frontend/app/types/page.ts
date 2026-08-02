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
