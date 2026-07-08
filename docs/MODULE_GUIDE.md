# Module Guide

## Module Structure

Use this shape for new modules:

```text
modules/ModuleName/
  routes/api.php
  Http/Controllers/
  Actions/
  Models/
  Policies/
  Jobs/
  Events/
  Listeners/
  Tests/
```

## Rules

- Keep business behavior inside actions or services, not controllers.
- Every operational model must include `company_id`.
- Use policies for user access and tenant membership checks.
- Use events for cross-module effects such as accounting postings or notifications.
- Use queued jobs for imports, exports, syncs, report generation, and webhook processing.
- Prefer append-only transaction tables for finance and inventory.

## Naming

- Controller: `StoreCustomerController`
- Action: `CreateCustomer`
- Policy: `CustomerPolicy`
- Event: `SaleCompleted`
- Job: `SyncShopifyOrders`

## Testing

Each module should include:

- Policy tests for tenant isolation and role behavior
- Action tests for business rules
- API tests for request validation and response contracts
- Integration tests for queued jobs and external connector adapters
