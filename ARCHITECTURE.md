# forms_register — Architecture (Elgg 6.x)

## Plugin summary

`forms_register` enhances Elgg's built-in registration form with:
- Optional first/last name fields (stored as user metadata)
- Username and display-name auto-generation from email or name
- Password auto-generation or minimum-strength enforcement (via zxcvbn)
- Hide-password-repeat mode
- AJAX validation endpoints for username availability and validity

No custom entity types. No custom database tables. Plugin modifies the
standard `register` action flow via Elgg's unified event system (6.x).

## Directory structure

```
forms_register/
├── actions/
│   └── validation/
│       ├── availableusername.php  — check if username is already taken
│       └── validusername.php      — check if username meets Elgg rules
├── classes/
│   └── FormsRegister/
│       ├── Bootstrap.php          — DefaultPluginBootstrap: extends views when forms_validation is active
│       └── Events.php             — prepareActionValues, registerUser, generateUsername
├── views/default/
│   ├── elements/forms/validation/
│   │   ├── password.php           — injects Parsley minstrength validator (AMD require)
│   │   ├── register.php           — injects Parsley init for register form (AMD require)
│   │   └── username.php           — injects Parsley validusername/availableusername validators (AMD require)
│   ├── forms/
│   │   └── register.php           — overrides Elgg's core register form
│   └── plugins/forms_register/
│       └── settings.php           — admin settings page
├── docker/
│   ├── docker-compose.yml         — per-plugin Elgg 4.x test stack
│   └── elgg5/                     — per-plugin Elgg 5.x test stack (PHP 8.2, MySQL 8.0)
├── tests/
│   ├── phpunit/integration/FormsRegister/
│   │   ├── EventsTest.php
│   │   └── ValidationActionsTest.php
│   └── playwright/
│       └── tests/register-form.spec.ts
├── composer.json
└── elgg-plugin.php
```

## Registered events (elgg-plugin.php)

| Event | Type | Handler | Priority |
|-------|------|---------|----------|
| `action` | `register` | `Events::prepareActionValues` | 1 |
| `register` | `user` | `Events::registerUser` | 1 |

## Actions

| Action | Access | Description |
|--------|--------|-------------|
| `validation/validusername` | public | Validates username format/length; returns 200 OK or 422 error |
| `validation/availableusername` | public | Checks username uniqueness; returns 200 OK or 422 error |

## View overrides / extensions

| View | Purpose |
|------|---------|
| `forms/register` | Replaces Elgg's default register form with extended fields |
| `input/text` ← `elements/forms/validation/username` | Injects username validator (when forms_validation active) |
| `input/password` ← `elements/forms/validation/password` | Injects password strength validator (when forms_validation active) |
| `forms/register` ← `elements/forms/validation/register` | Injects Parsley form init (when forms_validation active) |
| `theme_sandbox/forms/forms_register` | Dev theme sandbox demo only |

## Dependencies

| Dependency | Type | Required |
|-----------|------|---------|
| `bjeavons/zxcvbn-php` | PHP — password strength scoring | Yes (runtime) |
| `bower-asset/zxcvbn` | JS — client-side password strength | Yes (runtime) |
| `hypejunction/forms_validation` | Elgg plugin — Parsley.js integration | Optional |

## Plugin settings (admin)

| Setting | Values | Effect |
|---------|--------|--------|
| `min_password_strength` | 0–4 | Minimum zxcvbn score to accept password |
| `first_last_name` | 0/1 | Show first + last name fields, save to user metadata |
| `autogen_name` | 0/1 | Set display name from email username |
| `autogen_username` | 0/1 | Auto-generate username (hidden from form) |
| `autogen_username_algo` | first_name_only/full_name/email/alnum | Username generation strategy |
| `autogen_password` | 0/1 | Generate random password (hidden from form) |
| `hide_password_repeat` | 0/1 | Hide password-repeat field (copies password silently) |

## Migration notes (3.x → 4.x)

- `manifest.xml` deleted; metadata moved to `elgg-plugin.php` `plugin` key + `composer.json`
- `generate_random_cleartext_password()` (deprecated) replaced by `elgg_generate_password()`
- `\Elgg\Hook` interface type-hint retained in callbacks (valid for 4.x plugin hooks)
- Plugin Composer deps (`bower-asset/zxcvbn`, `bjeavons/zxcvbn-php`) installed via site's
  `elgg-composer.json` in the test stack; `asset-packagist.org` repository added to `composer.json`
- Docker test stack: `ELGG_SITE_URL` changed to `http://elgg/` (internal hostname) to prevent
  Playwright redirect failures; registration enabled via `elgg-install.sh`
- Test suite: `elgg_users_entity` table no longer exists in Elgg 4.x — DB helpers updated to
  query `elgg_entities` + `elgg_metadata`

## Migration notes (4.x → 5.x)

- `composer.json`: `php >=8.2`, `elgg/elgg ^5.0`; Docker image switched to `php:8.2-apache`
- `elgg-plugin.php`: `'hooks'` key renamed to `'events'`; handler references updated to `Events::class`
- `Hooks.php` renamed to `Events.php`; class renamed `Hooks` → `Events`; `\Elgg\Hook` → `\Elgg\Event`
- `get_user_by_username()` → `elgg_get_user_by_username()` (removed in 5.x)
- Tests adapted: `HooksTest.php` → `EventsTest.php`; `Elgg\HooksRegistrationService\Hook` → `Elgg\Event`
- PHPCS: empty IF guards in register.php view inverted to positive conditions; class docblocks added
- Added `docker/elgg5/` stack (PHP 8.2, MySQL 8.0) for 5.x verification

## Migration notes (5.x → 6.x)

- `composer.json`: `php >=8.1`, `elgg/elgg ~6.1.0`, added `ext-intl`
- `elgg-plugin.php`: version bumped `5.0.0 → 6.0.0`
- `password.js` and `username.js`: converted AMD `define(function(require){...})` wrappers to ES module `import` statements; `require('elgg')` → `import elgg from 'elgg'`; `require('jquery')` → `import $ from 'jquery'`
- Docker stack updated to Elgg 6.x (PHPUnit ^10.5, MySQL 8.0)
