# Meowdo — project guide for Claude

Cat-themed, gamified task manager. Tasks (with a planned day and an optional deadline) live in projects or in the inbox; **Today** is a work context that gathers tasks from every project; the **Eisenhower matrix** orders every list; completing tasks earns XP, coins and streaks that feed a cat companion, a cosmetic shop and achievements.
UI languages: **English and French**. URLs, code, commits: **English**.

**Business rules live in [`docs/business/`](docs/business/README.md)** — read the relevant page before touching a domain concept, and update it in the same commit when a rule changes.

Architecture and conventions mirror `~/Sites/MossyTrunk` (read its `CLAUDE.md` when in doubt); deliberate differences are listed at the end of this file.

## Code style

- **Never write comments** — no docblocks, no inline `//`/`#`, no `<!-- -->`, no `{# #}`. Only type-only PHPDoc the type system needs (`@return list<Task>`, `@param iterable<AchievementRule>`) is allowed.
- **SOLID in every layer**: one use case per handler, one route per controller, one exception per file; extend by adding a class (an `AchievementRule`, a controller, an exception subclass), not by growing `switch`/`instanceof` branches; Domain and Application own their ports.

Generic Symfony conventions: see `AGENTS.md` (this file wins when they disagree).

## Stack

- Symfony 8.1, PHP 8.5 (FrankenPHP), Doctrine ORM 3, PostgreSQL 18, ULIDs.
- Vue 3 SPA (vue-router) mounted in one Twig shell, bundled by **Vite** through `pentatrion/vite-bundle` (`vite_entry_script_tags('app')`). Login/password pages are a second entry (`auth`).
- Tests: PHPUnit 13 (unit + functional, DAMA rollback), Vitest (pure JS modules), Playwright (e2e).

## Running things (always in Docker)

```bash
make up              # php + database + node (Vite dev server :5174) + mailpit (:8026) → http://localhost:8090
make db              # create + migrate dev DB
make fixtures        # demo data (fixtures/Story)
make migration       # doctrine:migrations:diff after mapping changes
make test            # PHPUnit; make test-unit / test-functional
make test-js         # Vitest
make deptrac / make cs / make cs-fix / make phpstan   # keep all at 0
make e2e             # Playwright against php-e2e (APP_ENV=test)
docker compose exec php php bin/console app:user:create <email> --name=<name> --cat=<cat name> --coat=<coat> --timezone=Europe/Paris
```

## Backend architecture (Onion) — `src/`

| Layer | Contains | May depend on |
|---|---|---|
| `Domain/` | Entities (rich, **no setters**), value objects, domain services (`RewardPolicy`, `AchievementReferee`), repository **interfaces**, exceptions | nothing |
| `Application/` | Use cases `<Context>/<UseCase>/{Command, Handler}`, views (`TaskView`, `PlayerView`), ports (`TaskQueries`, `PlayerStatsLedger`, `CurrentUser`, `Transaction`, `AccountMailer`) | Domain |
| `Infrastructure/` | Doctrine repositories and read ports, security adapters, mailer, console | Domain, Application |
| `Presentation/` | `Api/` JSON controllers + payload DTOs, `Web/` Twig shells and auth pages | Application, Domain |

Contexts: `Identity`, `Planning`, `Gamification`.

- Doctrine mapping = attributes on Domain entities. Dates without time (`plannedOn`, `dueOn`, streak days) are `date_immutable` built through `Domain\Shared\Day` — never `new \DateTimeImmutable()` for a day.
- **"Today" depends on the user's time zone** (`User::today($now)`, `Application\Planning\Today`): never compare days with the server clock directly.
- Invariants throw `DomainException` subclasses (`<Context>/Exception/<Violation>.php`) → 422 `problem+json` (404 for `NotFound`) through `DomainExceptionListener`, message translated from `translations/exceptions+intl-icu.<locale>.yaml`.
- Handlers are invokable, end with `Transaction::commit()`. No bus, no domain events: `CompleteTaskHandler` orchestrates task → reward → player → `AchievementCheck`.
- **Owner scoping**: every user-owned aggregate (`Task`, `Project`, …) has an `owner`; Doctrine repositories/read ports filter through `Infrastructure\Persistence\Doctrine\OwnerScope` (a new query must too). Another user's id answers 404.
- Achievements: one `AchievementRule` class per achievement (auto-tagged `app.achievement_rule`, listed by `#[AsTaggedItem(priority)]`, highest first), translations for its title/description in `assets/vue/i18n/<locale>/achievements.json`.

## Accounts & security

- **No registration page**: `app:user:create` creates the user, their player profile and cat, and emails an invitation link (Mailpit: http://localhost:8026) to choose a password (`/password/set`). Forgot password: `/password/forgot`. Same flow as MossyTrunk.
- Session firewall with `form_login` (`/login`), sessions in PostgreSQL (`PdoSessionHandler`), remember-me always on (so phones stay signed in), CSRF logout. The JSON API uses the session cookie; `SameOriginGuard` rejects cross-site writes.
- Sync between devices = same account; the server is the source of truth, the SPA refetches on focus and every minute while visible.

## Frontend — `assets/`

- `app.js` mounts the SPA in `templates/app.html.twig` (`#app-session`, `#app-preload` filled by `AppShellController` through `ApiPreload`). A new page that loads data on mount adds its URL to `AppShellController::pageUrls()`.
- `vue/layouts/AppShell.vue` keeps the rail/tab bar and the **CatDesk** mounted across routes. Pages are thin orchestrators.
- Data goes through `composables/useApi.js` (`load` = stale-while-revalidate, writes send `X-Refresh`, see MossyTrunk). Task ordering on the client uses `vue/tasks/compareTasks.js`, which must match `DoctrineTaskQueries::ordered()` (Vitest covers it).
- Interactive widgets on **Reka UI**; icons **Lucide** (`size` in rem); no native select/checkbox/date inputs.
- CSS: BEM, `<style scoped>`, tokens in `assets/styles/tokens.css`, **rem only** (except inside SVG drawings such as `CatSvg.vue`, whose transforms and font sizes are in viewBox user units). Dark theme first ("midnight desk": blue-violet night, warm lamp light). Fonts: Bagel Fat One (display), Atkinson Hyperlegible Next (text), self-hosted via `@fontsource`.
- **Never hardcode a user-visible string**: vue-i18n keys from `assets/vue/i18n/<locale>/<namespace>.json` (same keys in every locale).
- Motion: one bold moment — the cat slapping the bongo on completion. `prefers-reduced-motion` disables it.
- Assets: hand-built SVG only (cat, cosmetics), no AI-generated images; every third-party asset is listed with its licence in `docs/assets.md` and on `/credits`.

## Testing expectations

- PHPStan level 10 on `src`, `tests`, `fixtures`; ignores only in `phpstan.dist.neon`.
- Unit test every business rule; functional test every handler (KernelTestCase, real DB) and endpoint (WebTestCase); Playwright for user journeys.

## Differences from MossyTrunk

- Vite + vue-router SPA instead of Encore + UX Vue + Turbo (the cat companion stays mounted between pages).
- Owner scoping instead of workspaces; every user has their own data.
- Vitest for pure JS modules.

## Git

One commit per feature, conventional messages. No co-author lines.
