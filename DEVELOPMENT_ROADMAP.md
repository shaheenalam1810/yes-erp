# Development Roadmap

## Purpose

This roadmap breaks the approved SME Business ERP into the smallest practical implementation phases. Each phase has a clear goal and must be independently testable.

No phase should continue automatically after completion. Approval is required before starting the next phase.

## Phase 0: Project Governance

Goal: finalize permanent rules and development boundaries.

Deliverables:

- Project constitution
- Development roadmap
- Confirmed approved module list

Independent Tests:

- Review constitution against architecture, database, UI/UX, and workflow documents.
- Confirm no unapproved modules are included.

## Phase 1: Laravel Foundation

Goal: install and verify a clean Laravel 12 application foundation.

Deliverables:

- Laravel 12 app bootstrapped
- PHP 8.4 compatibility verified
- Environment configuration
- Basic app health route
- Local development instructions

Independent Tests:

- Application boots.
- Environment loads.
- Test suite runs.
- Health page responds.

## Phase 2: Frontend Foundation

Goal: establish Blade and Tailwind UI foundation.

Deliverables:

- Base app layout
- Guest layout
- Auth layout
- Sidebar shell
- Top navigation shell
- Theme structure
- Reusable alert and notification placeholders

Independent Tests:

- Layout renders.
- Navigation shell renders.
- Dark mode toggle behavior is verified.
- Mobile shell behavior is verified.

## Phase 3: Authentication Foundation

Goal: implement secure login/logout and user session handling.

Deliverables:

- Login
- Logout
- Password hashing
- Session protection
- Login/logout audit events

Independent Tests:

- Valid user can log in.
- Invalid credentials are rejected.
- User can log out.
- Login and logout are audited.

## Phase 4: Multi Business Foundation

Goal: implement company, branch, and warehouse foundation.

Deliverables:

- Companies
- Branches
- Warehouses
- Company switcher
- Branch switcher
- Warehouse context where applicable

Independent Tests:

- Company CRUD works.
- Branch belongs to company.
- Warehouse belongs to company.
- User cannot access another company context.

## Phase 5: User & Role Management Foundation

Goal: implement RBAC and company-aware user access.

Deliverables:

- Users
- Roles
- Permissions
- Company user membership
- Permission-based navigation
- Permission audit logs

Independent Tests:

- User can be assigned to company.
- Role can be assigned to user.
- Permission controls page access.
- Permission changes are audited.

## Phase 6: Global Platform Services

Goal: implement shared services required by all modules.

Deliverables:

- UUID public routing convention
- Audit log service
- Status workflow helper
- Notification foundation
- Queue foundation
- File/document attachment foundation
- Standard API response format

Independent Tests:

- UUID lookup works.
- Audit log records actions.
- Queued notification can be dispatched.
- API response format is consistent.

## Phase 7: Settings Foundation

Goal: implement dedicated configuration tables and screens.

Deliverables:

- Company settings
- Branch settings
- Invoice settings
- Tax settings
- Accounting settings
- POS settings

Independent Tests:

- Settings save and load by company.
- Branch settings override company defaults where applicable.
- Settings changes are audited.

## Phase 8: Currency Foundation

Goal: implement multi-currency readiness.

Deliverables:

- Currencies
- Exchange rates
- Company base currency
- Currency selectors

Independent Tests:

- Currency CRUD works.
- Exchange rate is unique by company/date/currency pair.
- Company base currency is enforced.

## Phase 9: Accounting Master Data

Goal: implement chart of accounts and accounting periods.

Deliverables:

- Account groups
- Accounts
- Fiscal years
- Accounting periods
- Journals
- Payment methods

Independent Tests:

- Account code is unique per company.
- Posting account validation works.
- Period open/closed state is enforced.

## Phase 10: Double Entry Journal Engine

Goal: implement core double-entry posting engine.

Deliverables:

- Journal entry draft
- Journal lines
- Debit/credit validation
- Posting workflow
- Reversal journal workflow

Independent Tests:

- Balanced journal can post.
- Unbalanced journal cannot post.
- Closed period blocks posting.
- Reversal creates opposite entries.

## Phase 11: Customer CRM Foundation

Goal: implement customer master data.

Deliverables:

- Customers
- Customer contacts
- Customer addresses
- Customer activities
- Customer documents

Independent Tests:

- Customer code unique per company.
- Customer CRUD respects company scope.
- Customer activity is audited.

## Phase 12: Supplier Foundation

Goal: implement supplier master data.

Deliverables:

- Suppliers
- Supplier contacts
- Supplier addresses
- Supplier documents

Independent Tests:

- Supplier code unique per company.
- Supplier CRUD respects company scope.
- Supplier changes are audited.

## Phase 13: Product Master Data

Goal: implement product family setup.

Deliverables:

- Product categories
- Brands
- Units
- Products
- Product documents

Independent Tests:

- Product slug unique per company.
- Product requires base unit.
- Product cannot be used as stock unit without variant.

## Phase 14: Product Variant Management

Goal: implement sellable and stockable variants.

Deliverables:

- Product attributes
- Attribute values
- Product variants
- Variant attribute combinations
- SKU and barcode management

Independent Tests:

- SKU unique per company.
- Barcode unique per company when present.
- Variant belongs to product.
- Duplicate attribute combination is blocked.

## Phase 15: Inventory Opening Stock

Goal: create opening stock through movement history.

Deliverables:

- Opening stock workflow
- Positive stock movements
- Opening inventory accounting option

Independent Tests:

- Opening stock creates movement.
- Current stock is calculated from movement.
- Duplicate opening stock is blocked where required.

## Phase 16: Inventory Stock Ledger

Goal: implement stock movement history and stock-on-hand views.

Deliverables:

- Stock movement history
- Stock on hand report/view
- Variant warehouse stock calculation
- Low stock indicator

Independent Tests:

- Stock is derived from movements.
- Filters by warehouse and variant work.
- Soft-deleted movements are excluded unless explicitly requested.

## Phase 17: Stock Transfer

Goal: implement warehouse-to-warehouse transfer.

Deliverables:

- Transfer draft
- Transfer approval
- Transfer completion
- Paired stock movements

Independent Tests:

- Same source/destination warehouse is blocked.
- Insufficient stock is blocked.
- Completion creates one outgoing and one incoming movement.

## Phase 18: Stock Adjustment And Damage

Goal: implement stock corrections and damaged stock handling.

Deliverables:

- Stock adjustment
- Damage stock workflow
- Inventory loss accounting

Independent Tests:

- Adjustment creates difference movement.
- Damage stock decreases inventory.
- Missing adjustment account blocks posting.

## Phase 19: Purchase Requisition

Goal: implement internal purchase request workflow.

Deliverables:

- Requisition header
- Requisition items
- Submit and approval workflow

Independent Tests:

- Requisition requires items before submit.
- Approval permission is enforced.
- No inventory or accounting impact occurs.

## Phase 20: RFQ

Goal: implement request for quotation workflow.

Deliverables:

- RFQ header
- RFQ items
- Supplier selection
- RFQ approval

Independent Tests:

- RFQ requires supplier and items.
- Inactive supplier is blocked.
- RFQ does not affect stock/accounting.

## Phase 21: Purchase Order

Goal: implement purchase commitment workflow.

Deliverables:

- Purchase order
- Purchase order items
- Approval workflow
- Print layout

Independent Tests:

- Totals calculate correctly.
- Approved PO can be used for receipt.
- PO does not affect stock/accounting.

## Phase 22: Goods Receive

Goal: implement GRN and inventory receipt.

Deliverables:

- Purchase receipt
- Receipt items
- Completion workflow
- Positive stock movements

Independent Tests:

- Receipt increases stock.
- Over-receipt is blocked unless allowed.
- Completed receipt cannot complete again.

## Phase 23: Purchases

Goal: implement supplier invoice/bill workflow.

Deliverables:

- Purchase header
- Purchase items
- Approval and completion
- Accounts payable posting
- Purchase print layout

Independent Tests:

- Purchase totals are correct.
- Completion posts balanced journal.
- Closed period blocks posting.

## Phase 24: Purchase Return

Goal: implement supplier return workflow.

Deliverables:

- Purchase return header
- Return items
- Stock decrease
- Accounting reversal

Independent Tests:

- Return cannot exceed purchased/received quantity.
- Return decreases stock.
- Return posts balanced reversal.

## Phase 25: Sales Quotation

Goal: implement customer quotation workflow.

Deliverables:

- Quotation header
- Quotation items
- Approval workflow
- Print layout

Independent Tests:

- Quotation requires customer and items.
- Quotation does not affect stock/accounting.
- Approved quotation can convert to sales order.

## Phase 26: Sales Order

Goal: implement customer order workflow.

Deliverables:

- Sales order
- Sales order items
- Approval workflow
- Conversion to sale or delivery

Independent Tests:

- Totals calculate correctly.
- Sales order does not post accounting.
- Approved order can convert.

## Phase 27: Sales Invoice

Goal: implement customer invoice workflow.

Deliverables:

- Sale header
- Sale items
- Approval and completion
- Receivable posting
- Invoice print layout

Independent Tests:

- Sale totals are correct.
- Completion posts balanced journal.
- Missing receivable/revenue account blocks posting.

## Phase 28: Delivery

Goal: implement delivery and stock-out workflow.

Deliverables:

- Delivery header
- Delivery items
- Completion workflow
- Negative stock movements

Independent Tests:

- Insufficient stock is blocked.
- Completion decreases stock.
- Completed delivery cannot complete again.

## Phase 29: Sales Return

Goal: implement customer return workflow.

Deliverables:

- Sales return
- Return items
- Stock increase
- Accounting reversal

Independent Tests:

- Return cannot exceed sold/delivered quantity.
- Return increases stock.
- Return posts balanced reversal.

## Phase 30: Customer Payments

Goal: implement receipt from customers.

Deliverables:

- Customer payment
- Payment allocation to sale
- Receipt print
- Accounting posting

Independent Tests:

- Payment cannot exceed invoice balance.
- Payment posts balanced journal.
- Invoice balance updates correctly.

## Phase 31: Supplier Payments

Goal: implement payments to suppliers.

Deliverables:

- Supplier payment
- Payment allocation to purchase
- Payment voucher print
- Accounting posting

Independent Tests:

- Payment cannot exceed purchase balance.
- Payment posts balanced journal.
- Purchase balance updates correctly.

## Phase 32: Expense Entry

Goal: implement non-inventory expenses.

Deliverables:

- Expense entry
- Approval workflow
- Accounting posting
- Receipt attachment

Independent Tests:

- Expense requires account and amount.
- Completion posts balanced journal.
- Closed period blocks posting.

## Phase 33: Income Entry

Goal: implement non-sales income.

Deliverables:

- Income entry
- Approval workflow
- Accounting posting

Independent Tests:

- Income requires account and amount.
- Completion posts balanced journal.
- Income does not create inventory effect.

## Phase 34: POS Foundation

Goal: implement POS terminal and shift control.

Deliverables:

- POS terminals
- Open shift
- Close shift
- Cash count

Independent Tests:

- User cannot sell without open shift.
- One active shift per terminal rule is enforced.
- Shift close records expected vs counted cash.

## Phase 35: POS Sale

Goal: implement completed retail sale workflow.

Deliverables:

- POS sale screen
- Cart
- Barcode/SKU search
- Payment
- Receipt print
- Stock movement
- Accounting posting

Independent Tests:

- Sale requires open shift.
- Payment total must match sale rules.
- Stock decreases.
- Journal balances.

## Phase 36: Courier Providers

Goal: implement courier provider setup.

Deliverables:

- Courier providers
- Provider credentials screen
- Test connection action

Independent Tests:

- Provider code unique per company.
- Inactive provider cannot be used.
- Credential changes are audited.

## Phase 37: Courier Shipments

Goal: implement courier booking and tracking.

Deliverables:

- Shipment booking
- Tracking number storage
- Label print action
- Retry sync

Independent Tests:

- Shipment requires delivery or sale reference.
- Failed booking is logged.
- Retry is permission controlled.

## Phase 38: E-commerce Channels

Goal: implement e-commerce channel setup.

Deliverables:

- Channels
- Credentials/configuration
- Test connection

Independent Tests:

- Channel name unique per company.
- Inactive channel cannot sync.
- Failed test is logged.

## Phase 39: E-commerce Product Mapping

Goal: map external products to ERP variants.

Deliverables:

- Product mapping list
- Map/remap workflow
- External SKU handling

Independent Tests:

- One external product maps uniquely per channel.
- Mapping requires product variant.
- Remap is audited.

## Phase 40: E-commerce Orders

Goal: import and convert e-commerce orders.

Deliverables:

- Imported orders
- Order items
- Convert to sale
- Failed import handling

Independent Tests:

- Duplicate external order is blocked.
- Unmapped product blocks conversion.
- Converted order links to sale.

## Phase 41: Employee Master Data

Goal: implement employee records.

Deliverables:

- Departments
- Designations
- Employees
- User-to-employee link

Independent Tests:

- Employee number unique per company.
- Employee can link to one user.
- Inactive employee excluded from payroll generation.

## Phase 42: Attendance

Goal: implement attendance management.

Deliverables:

- Daily attendance
- Attendance import
- Attendance correction audit

Independent Tests:

- One attendance per employee per date.
- Inactive employee is blocked.
- Completed payroll blocks normal attendance edits.

## Phase 43: Payroll

Goal: implement payroll calculation and posting.

Deliverables:

- Payroll periods
- Payroll generation
- Payroll items
- Approval workflow
- Accounting posting

Independent Tests:

- Duplicate payroll per employee/period is blocked.
- Payroll posts balanced journal.
- Missing salary account blocks completion.

## Phase 44: Document Management

Goal: implement document library and attachments.

Deliverables:

- Folders
- Upload
- Attach to business records
- Preview/download

Independent Tests:

- Document belongs to company.
- User cannot access another company's document.
- Attachment appears on related record.

## Phase 45: Dashboard KPIs

Goal: implement real-time dashboard widgets.

Deliverables:

- Sales KPIs
- Purchase KPIs
- Inventory KPIs
- Accounting KPIs
- HR KPIs
- Alerts

Independent Tests:

- Widgets respect permissions.
- Widgets respect company/branch/warehouse filters.
- KPIs match transactional data.

## Phase 46: Reports Foundation

Goal: implement report center and shared report UI.

Deliverables:

- Report center
- Filter panel
- Export controls
- Print controls

Independent Tests:

- Report access is permission controlled.
- Filters apply correctly.
- Exports respect permissions.

## Phase 47: Sales And Purchase Reports

Goal: implement operational sales and purchase reports.

Deliverables:

- Sales summary
- Sales by customer
- Sales by product variant
- Receivables aging
- Purchase summary
- Purchases by supplier
- Payables aging

Independent Tests:

- Reports match transactional documents.
- Date filters are correct.
- Soft-deleted records are excluded by default.

## Phase 48: Inventory Reports

Goal: implement inventory reporting.

Deliverables:

- Stock on hand
- Stock movement ledger
- Low stock
- Inventory valuation
- Transfer report
- Adjustment report

Independent Tests:

- Stock on hand matches movement sum.
- Warehouse filters are correct.
- Valuation uses approved cost basis.

## Phase 49: Financial Reports

Goal: implement accounting reports.

Deliverables:

- Trial balance
- General ledger
- Profit and loss
- Balance sheet
- Cash/bank book
- Tax summary

Independent Tests:

- Trial balance balances.
- P&L uses posted journals only.
- General ledger ties to journal lines.

## Phase 50: HR And Integration Reports

Goal: implement HR and integration reports.

Deliverables:

- Employee list
- Attendance summary
- Payroll summary
- Courier shipment status
- E-commerce sync report
- Failed syncs report

Independent Tests:

- HR reports respect branch filters.
- Failed sync reports match logged failures.
- Payroll summary matches completed payroll.

## Phase 51: Global Search

Goal: implement permission-aware search.

Deliverables:

- Global search bar
- Module-grouped results
- Record quick open

Independent Tests:

- Search returns only permitted records.
- Search respects company context.
- UUID links open correct records.

## Phase 52: Notifications And Alerts

Goal: complete notification center and operational alerts.

Deliverables:

- Notification center
- Toast messages
- Approval alerts
- Low stock alerts
- Failed sync alerts
- Overdue invoice/payable alerts

Independent Tests:

- Notifications are company-scoped.
- Users only receive permitted notifications.
- Alerts link to correct records.

## Phase 53: API V1 Foundation

Goal: expose stable versioned API foundation.

Deliverables:

- `/api/v1` structure
- Authentication
- Standard response format
- UUID route binding
- Rate limiting

Independent Tests:

- API requires authentication.
- API uses UUID public identifiers.
- Error responses are consistent.

## Phase 54: API V1 Business Endpoints

Goal: expose approved module APIs incrementally.

Deliverables:

- Customer API
- Supplier API
- Product/variant API
- Inventory read API
- Sales API
- Purchase API
- Payment API

Independent Tests:

- Each endpoint respects permissions.
- Each endpoint respects company context.
- API workflows match Blade workflows.

## Phase 55: Import And Export

Goal: implement approved import/export flows.

Deliverables:

- Customer import/export
- Supplier import/export
- Product import/export
- Report export
- Error download

Independent Tests:

- Invalid rows are reported.
- Imports are company-scoped.
- Exports respect permissions.

## Phase 56: Security Hardening

Goal: review and harden security.

Deliverables:

- Permission audit
- Tenant isolation audit
- Rate limit review
- Sensitive action audit review
- Secure headers review

Independent Tests:

- Cross-company access attempts fail.
- Unauthorized actions fail.
- Sensitive actions are audited.

## Phase 57: Performance Hardening

Goal: optimize high-volume workflows.

Deliverables:

- Query review
- Index review
- Report performance review
- Queue performance review
- Pagination review

Independent Tests:

- High-volume list pages stay responsive.
- Reports run within accepted limits.
- Queue jobs process reliably.

## Phase 58: Backup, Recovery, And Operations

Goal: prepare production operations.

Deliverables:

- Backup plan
- Restore process
- Queue worker plan
- Scheduler plan
- Log and alert plan

Independent Tests:

- Backup restore is documented and tested.
- Failed jobs can be retried.
- Scheduler tasks are visible.

## Phase 59: Final UAT Preparation

Goal: prepare full system for business acceptance testing.

Deliverables:

- Demo data
- UAT checklist
- Role-based test scripts
- Known limitations list

Independent Tests:

- Admin UAT script passes.
- Sales UAT script passes.
- Purchase UAT script passes.
- Inventory UAT script passes.
- Accounting UAT script passes.

## Phase 60: Production Readiness Review

Goal: confirm the ERP is ready for production deployment.

Deliverables:

- Final security review
- Final performance review
- Final data integrity review
- Deployment checklist
- Rollback checklist

Independent Tests:

- Full test suite passes.
- Critical workflows pass end-to-end.
- Deployment and rollback process are verified.

## Approval Rule

Every phase must stop after completion and wait for approval before the next phase begins.
