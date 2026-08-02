# Keybolts Projects and Content Detail Implementation Plan

**Goal:** Build dynamic `/du-an`, `/du-an/{slug}` and `/tin-tuc/{slug}` pages from the current prototypes in `design/`.

**Design authority:** `design/Keybolts Projects.html` governs the project listing and project visual language. `design/Keybolts Article.html` governs article detail. There is no standalone project-detail prototype, so project detail reuses the Projects hero/card vocabulary and the Article two-column detail rhythm without adding copy not present in the project dataset.

**Architecture:** Drupal owns a singleton `projects_page`, twelve `project` nodes, and article-detail fields. Collection/detail JSON endpoints serialize published nodes by slug. Nuxt SSR renders filters, pagination and both detail routes from those APIs.

## Task 1: Plan

- Record design sources, scope, data ownership, routes and acceptance criteria.
- Commit this plan before implementation.

## Task 2: Content model and seed

- Add `projects_page` and `project` content types.
- Store project type/key, description, products, image URL, slug and sort order.
- Add article author, update label, quick answer, sections, comparison rows and FAQs using structured JSON text fields.
- Seed the Projects hero and all twelve project cards verbatim from the final prototype occurrence.
- Seed the complete featured article detail verbatim from `Keybolts Article.html`; leave unsupported detail fields empty on other articles.
- Add editor form displays, run setup/seed twice, export config and run kernel tests before commit.

## Task 3: Dynamic APIs

- Expose `GET /api/v1/page/projects`, `GET /api/v1/projects`, `GET /api/v1/projects/{slug}` and `GET /api/v1/articles/{slug}`.
- Return only published content, preserve editor sort order and return 404 for unknown slugs.
- Include cache tags and add kernel coverage for payload, order and 404 behavior.
- Run the complete kernel suite before commit.

## Task 4: Nuxt listing and detail pages

- Build `/du-an` with the prototype hero, five filters, six-card pagination and range label.
- Link project cards to `/du-an/{slug}` and news cards to `/tin-tuc/{slug}`.
- Build article detail with prototype hero, quick answer, sections, comparison table, FAQ and sticky table of contents when data exists.
- Build project detail from its CMS fields, with breadcrumb, dark hero, image, description and product solution; do not invent case-study facts.
- Restore the primary Dự án navigation link to `/du-an`.
- Add TypeScript/service tests, run Vitest and production build before commit.

## Task 5: Final verification

- Reseed and verify all four public routes and APIs against live Drupal data.
- Verify filter/pagination interaction, detail links, 404s and SSR content.
- Check 375, 768 and 1440px layouts for overflow and visual regressions.
- Run all backend/frontend tests and production build before the final commit.

## Acceptance criteria

- All listing/detail content is loaded from Drupal; no card or detail copy is hard-coded in Vue.
- Editors can update page hero copy, project records and supported article-detail structures.
- The featured article reproduces `Keybolts Article.html`; `/du-an` reproduces `Keybolts Projects.html`.
- Project detail stays faithful to available design/data and introduces no fabricated project facts.
- Unknown or unpublished slugs return 404 and all navigation links resolve.
