# CLAUDE.md

# YES ERP - AI Development Constitution

This document is the permanent operating manual for all AI-assisted development on this project.

---

# Project

Name:
YES ERP

Type:
Production-grade SME Business ERP

Framework:
Laravel 12

Language:
PHP 8.4

Database:
MySQL 8

Frontend:
Blade
Tailwind CSS
Alpine.js

API
/api/v1

Architecture
Modular Monolith

---

# Reference Documents

The governance documents referenced throughout this file live in the repo root and `docs/`:

- `PROJECT_CONSTITUTION.md` (root)
- `docs/ARCHITECTURE.md`
- `docs/DATABASE_ARCHITECTURE.md`
- `docs/BUSINESS_RULES_WORKFLOW.md`
- `docs/UI_UX_ARCHITECTURE.md`
- `docs/MODULE_GUIDE.md`
- `docs/DEPLOYMENT.md`
- `DEVELOPMENT_ROADMAP.md` (root)

`DATABASE_ARCHITECTURE.md` and `BUSINESS_RULES_WORKFLOW.md` are design-only: they define the target schema and workflows in prose and contain no code, migrations, or SQL. Do not copy field lists from them into migrations without checking `DEVELOPMENT_ROADMAP.md` to confirm the current phase actually calls for that table yet.

---

# Common Commands

```bash
composer install                 # install PHP dependencies
npm install                      # install frontend dependencies

php artisan migrate              # run migrations (MySQL by default, see .env)
php artisan db:seed              # run database/seeders/DatabaseSeeder.php

composer test                    # clears config cache, then runs the Pest suite
vendor/bin/pest                  # run tests directly
vendor/bin/pest --filter=<name>  # run a single test by name
vendor/bin/pest tests/Feature/FoundationTest.php  # run a single test file

composer analyse                 # PHPStan/Larastan, level 6, see phpstan.neon
composer format                  # Laravel Pint, see pint.json

composer dev                     # concurrently runs serve + queue:listen + vite dev
```

Test environment uses in-memory SQLite (`phpunit.xml`), sync queue driver, and array cache/session drivers — no external services required to run the suite.

---

# Current Codebase State

This repository is in early foundation stage (roadmap Phase 1), not yet a full implementation of the constitution above. Concretely, as of now:

- Only `Core`, `Accounting`, and `Inventory` have migrations (`database/migrations/`), and those tables do **not** yet have `uuid`, `created_by`/`updated_by`/`deleted_by`, or soft deletes — those platform conventions land in a later roadmap phase (Phase 6: Global Platform Services). Don't assume every table already follows the Database Rules above; check the actual migration first.
- Most `modules/{Name}/routes/api.php` files are placeholder stub endpoints that just return a JSON list of planned resource names (e.g. `modules/Core/routes/api.php`). Only `Accounting` (`Account`) and `Inventory` (`Item`, `StockMovement`) have real Eloquent models so far.
- `.agents/*.md` (architect, backend, database, frontend, qa, reviewer) exist as empty placeholder files.
- Before adding a table, model, or route, check `DEVELOPMENT_ROADMAP.md` for the current phase's actual scope — don't jump ahead to fields/tables described in `DATABASE_ARCHITECTURE.md` that belong to a later phase.

---

# Architecture Map

## App vs. Modules split

- `app/` owns shared platform concerns: base model traits, tenancy context, middleware, the `ErpServiceProvider`, and cross-cutting `Foundation/` base classes.
- `modules/{ModuleName}/` owns one approved business module (see Approved Modules list). Each module has its own `routes/api.php` and, as it grows, `Models/`, `Actions/`, `Services/`, `Policies/`, etc. per `docs/MODULE_GUIDE.md`.
- Module autoloading is PSR-4 `Modules\` → `modules/` (see `composer.json`).

## Tenancy flow (company context)

1. Every request under `/api/v1` first hits `auth:sanctum` (`routes/api.php`), then the `erp.company` middleware alias (`routes/api/v1.php`), which resolves to `App\Http\Middleware\EnsureCompanyContext`.
2. That middleware reads the `X-Company-Id` header (name configurable via `config/erp.php: tenant_header`), loads the active `Company`, and stores it on `App\Support\Tenancy\CurrentCompany` — a request-scoped singleton bound in `ErpServiceProvider::register()`.
3. Controllers/actions/services pull the active company via the injected `CurrentCompany` instance rather than reading the header directly. `GET /api/v1/context` (`ContextController`) returns the resolved user/company/branch/warehouse/enabled-modules for debugging.
4. Models that belong to a company use the `App\Models\Concerns\BelongsToCompany` trait for the `company()` relation; there is no global query scope yet enforcing company isolation automatically, so new queries must filter by company explicitly until that lands.

## Routing structure

`routes/api.php` → prefixes everything with `v1` and applies `auth:sanctum` → includes `routes/api/v1.php` → applies `erp.company` → `require`s each module's `modules/{Name}/routes/api.php`. To add endpoints to a module, edit that module's route file; to add a new module's routes, register the `require` line in `routes/api/v1.php` (module must first be added to the Approved Modules list and `config/erp.php: modules`).

## Foundation base classes (`app/Foundation/`)

- `Actions/Action.php` — abstract `readonly` base for single-purpose business actions (currently empty; extend per Architecture Rules).
- `Services/Service.php` — abstract base for multi-step workflow services.
- `Data/DataObject.php` — abstract `readonly` DTO base requiring `toArray(): array`.
- `Http/ApiResponse.php` — `ApiResponse::success()` / `ApiResponse::error()` static helpers for the consistent JSON envelope (`success`, `message`, `data`/`errors`, `meta`) required by the API Rules.

## Coding conventions already enforced by tooling

- `declare(strict_types=1)` and `final` classes are the norm throughout `app/` and `modules/` — match this in new code.
- Pint (`pint.json`) enforces the Laravel preset plus alphabetically-sorted imports and no unused imports.
- PHPStan/Larastan (`phpstan.neon`) runs at level 6 over `app`, `modules`, `routes`, `config`, `tests`.

---

# Primary Goal

Build a commercial-grade SME ERP.

Quality is more important than speed.

Never generate prototype code.

Never generate demo code.

Never generate placeholder business logic.

Everything must be production-ready.

---

# Approved Modules

ONLY these modules may exist.

1. Dashboard
2. User & Role Management
3. Customer (CRM)
4. Supplier Management
5. Product Management
6. Inventory Management
7. Purchase Management
8. Sales Management
9. POS
10. Accounting
11. Courier Integration
12. E-commerce Integration
13. HR & Payroll
14. Reports
15. Document Management
16. Multi Business
17. Settings

Never create additional business modules without explicit approval.

---

# Development Rules

Never continue automatically.

Always stop after each approved Sprint.

Wait for approval.

Never skip phases.

Never rewrite existing working code unless requested.

Never remove features.

Never change database structure without approval.

---

# Architecture Rules

Controllers must remain thin.

Business logic belongs in:

- Services
- Actions

Validation belongs in:

- Form Requests

Authorization belongs in:

- Policies

Database queries should be encapsulated cleanly (repositories are optional; use them only when they improve maintainability).

Background work:

- Jobs
- Queues

Communication:

- Events
- Listeners

API:

Resources

---

# Database Rules

Shared MySQL database.

Every business table must contain

company_id

uuid

created_by

updated_by

deleted_by

timestamps

soft deletes

UUID must be used for external references.

Use transactions for every business workflow.

Money fields

DECIMAL(18,2)

---

# Inventory Rules

Stock source of truth:

stock_movements

Never store duplicated stock quantities.

Inventory must always be traceable.

---

# Accounting Rules

Double-entry accounting only.

Every financial transaction must remain balanced.

No direct balance updates.

Balances are calculated from ledger entries.

---

# Security Rules

RBAC everywhere.

Permission-based navigation.

Company isolation.

Branch isolation.

Warehouse isolation.

No unauthorized data access.

Validate every request.

Escape output.

Protect against mass assignment.

---

# Audit Rules

Every important action must be logged.

Create

Update

Delete

Approve

Cancel

Reverse

Login

Permission changes

Financial changes

Inventory changes

Audit logs must never be deleted.

---

# UI Rules

Use reusable Blade components.

Use Tailwind only.

Responsive.

Dark mode compatible.

Reusable tables.

Reusable forms.

Reusable modals.

Consistent spacing.

No duplicated UI.

---

# API Rules

All APIs

/api/v1

Return consistent JSON.

Use Resources.

Never expose internal IDs publicly.

Use UUID.

---

# Coding Standards

Follow PSR.

Laravel best practices.

Strict typing.

Small methods.

Readable names.

No dead code.

No duplicated logic.

No magic numbers.

No commented-out code.

---

# Performance

Prevent N+1 queries.

Use eager loading.

Queue slow tasks.

Index searchable columns.

Optimize reports.

---

# Testing

Every Sprint must include

Feature Tests

Unit Tests

Critical workflow tests

Nothing is complete without passing tests.

---

# Git Rules

One Sprint

One Commit

Commit message example

Sprint 7 - Inventory Transfer

Never commit broken code.

---

# Definition of Done

A Sprint is complete only if

All tests pass

No PHP errors

Laravel Pint passes

PHPStan passes

No duplicated code

Documentation updated

Git committed

Git pushed

Review completed

---

# Development Workflow

Understand task

Review architecture

Review database

Review dependencies

Implement

Run tests

Explain changed files

Wait for approval

Never continue automatically.

---

# Communication Rules

Always explain

Why

What changed

Files changed

Risks

Migration impact

Testing completed

Never give one-line answers.

---

# When Unsure

Never guess.

Ask for clarification.

Never invent business rules.

Never invent database fields.

Never invent workflows.

Never invent modules.

Always follow the approved documents.

---

# Priority Order

PROJECT_CONSTITUTION.md

ARCHITECTURE.md

DATABASE_ARCHITECTURE.md

BUSINESS_RULES_WORKFLOW.md

UI_UX_ARCHITECTURE.md

DEVELOPMENT_ROADMAP.md

CLAUDE.md

If conflicts exist, follow the higher-priority document.

---

# Final Rule

Build enterprise-quality software.

Correctness is more important than speed.

Maintainability is more important than cleverness.

Consistency is mandatory.

Never continue without approval.