import { apiFetch } from './http'
import type { AboutPage, ArticleDetail, Branch, ContactPage, DealersPage, NewsArticle, NewsPage, PoliciesPage, Project, ProjectsPage } from '~/types/page'

export const fetchBranches = () => apiFetch<Branch[]>('/branches')
export const fetchPage = <T>(key: string) => apiFetch<T>(`/page/${key}`)
export const fetchAbout = () => fetchPage<AboutPage>('about')
export const fetchDealers = () => fetchPage<DealersPage>('dealers')
export const fetchContact = () => fetchPage<ContactPage>('contact')
export const fetchPolicies = () => fetchPage<PoliciesPage>('policies')
export const fetchNews = () => fetchPage<NewsPage>('news')
export const fetchArticles = () => apiFetch<NewsArticle[]>('/articles')
export const fetchArticle = (slug: string) => apiFetch<ArticleDetail>(`/articles/${encodeURIComponent(slug)}`)
export const fetchProjectsPage = () => fetchPage<ProjectsPage>('projects')
export const fetchProjects = () => apiFetch<Project[]>('/projects')
export const fetchProject = (slug: string) => apiFetch<Project>(`/projects/${encodeURIComponent(slug)}`)

export interface LeadPayload {
  name: string
  phone: string
  message: string
  source: 'contact' | 'dealer' | 'consult'
  email?: string
  /** Honeypot — must stay empty for real users. */
  website?: string
  /** Absent when reCAPTCHA is unconfigured or its script failed to load. */
  recaptchaToken?: string
  recaptchaAction?: string
}

export async function submitLead(payload: LeadPayload): Promise<void> {
  const base = useRuntimeConfig().public.apiBase
  await $fetch(`${base}/contact`, { method: 'POST', body: payload })
}
