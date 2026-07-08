# Architecture

## Purpose

This ERP is a production-ready, cloud-based SME business system built with Laravel 12, PHP 8.4, MySQL 8, Blade, and Tailwind CSS.

The architecture must support real business operations while remaining modular, secure, maintainable, and easy to extend. Future modules must be added without changing existing module internals.

These rules override earlier design decisions.

## Mandatory Principles

- Follow Laravel 12 best practices.
- Use a modular monolith architecture.
- Use one shared MySQL database.
- Use Eloquent ORM as the primary data access layer.
- Use Service Layer and Action Classes for business workflows.
- Do not force the Repository Pattern unless there is a clear technical need.
- Keep controllers thin.
- Keep models focused on relationships, casts, scopes, and simple domain helpers.
- Every approved module must support Soft Deletes.
- Every business transaction must run inside a database transaction.
- APIs must be versioned under `/api/v1`.
- Events, Notifications, Queues, and Listeners must be available from the beginning.
- Reports must use real-time data unless a report is explicitly marked as cached in the future.
- Do not add extra business modules beyond the approved scope.

## Modular Architecture

The application is a modular monolith. The Laravel application owns shared platform concerns such as authentication, authorization, tenancy context, audit logging, queues, notifications, UI layout, API response format, and cross-module events.

Approved business functionality lives under `modules/{ModuleName}`.

Each module should be independently understandable and should avoid reaching into another module's internal classes. Cross-module work should use events, listeners, services, or explicit contracts.

Recommended module structure:

```text
modules/ModuleName/
  Http/Controllers/
  Http/Requests/
  Actions/
  Services/
  Models/
  Policies/
  Events/
  Listeners/
  Jobs/
  Notifications/
  Data/
  routes/
  database/migrations/
  tests/
```

Repositories are optional. They should be introduced only when they solve a real technical need, such as complex reusable query composition, external data sources, read-model abstraction, or integration testing boundaries. Standard CRUD and normal business queries should use Eloquent directly through actions, services, scopes, and query objects where appropriate.

## Data Model Rules

The ERP uses one shared MySQL database. Tenant separation is enforced through `company_id` on business tables.

Every business table must include:

- `id`
- `uuid`
- `company_id`
- `created_by`
- `updated_by`
- `deleted_by`
- `created_at`
- `updated_at`
- `deleted_at`

Additional rules:

- UUIDs are required for all public URLs and external references.
- Internal joins may use numeric IDs for performance.
- Public routes, API payloads, imports, exports, and integration references must use UUIDs.
- Every business table must use Soft Deletes.
- Branches belong to a company.
- Warehouses belong to a company.
- Warehouses may belong to a branch when the approved business workflow requires it.
- Records scoped to a branch or warehouse must still include `company_id`.
- Unique constraints for business records must be company-aware.

Example uniqueness rule:

```text
company_id + code
company_id + uuid
company_id + document_number
```

## Company, Branch, and Warehouse Context

Every authenticated business request must resolve company context before accessing module data.

Branch and warehouse context should be resolved only when the workflow requires them. A company may have multiple branches and warehouses.

Access checks must verify:

- The authenticated user belongs to the company.
- The selected branch belongs to the company.
- The selected warehouse belongs to the company.
- The user has permission to perform the requested action.

No module may trust a client-provided company, branch, warehouse, user, or permission value without server-side validation.

## Business Logic

Business logic belongs in Action Classes and Services.

Use Action Classes for focused use cases, such as:

- Create a record
- Update a record
- Delete a record
- Post a transaction
- Approve a document
- Change a setting

Use Services for larger workflows that coordinate multiple actions, models, events, notifications, jobs, and external systems.

Controllers should only:

- Accept requests
- Authorize access
- Call actions or services
- Return Blade views or API resources

## Database Transactions

Every business transaction must use a database transaction.

This includes all workflows that create, update, delete, approve, post, pay, move stock, change settings, or change permissions.

Transaction boundaries should live in actions or services, not controllers.

Events that depend on committed data should be dispatched after commit.

## Events, Listeners, Queues, and Notifications

Events, Notifications, Queues, and Listeners must be enabled from the beginning.

Use events for important domain activity and cross-module communication. Use listeners for follow-up work such as audit logging, notifications, report refreshes, and integration jobs.

Queue long-running or failure-prone work:

- Imports
- Exports
- Report generation
- External API sync
- Webhook processing
- Email and SMS notifications
- Document processing

Critical synchronous business writes should complete before queueing follow-up work.

## Audit Log System

The ERP must include a complete audit log system from the beginning.

Audit logs must capture important business and security actions, including:

- Login
- Logout
- Invoice Create
- Invoice Update
- Invoice Delete
- Product Update
- Inventory Changes
- Payment
- Settings Changes
- User Changes
- Permission Changes

Audit log records should include:

- Company
- Branch, when applicable
- Warehouse, when applicable
- User
- Event name
- Auditable model type
- Auditable model UUID
- Action
- Old values, when applicable
- New values, when applicable
- IP address
- User agent
- Request ID
- Timestamp

Audit logs should be append-only. They must not be edited by normal application workflows.

## Authorization And RBAC

RBAC must be enforced with Laravel policies and permissions.

Authorization should check both:

- Role or permission
- Company ownership or membership context

Permission names should be module-aware and action-aware.

Example:

```text
core.users.view
core.users.create
core.users.update
core.users.delete
inventory.products.update
settings.company.update
```

Permission changes themselves must be audited.

## API Architecture

All APIs must be versioned under:

```text
/api/v1
```

API routes should use:

- Laravel Form Requests for validation
- Policies for authorization
- API Resources for responses
- UUID route binding for public identifiers
- Consistent JSON error responses

Internal numeric IDs must not be exposed as public API identifiers.

## Blade And Tailwind UI

Blade and Tailwind CSS are the primary UI stack.

The UI should use reusable layout, navigation, form, table, filter, modal, alert, and action-button components.

Module screens should share the same design system but keep module-specific workflows inside the module.

## Reporting

Reports must use real-time data by default.

A report may only use cached data if it is explicitly marked as cached in a future approved requirement.

Reports must respect:

- Company scope
- Branch scope, when applicable
- Warehouse scope, when applicable
- User permissions
- Soft-deleted record rules

## Security

The application must be secure by default.

Required controls:

- Authentication on all business routes.
- Authorization through policies and permissions.
- Company context validation on every business request.
- UUIDs for public references.
- Request validation through Form Requests.
- CSRF protection for Blade routes.
- Rate limiting for APIs and authentication.
- Secure password hashing.
- Audit logs for important business and security actions.
- No mass assignment of unsafe fields.
- No direct trust of client-provided tenant context.

## Future Extension Rules

Future modules must be easy to add without changing existing modules.

To preserve this:

- Shared behavior belongs in the platform layer only when more than one approved module needs it.
- Module internals must not be tightly coupled to other modules.
- Cross-module workflows should use events, listeners, contracts, or clearly named services.
- Public module behavior should be exposed through routes, actions, services, events, or policies.
- New modules require approval before implementation.

## Approval Workflow

Development must proceed phase by phase.

After each phase:

1. Present what was completed.
2. Explain design decisions.
3. Identify any risks or open questions.
4. Wait for approval before continuing.

No automatic continuation is allowed after a phase is complete.
