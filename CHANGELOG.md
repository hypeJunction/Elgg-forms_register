<a name="6.0.0"></a>
# 6.0.0 (2026-05-09)

### Migration to Elgg 6.x

* Update `composer.json`: `php >=8.1`, `elgg/elgg ~6.1.0`, added `ext-intl`
* Convert `password.js` and `username.js` from AMD (`define(function(require){...})`) to ES modules (`import`)
* Update Docker stack to Elgg 6.x (PHPUnit ^10.5)
* Version bumped to 6.0.0

### BREAKING CHANGES

* Requires Elgg 6.x

---

<a name="5.0.0"></a>
# 5.0.0 (2026-05-04)

### Migration to Elgg 5.x

* Update `composer.json`: php >=8.2, elgg/elgg ^5.0; Docker image php:8.2-apache
* Rename `'hooks'` key to `'events'` in `elgg-plugin.php` (unified events system)
* Rename `Hooks.php` → `Events.php`; class `Hooks` → `Events`; `\Elgg\Hook` → `\Elgg\Event`
* Replace `get_user_by_username()` with `elgg_get_user_by_username()` (function removed in 5.x)
* Adapt test suite: `HooksTest` → `EventsTest`; update `Hook` object usage to `\Elgg\Event`
* Add `docker/elgg5/` stack (PHP 8.2, MySQL 8.0) for Elgg 5.x local verification
* Fix: invert empty IF guards in `register.php` view for PHPCS compliance
* No data migration needed (plugin settings are unchanged)

### BREAKING CHANGES

* Requires Elgg 5.0 or later and PHP 8.2+

<a name="4.0.0"></a>
# 4.0.0 (2026-04-17)

### Migration to Elgg 4.x

* Remove `manifest.xml`; plugin metadata now in `elgg-plugin.php` + `composer.json`
* Replace deprecated `generate_random_cleartext_password()` with `elgg_generate_password()`
* Update `composer.json`: php >=7.4, elgg/elgg ^4.0, composer/installers ^2.0
* Add `asset-packagist.org` repository for `bower-asset/zxcvbn`
* Add per-plugin Docker test stack with PHPUnit + Playwright test suites
* All hooks remain on the `hooks` key (4.x style); no event renames needed

### BREAKING CHANGES

* Requires Elgg 4.0 or later

<a name="2.0.0"></a>
# [2.0.0](https://github.com/hypeJunction/Elgg-forms_register/compare/1.3.5...v2.0.0) (2017-02-01)


### Bug Fixes

* **forms:** respect repeat password setting when rendering the form ([5808125](https://github.com/hypeJunction/Elgg-forms_register/commit/5808125))

### Features

* **forms:** render forms elements using new core API ([d9629c8](https://github.com/hypeJunction/Elgg-forms_register/commit/d9629c8))


### BREAKING CHANGES

* forms: Now requires Elgg 2.3



<a name="1.3.5"></a>
## [1.3.5](https://github.com/hypeJunction/Elgg-forms_register/compare/1.3.4...v1.3.5) (2016-01-12)


### Bug Fixes

* **views:** fix root variable name ([137b2e6](https://github.com/hypeJunction/Elgg-forms_register/commit/137b2e6))



<a name="1.3.4"></a>
## [1.3.4](https://github.com/hypeJunction/Elgg-forms_register/compare/1.3.3...v1.3.4) (2015-12-11)


### Features

* **usernames:** add random alphanum to username generating algorithms ([8a5e801](https://github.com/hypeJunction/Elgg-forms_register/commit/8a5e801))



<a name="1.3.3"></a>
## [1.3.3](https://github.com/hypeJunction/Elgg-forms_register/compare/1.3.2...v1.3.3) (2015-12-10)


### Bug Fixes

* **releases:** do not include mod directory in commits ([cadced2](https://github.com/hypeJunction/Elgg-forms_register/commit/cadced2))



<a name="1.3.2"></a>
## [1.3.2](https://github.com/hypeJunction/Elgg-forms_register/compare/1.3.1...v1.3.2) (2015-12-10)


### Bug Fixes

* **composer:** dependency plugins now installed in mod and cleaned during release ([c063baf](https://github.com/hypeJunction/Elgg-forms_register/commit/c063baf))



<a name="1.3.1"></a>
## [1.3.1](https://github.com/hypeJunction/Elgg-forms_register/compare/1.2.1...v1.3.1) (2015-12-10)


### Bug Fixes

* **grunt:** push tags to the repo ([9de5e7f](https://github.com/hypeJunction/Elgg-forms_register/commit/9de5e7f))
* **manifest:** plugin dependencies are now more transparent ([184b3cb](https://github.com/hypeJunction/Elgg-forms_register/commit/184b3cb))
* **releases:** rebuild changelog and run gitfetch to keep tags up to date ([1c3a63e](https://github.com/hypeJunction/Elgg-forms_register/commit/1c3a63e))
* **releases:** regenerate changelog after commit reword ([d021136](https://github.com/hypeJunction/Elgg-forms_register/commit/d021136))
* **usernames:** username generator fixed and is now configurable ([ec6007a](https://github.com/hypeJunction/Elgg-forms_register/commit/ec6007a))

### Features

* **core:** forms_api requirement is no longer explicit ([713699e](https://github.com/hypeJunction/Elgg-forms_register/commit/713699e))



<a name="1.3.0"></a>
# [1.3.0](https://github.com/hypeJunction/Elgg-forms_register/compare/1.2.0...v1.3.0) (2015-12-10)


### Bug Fixes

* **manifest:** plugin dependencies are now more transparent ([184b3cb](https://github.com/hypeJunction/Elgg-forms_register/commit/184b3cb))
* **releases:** rebuild changelog and run gitfetch to keep tags up to date ([1c3a63e](https://github.com/hypeJunction/Elgg-forms_register/commit/1c3a63e))
* **releases:** regenerate changelog after commit reword ([d021136](https://github.com/hypeJunction/Elgg-forms_register/commit/d021136))
* **usernames:** username generator fixed and is now configurable ([ec6007a](https://github.com/hypeJunction/Elgg-forms_register/commit/ec6007a))

### Features

* **core:** forms_api requirement is no longer explicit ([713699e](https://github.com/hypeJunction/Elgg-forms_register/commit/713699e))



