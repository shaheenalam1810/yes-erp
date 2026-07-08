# SME Cloud ERP

A production-oriented Laravel 12 and PHP 8.4 foundation for a modular cloud ERP serving small and medium businesses.

## Core Capabilities

- Multi-company, branch, and warehouse operations
- Role-based access control with company-aware permissions
- Accounting, CRM, inventory, POS, reports, HR, courier, e-commerce, and document modules
- API-first integration layer for mobile apps, marketplaces, couriers, and external BI
- Queue-backed jobs, audit logging, file storage, and strict tenant context controls

## Architecture

The application uses a central Laravel app with bounded ERP modules under `modules/`. Shared platform concerns live in `app/Support` and `app/Http/Middleware`.

Primary boundaries:

- `Core`: companies, branches, warehouses, settings, fiscal periods
- `Accounting`: chart of accounts, journals, ledgers, payments, tax
- `CRM`: leads, customers, opportunities, activities
- `Inventory`: items, stock movements, purchases, transfers, adjustments
- `POS`: tills, shifts, sales, returns, receipts
- `HR`: employees, attendance, payroll hooks
- `Reports`: operational and financial reporting endpoints
- `Integrations`: courier and e-commerce connectors
- `Documents`: managed files, templates, approvals, retention

## Local Setup

Install PHP 8.4, Composer, Node.js, MySQL, and Redis, then run:

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan serve
```

This environment does not currently have PHP or Composer installed, so dependencies were not installed here.

## Production Notes

- Keep `ERP_STRICT_TENANCY=true` outside local development.
- Run queues with a process manager and separate high-priority queues for POS, accounting postings, and integrations.
- Use object storage for documents and generated reports.
- Enable centralized logs, database backups, and audit retention policies.
- Put all external integrations behind signed webhooks, idempotency keys, and retryable jobs.
