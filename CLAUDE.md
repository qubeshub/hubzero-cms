# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Overview

This is the QUBESHub instance of the [HUBzero® Platform for Scientific Collaboration](https://hubzero.org), a PHP CMS (version 2.4.1). The repository uses a **manual git subtree** approach to manage upstream HUBzero code alongside QUBESHub-specific overrides.

## Key Directories

- `core/` — Upstream HUBzero framework. **Do not modify directly** for QUBESHub-specific work; fixes to upstream bugs should be PRed to the upstream repo via the `upstream/2.4-main` remote.
- `app/` — QUBESHub-specific overrides and extensions. Components/plugins/templates here shadow their counterparts in `core/`.
- `app/config/` — Environment configuration (database, mail, session, etc.).
- `app/templates/bmc/` is the active site template.

## Common Commands

All commands are run from within DDEV (`ddev ssh`, then navigate to `/var/www/html/public/core`):

```bash
# Install/update PHP dependencies
php bin/composer install

# Run database migrations
php bin/muse migration -f

# Run PHPUnit tests (from core/ - DO NOT USE)
vendor/bin/phpunit libraries/Hubzero/Database/Tests/

# Run a single test file (DO NOT USE)
vendor/bin/phpunit libraries/Hubzero/Database/Tests/RelationalTest.php
```

## Architecture

### Hubzero CMS library (and Qubeshub overrides)
`core/libraries/Hubzero` is shared framework code — the platform's engine room. It's organized by concern (Database, Auth, Http, Session, Routing, Filesystem, etc.), namespaced `Hubzero\*`, and consumed *by* components/plugins/modules/templates (see Extension Types below). It defines base abstractions like `Relational` (ORM base), the `Plugin` base class, `SiteController`, etc. Nothing here is directly routable or user-facing — it's library code that gets extended/used, never the reverse. `app/libraries/Qubeshub` holds the QUBESHub-specific overrides: shared code specific to this instance rather than upstream HUBzero.

### Bootstrap & Entry Point

`index.php` → `core/vendor/autoload.php` → `core/bootstrap/app.php` → `Hubzero\Base\Application::run()`

The `Application` class extends a Laravel-inspired IoC `Container`. Global helpers `app()`, `config()`, `ddie()`, and `dlog()` are defined in `bootstrap/app.php`. The constant `_HZEXEC_` is defined at boot; extension files check `defined('_HZEXEC_') or die()` as a security guard.

Key path constants: `PATH_ROOT`, `PATH_CORE` (`/core`), `PATH_APP` (`/app`).

### Extension Types

**Components** (`core/components/com_<name>/`, `app/components/com_<name>/`)
- Full features/apps (forums, wiki, groups, resources) with their own routable controllers, views, and models — what a URL resolves to.
- Each component has `site/`, `admin/`, `api/`, `models/`, `migrations/`, `config/` subdirectories.
- `site/<componentname>.php` is the entry point; it resolves a controller by `?controller=` or `?view=` query param.
- Controllers extend `Hubzero\Component\SiteController`. Public methods ending in `Task` (e.g., `displayTask`, `editTask`) are the callable actions.
- Views live in `site/views/<viewname>/tmpl/<layout>.php`. The default layout matches the task name.
- Namespace pattern: `Components\<ComponentName>\Site\Controllers\<ControllerName>`.

**Plugins** (`core/plugins/<group>/<name>/`, `app/plugins/<group>/<name>/`)
- Hooks into events/lifecycle points across the system.
- Class naming: `plg<Group><Name>` (e.g., `plgHubzeroComments`), extending `Hubzero\Plugin\Plugin`.
- Each plugin has a `.php` file and a `.xml` manifest; may include `views/`, `assets/`, `migrations/`, `language/`.

**Templates** (`app/templates/<name>/`)
- Control site-wide layout/theming — the HTML skeleton and view overrides.
- `index.php` is the main layout; `component.php` is used for component-only output.
- The `html/` subdirectory holds view overrides (e.g., `html/com_members/profiles/default.php` overrides the members component's default profile view).

**Modules** (`core/modules/mod_<name>/`, `app/modules/mod_<name>/`)
- Small, reusable display widgets injected into page layouts (sidebars, menus, feeds).

### ORM / Database

Models extend `Hubzero\Database\Relational` (in `core/libraries/Hubzero/Database/`). It supports `belongsToOne`, `oneToMany`, `manyToMany`, and polymorphic relationships. State constants `STATE_PUBLISHED`, `STATE_UNPUBLISHED`, `STATE_DELETED` are defined on the base class.

### Autoloading

PSR-0: The `Hubzero\` namespace maps to `core/libraries/`. Both `PATH_APP` and `PATH_CORE` are registered as class loader roots, so `app/` overrides can shadow `core/` classes.

### Migrations

Migration files follow the naming convention `Migration<YYYYMMDDHHmmss><ExtensionName>.php` and extend `Hubzero\Content\Migration\Base`. They live in `migrations/` subdirectories of components/plugins or in `core/migrations/` for core migrations. Run with `php muse migration -f`.

## Git Subtree Workflow

This repo uses **manual subtree** management (not `git subtree`). Extensions tracked as separate GitHub repos under `qubeshub/` org are merged into `app/` subdirectories. See `README.md` for the full cherry-pick and squash-merge workflow used during monthly upstream updates.

Remotes:
- `origin` — `https://github.com/qubeshub/hubzero.cms` (this repo)
- `upstream` — HUBzero upstream (`2.4-main` branch)
- Additional per-extension remotes for each tracked `app/` subtree

To fix a core (upstream) bug:
```bash
git checkout --track -b fix-core-bug upstream/2.4-main
# ... make fix, then:
git push -u origin fix-core-bug
# Open a PR to upstream
```
