# Project Constitution

## Purpose

This constitution defines the permanent development rules for the SME Business ERP. These rules govern architecture, implementation, quality, security, workflow, and approvals for the full lifetime of the project.

If a future instruction conflicts with this constitution, the conflict must be identified and approved before implementation.

## Approved Technology Stack

- Laravel 12
- PHP 8.4
- MySQL 8
- Blade
- Tailwind CSS

No additional primary framework, frontend runtime, database engine, or architecture style may be introduced without approval.

## Approved Modules

Only these modules are approved:

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

No extra business module may be designed, scaffolded, coded, migrated, or documented as active scope without approval.

## Architecture Rules

- Use Laravel 12 best practices.
- Use a modular monolith architecture.
- Use Service Layer and Action Classes for business workflows.
- Use Eloquent ORM as the primary data access layer.
- Do not force the Repository Pattern unless there is a clear technical need.
- Keep controllers thin.
- Keep models focused on relationships, casts, scopes, and simple domain helpers.
- Keep business rules out of Blade templates.
- Keep modules isolated and independently understandable.
- Cross-module communication should use events, listeners, services, or explicit contracts.
- Future modules must be addable without changing existing module internals.

## Database Rules

- Use one shared MySQL database.
- Every business table must include `company_id`.
- Branches and warehouses belong to a company.
- Every business table must include `id`, `uuid`, `company_id`, `created_by`, `updated_by`, `deleted_by`, timestamps, and soft deletes.
- UUIDs must be used for public URLs, APIs, imports, exports, integrations, and external references.
- Numeric IDs may be used internally for joins and performance.
- Every business table must support Soft Deletes.
- All money fields must use `DECIMAL(18,2)`.
- Multi-currency-ready documents must include currency references and exchange rates where appropriate.
- Every business transaction must run inside a database transaction.
- Inventory source of truth is stock movement history.
- Current stock must be derived from stock movements or from projections generated from stock movements.
- Product variants must be separate from product master records.
- Product variants own SKU, barcode, cost price, selling price, and stock identity.
- Business documents must separate headers and line items.
- Double-entry accounting is mandatory for all posted financial impact.
- Posted journals must balance debit and credit totals.
- Reports must use real-time transactional data unless cached reporting is explicitly approved later.

## Workflow Rules

Standard workflow statuses:

- `draft`
- `pending`
- `approved`
- `completed`
- `cancelled`

Rules:

- Draft records may not create final accounting or inventory impact.
- Pending records wait for approval.
- Approved records are ready for completion or posting.
- Completed records are locked from normal editing.
- Cancelled records must keep history and audit data.
- Completed records must be corrected through reversal workflows, not direct editing.
- Cancellation and reversal require permission and reason.

## Security Rules

- Secure by default.
- All business routes require authentication.
- All business actions require authorization.
- RBAC must be enforced with permissions and policies.
- Permission checks must include company context.
- Navigation, buttons, bulk actions, exports, reports, and APIs must be permission-aware.
- Never trust client-provided company, branch, warehouse, user, price, permission, or accounting values without server-side validation.
- CSRF protection is required for Blade routes.
- API routes must be rate-limited.
- Passwords must use Laravel-supported secure hashing.
- Sensitive actions must be audited.
- Public APIs must not expose internal numeric IDs.

## API Rules

- All APIs must be versioned under `/api/v1`.
- APIs must use UUIDs for public identifiers.
- APIs must use Form Requests or equivalent validation.
- APIs must use policies for authorization.
- APIs must return consistent JSON responses.
- API errors must be clear, stable, and safe.
- API behavior must be testable independently from Blade UI.

## UI/UX Rules

- Use Blade and Tailwind CSS.
- UI must be operational, clear, and efficient.
- Do not build marketing pages as ERP screens.
- Use consistent reusable components for layouts, forms, tables, filters, modals, alerts, notifications, breadcrumbs, and status badges.
- UI must support desktop, tablet, and mobile behavior.
- Dark mode must be supported.
- Loading, empty, validation, permission, and error states must be designed for every major screen.
- Print layouts must be clean, light-mode by default, and must not expose internal numeric IDs.

## Audit Rules

A complete audit log system is mandatory.

Audit these actions at minimum:

- Login
- Logout
- Invoice create, update, delete
- Product update
- Product variant update
- Inventory changes
- Payment
- Settings changes
- User changes
- Permission changes
- Accounting posting
- POS sale completion
- Payroll posting
- Integration sync failures

Audit logs must include company, branch when applicable, warehouse when applicable, user, action, auditable type, auditable UUID, old values, new values, IP address, user agent, request ID, and timestamp.

Audit logs are append-only and must not be edited by normal application workflows.

## Queue, Event, Notification Rules

- Events, Listeners, Notifications, and Queues must be enabled from the beginning.
- Long-running work must be queued.
- External integrations must use retryable jobs.
- Important domain activity must dispatch events after successful transaction commit.
- Notifications must be permission-aware and company-scoped.

Queue candidates:

- Imports
- Exports
- Report exports
- Courier sync
- E-commerce sync
- Webhook processing
- Email notifications
- Document processing
- Payroll notifications

## Testing Rules

Every implementation phase must be independently testable.

Required test categories where applicable:

- Unit tests for actions and services
- Feature tests for HTTP workflows
- Policy tests for permissions and company isolation
- Database tests for posting and transaction effects
- UI tests for critical Blade workflows when UI exists
- Queue tests for jobs and listeners
- API tests for versioned endpoints

No phase is complete without verification notes.

## Quality Rules

- Code must be clean, readable, and intentionally named.
- SOLID principles must guide design.
- Avoid premature abstractions.
- Do not introduce repositories unless there is a real technical need.
- Do not duplicate business rules between UI, API, and services.
- Validate at the boundary, enforce rules in actions/services, and persist through Eloquent.
- Prefer small focused classes.
- Avoid hidden side effects.
- Every destructive or posting action must be auditable.

## Approval Rules

- Work proceeds phase by phase.
- After every phase, stop and wait for approval.
- Never continue automatically.
- Do not change business logic without asking.
- Do not remove existing functionality without approval.
- Do not add unapproved modules.
- Before writing Laravel code for a new phase, confirm the phase goal and scope.

## Documentation Rules

Architecture, database design, UI/UX, workflow rules, roadmap, and major decisions must remain documented.

Documentation must be updated when:

- A business rule changes
- A workflow changes
- A table design changes
- A permission model changes
- A module scope changes
- A new integration rule is approved

## Definition Of Done

A phase is done only when:

- The approved scope is complete.
- Tests or verification steps pass.
- Security and permission behavior is checked.
- Audit behavior is checked where applicable.
- Documentation is updated where applicable.
- No unapproved module or business behavior was added.
- Remaining risks or gaps are clearly reported.
