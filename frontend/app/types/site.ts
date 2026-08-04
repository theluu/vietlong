export interface MenuItem {
  label: string
  to: string
  children?: { label: string; to: string }[]
}

export interface SiteLink { label: string; url: string }

export interface SiteSettings {
  menu: MenuItem[]
  topbar: { text: string; badges: string[] }
  header: { tagline: string; cta: SiteLink | null }
  contact: {
    hotline: string
    hotlineTel: string
    email: string
    companyName: string
    companyShort: string
    address: string
    workingHours: string[]
  }
  footer: {
    description: string
    copyright: string
    columns: { title: string; links: { label: string; to: string }[] }[]
  }
  social: { label: string; icon: string; url: string }[]
  seo: { title: string; description: string }
  recaptcha: { enabled: boolean; siteKey: string }
}
