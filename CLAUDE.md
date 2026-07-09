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
6. Inventory
7. Purchase
8. Sales
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