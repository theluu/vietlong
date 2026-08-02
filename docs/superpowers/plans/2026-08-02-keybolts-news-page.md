# Keybolts News Page Implementation Plan

**Goal:** Build `/tin-tuc` from `design/Keybolts News.html`, with all page copy and the 12 article cards editable in Drupal.

**Scope:** News listing only. `design/Keybolts Article.html` is newer and will govern article-detail work separately; this plan does not create detail routes.

**Architecture:** A singleton `news_page` node stores hero copy. Multiple `article` nodes store category, summary, reading time, image URL, slug and sort order. Drupal exposes `GET /api/v1/page/news` and `GET /api/v1/articles`; Nuxt performs category filtering and six-item pagination from the SSR payload.

## Task 1: Content model and seed

- Extend `scripts/setup/install_page_model.php` with `news_page` and `article`.
- Fields: `field_eyebrow`, `field_subtitle` on `news_page`; `field_article_category`, `field_article_category_key`, `field_article_summary`, `field_article_read_time`, `field_article_image_url`, `field_article_slug`, `field_sort_order` on `article`.
- Seed the singleton and all 12 cards verbatim from the last `ARTICLES` occurrence in `design/Keybolts News.html`.
- Add form displays/tabs, run scripts twice, export config, run kernel tests, commit.

## Task 2: Dynamic API

- Add `PageSerializer::news()`.
- Add `ArticleSerializer::all()` ordered by `field_sort_order`, returning `id`, `slug`, `categoryKey`, `category`, `title`, `summary`, `readTime`, `image`.
- Register `news` in the singleton page map and expose `GET /api/v1/articles` with `node_list:article` cache metadata.
- Add a kernel ordering/payload test, run it and the complete kernel suite, commit.

## Task 3: Nuxt SSR page

- Add TypeScript payload types and services.
- Create `PageNewsCard`, filter controls and pagination matching the prototype.
- Create `/tin-tuc` with the shared site chrome, breadcrumb, hero copy from CMS, category filters `Tất cả · Chọn khóa · So sánh · Hướng dẫn · FAQ`, six cards per page, and the prototype range label.
- Restore the primary navigation and homepage news CTA from temporary `/#tin-tuc` links to `/tin-tuc`.
- Run Vitest and production build, verify rendered content, commit.

## Task 4: Final verification

- Confirm `/tin-tuc` returns 200 and contains CMS content.
- Confirm API returns 12 ordered articles and filtering/pagination work.
- Check no horizontal overflow at 375, 768 and 1440px.
- Audit header/footer links, run all backend/frontend tests and production build, commit any verification fixes.

## Acceptance criteria

- `/tin-tuc` is SSR-rendered from Drupal with no article-card content hard-coded in Vue.
- Editors can change hero copy, article metadata, ordering and images in Drupal.
- Four filters and pagination reproduce the prototype behavior.
- Empty/missing data degrades safely; unpublished articles do not appear.
- No broken `/tin-tuc` navigation link, horizontal overflow or external font request.
