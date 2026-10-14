# Project Instructions

## Project Overview

PHP library converting domain names between IDN and Punycode with TR46 processing, built on
`ext-intl`. Node is present only for the release tooling, never for product code.

- **Language / runtime:** PHP `^8.3` (`composer.json`), with `ext-intl`, `ext-mbstring`,
  `ext-json`
- **Package name:** `centralnic-reseller/idn-converter` (Composer), namespace `CNIC\IDNA\`
  in `src/IDNA/`
- **Default branch:** `main`

## Architecture

- **`ConverterFactory::transitionalProcessing()` decides transitional processing from the
  TLD:** off for `.art .be .ca .de .fr .pm .re .swiss .tf .wf .yt`, on otherwise; an explicit
  `transitionalProcessing` option overrides it.
- **Transitional mapping is applied explicitly** (`applyTransitionalMapping`), never
  delegated to ICU — ICU no longer distinguishes the modes, and this keeps results stable
  across ICU versions.
- **Never re-convert an already-decoded label** in `toUnicode` — re-applying transitional
  mapping corrupts it (`faß` becomes `fass`).
- **`ext-mbstring` needs mbregex** (`mb_ereg`/`mb_eregi`); Composer cannot express that.

## Coding Standards

- **Style:** PSR-12 plus Slevomat rules (config: `.github/linters/phpcs.xml`) — strict types
  declaration, typed properties, parameter and return type hints, alphabetically sorted uses.
- **Static analysis:** PHPStan at level 9 (`.github/linters/phpstan.neon`) and Psalm at
  `errorLevel="1"` with `findUnusedCode` (`.github/linters/psalm.xml`), both over `src` and
  `tests`.
- **Rector:** `CODE_QUALITY`, `DEAD_CODE`, `PHP_83` sets (`.github/linters/rector.php`); CI
  runs the dry-run.
- Use `declare(strict_types=1);` and typed signatures with declared return types on all new
  code.

### Naming

- Classes: PascalCase
- Methods / functions: camelCase
- Constants: UPPER_SNAKE_CASE

## Testing

- **Framework:** PHPUnit 11, config `phpunit.xml.dist` (`stopOnFailure`).
- **The suite only picks up files ending in `IDNATranslatorTest.php`** under `tests/` — a
  test file named otherwise never runs.

### Running tests

```sh
composer test         # phpunit --no-coverage
composer lint         # phpcs + phpstan + psalm
composer rector       # dry-run; composer rector:fix applies
composer coverage     # HTML report in reports/html
```

## Build, CI & Policies

- **CI:** `static-analysis.yml` runs `composer lint`, `composer rector`, `composer test` and
  `composer coverage` on PHP 8.3; `test.yml` and `release.yml` delegate to
  [rtldev-middleware-shareable-workflows](https://github.com/centralnicgroup-opensource/rtldev-middleware-shareable-workflows)
  (`php-idna-translator-test.yml`, `php-idna-translator-release.yml`). Do not reimplement
  the shared workflows here.
- **Distribution archive:** CI fails unless `git archive` contains exactly `LICENSE`,
  `README.md`, `composer.json` and `src/` — a new top-level path needs `export-ignore` in
  `.gitattributes`.
- **Releases:** semantic-release (`.releaserc.json`) writes `HISTORY.md` and commits it,
  `composer.json`, `src`, `docs` and `reports` back to `main`.
- **Lockfiles:** `pnpm-lock.yaml` is committed deliberately; `composer.lock` and
  `package-lock.json` are git-ignored.
- **Supply chain:** `minimumReleaseAge: 1440` in `pnpm-workspace.yaml` is enforced
  workspace-wide — wait out a red build rather than lowering it.
- **Devcontainer:** the frame is in `.devcontainer/`; the shared behaviour comes from the
  `devbase` devcontainer Feature (`devbase:2`) and PHP 8.3 from the `php` Feature. Never fork
  devbase's scripts here. `apply-php-config.sh` must stay on `onCreateCommand` — it loads
  `ext-intl` before devbase runs `composer install`.
- **Runtime version policy:** PHP `^8.3` in `composer.json`; Node `engines`
  `^24.15.0 || ^26.0.0` and `devEngines.packageManager` pnpm `^11.0.0` in `package.json`
  cover the release tooling.

## Git Conventions

- **Commit messages:** Conventional Commits with **mandatory scope** —
  `<type>(<scope>): <summary>`. Never append a `Co-Authored-By:` trailer.
- **Commit type selection:** `fix`/`feat` are reserved for source changes — they trigger a
  release. Everything else uses a non-releasing type: `ci` (workflows, devcontainer),
  `build` (build tooling), `chore`, `docs`, `test`, `refactor`.
- **Breaking changes:** add `BREAKING CHANGE: <summary>` to the commit body after a blank
  line, and document the consumer upgrade path in the same change.
- **Branch creation:** before creating a branch, and again after any PR merges,
  run `git checkout main && git pull --ff-only` first — it brings in the merged
  commits and their release tags. Never branch from a stale local `main`, from
  `origin/main` (it becomes the new branch's upstream) or from another feature
  branch.
- **Branch naming:** prefix with the Jira issue ID — `RSRMID-1234-short-description`.
- **Pull requests:** include the Jira issue link in the description; after opening, add the
  PR URL as a comment on the Jira issue.
- **Merging:** rebase-merge (`gh pr merge --rebase`). Squash merges are disabled at the
  repository level.

## Important Paths

| Path                                                         | Purpose                                      |
| ------------------------------------------------------------ | -------------------------------------------- |
| `.github/linters/`                                           | PHPCS, PHPStan, Psalm and Rector configs     |
| `phpunit.xml.dist`                                           | Test runner configuration                    |
| `.devcontainer/supporting_files/scripts/apply-php-config.sh` | Copies `intl.ini` / `zz-custom.ini` into PHP |

## Atlassian / JIRA

Work is tracked in **Jira Cloud**, project `RSRMID` — not GitHub Issues. Load the
`jira-conventions` skill before creating, editing or transitioning an issue: it carries the
required fields, this repository's title prefix and component, and the worklog rule for Done and Cancelled.

## Do NOT

- Add dependencies without explicit request
- Add `@author` tags to docblocks
- Add `Co-Authored-By:` trailers to commit messages
- Fork the `devbase` devcontainer Feature's scripts into this repository
- Hand-edit `HISTORY.md`, `docs/` or `reports/` — generated (semantic-release, `composer docs`,
  PHPUnit coverage/logging) and committed by the release
