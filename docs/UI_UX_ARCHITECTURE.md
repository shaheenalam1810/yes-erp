# UI/UX Architecture

## Scope

This document defines the UI/UX architecture for the approved SME Business ERP modules only:

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

This is a design document only. It does not include Laravel code, Blade templates, HTML, or CSS.

## UX Principles

- The ERP should feel operational, fast, and clear rather than decorative.
- Screens should prioritize scanning, filtering, comparing, and repeated business actions.
- Every module should follow consistent list, create, view, edit, approval, print, and audit patterns.
- Users should never see navigation or actions they do not have permission to use.
- Destructive actions should require confirmation.
- Business transactions should clearly show workflow status: `draft`, `pending`, `approved`, `completed`, `cancelled`.
- Public-facing references should display UUID-backed document links, while users see friendly document numbers.

## Application Shell

### Sidebar Navigation

The sidebar is the primary navigation for desktop and tablet.

Logical groups:

#### Main

- Dashboard
- POS

#### Business Partners

- Customers
- Suppliers

#### Products & Inventory

- Products
- Inventory

#### Buying & Selling

- Purchases
- Sales
- Returns
- Courier
- E-commerce

#### Finance

- Accounting
- Payments
- Reports

#### People

- HR & Payroll

#### Documents

- Document Management

#### Administration

- Multi Business
- Users & Roles
- Settings

Sidebar behavior:

- Collapsible on desktop.
- Drawer on mobile.
- Active item highlights the current module and page.
- Module groups are collapsible.
- Hidden modules or pages are removed based on permission.
- Badge counters may appear for pending approvals, low stock, unpaid invoices, failed syncs, or pending payroll.

### Top Navigation Bar

Purpose: global context, quick actions, and user controls.

Main sections:

- Company selector
- Branch selector
- Warehouse selector, visible when inventory context applies
- Global search
- Quick create menu
- Notifications
- Theme toggle
- User menu

Buttons:

- Quick Create
- Notifications
- Help
- Profile
- Logout

User menu:

- My Profile
- Change Password
- My Activity
- Switch Company
- Logout

Context rules:

- Company selector appears when user belongs to multiple companies.
- Branch selector appears when selected company has multiple branches.
- Warehouse selector appears on inventory, purchase receipt, sales delivery, POS, and stock screens.
- Current context must be visible at all times.

### Breadcrumb System

Breadcrumbs appear below the top bar on internal pages.

Pattern:

```text
Dashboard / Module / List
Dashboard / Sales / Sale #INV-0001
Dashboard / Products / Variant / Edit
```

Rules:

- Breadcrumbs use business names and document numbers.
- Current page is not clickable.
- Permission checks apply to each breadcrumb link.

## Dashboard Layout

Dashboard purpose: provide real-time operational visibility.

Main sections:

- KPI strip
- Sales and purchase trend
- Cash and receivables summary
- Inventory health
- Pending approvals
- Recent activity
- Alerts
- Quick actions

Dashboard widgets:

- Today Sales
- Today Purchases
- Cash/Bank Balance
- Receivables
- Payables
- Low Stock Products
- Inventory Value
- Pending Sales
- Pending Purchases
- Pending Payments
- Pending Payroll
- Failed Courier Syncs
- Failed E-commerce Syncs
- Recent Audit Activity

Filters:

- Date range
- Company
- Branch
- Warehouse
- Currency

Actions:

- Create Sale
- Create Purchase
- Open POS
- Add Customer
- Add Product
- View Reports

Empty state:

- New company dashboard shows setup checklist: company settings, branch, warehouse, chart of accounts, products, users.

## Global UI Components

### Page Header

Purpose: consistent title, context, and page actions.

Contains:

- Page title
- Subtitle or document number
- Status badge
- Breadcrumbs
- Primary action button
- Secondary action menu

### Status Badge

Used for workflow status:

- Draft
- Pending
- Approved
- Completed
- Cancelled

Rules:

- Status color is consistent across all modules.
- Cancelled and completed records are read-only unless permissions allow reversal workflows.

### Action Bar

Contains common actions:

- Create
- Edit
- Save
- Submit for Approval
- Approve
- Complete
- Cancel
- Delete
- Restore
- Print
- Export
- Attach Document
- View Audit Log

### Reusable Table

Used for list screens and line-item sections.

Features:

- Search
- Sort
- Filters
- Column visibility
- Bulk select
- Pagination
- Row actions
- Status badges
- Empty state
- Loading skeleton

Common row actions:

- View
- Edit
- Duplicate
- Print
- Export
- Delete
- Restore
- View Audit

### Reusable Form

Used for create and edit screens.

Common sections:

- Basic Information
- Contact or Address
- Financial Settings
- Inventory Settings
- Documents
- Notes
- Audit Summary

Common behavior:

- Required fields marked clearly.
- Inline validation.
- Unsaved changes warning.
- Autosave only for drafts if approved later.
- Save and Continue option for multi-section forms.

### Modal Dialogs

Global modals:

- Confirm Delete
- Confirm Cancel
- Confirm Approval
- Confirm Completion
- Restore Record
- Attach Document
- Add Note
- Import File
- Export Options
- Column Settings
- Filter Builder
- View Audit Details
- Payment Allocation
- Product Variant Picker
- Customer Quick Create
- Supplier Quick Create
- Low Stock Warning
- Insufficient Stock Warning

Rules:

- Modals should be focused and short.
- Complex workflows should use full pages, not modals.
- Destructive modals require clear confirmation.

### Notification System

Notification types:

- Success
- Info
- Warning
- Error
- Approval request
- Integration failure
- Low stock
- Payment received
- Payroll completed

Locations:

- Toast for immediate feedback
- Notification center for persistent notices
- Dashboard alerts for high-priority operational issues

Actions:

- Mark as read
- Open related record
- Dismiss
- Retry failed sync, when allowed

### Alert System

Alert types:

- Low stock
- Negative stock attempt
- Overdue invoice
- Overdue payable
- Failed e-commerce sync
- Failed courier sync
- Unbalanced journal
- Closed accounting period
- Missing default account
- Permission denied

Alert behavior:

- Critical alerts block completion.
- Warnings allow continuation only if permission allows override.
- Informational alerts do not block workflow.

### Search System

Global search should find:

- Customers
- Suppliers
- Products
- Product variants
- Sales
- Purchases
- Payments
- Journal entries
- Employees
- Documents

Search result layout:

- Grouped by module
- Shows record type, name, document number, status, and company context
- Permission-filtered results only

Page search:

- Table keyword search
- Barcode/SKU search in POS, products, purchases, sales, and inventory
- Document number search in transaction modules

### Global Filters

Common filters:

- Company
- Branch
- Warehouse
- Date range
- Status
- Customer
- Supplier
- Product variant
- User
- Currency

Rules:

- Filters persist per user and page.
- Reset filters button is always available.
- Saved filter views may be added later.

## Permission-Based Navigation

Navigation is built from permissions.

Examples:

- Users without `sales.view` do not see Sales.
- Users with `sales.view` but not `sales.create` see the sales list without Create button.
- Users without `accounting.view` cannot access accounting reports.
- Users without `settings.update` see settings in read-only mode or not at all based on policy.

Permission checks apply to:

- Sidebar items
- Top bar actions
- Page buttons
- Row actions
- Bulk actions
- Report filters
- Export and print buttons
- Dashboard widgets

## Mobile Responsive Behavior

Desktop:

- Persistent sidebar.
- Wide tables.
- Multi-column forms.
- Split view where useful.

Tablet:

- Collapsible sidebar.
- Forms reduce to two columns.
- Tables support horizontal scroll and column hiding.

Mobile:

- Sidebar becomes drawer.
- Top bar shows compact context selector.
- Tables become stacked cards for critical pages.
- Primary action becomes fixed bottom button when appropriate.
- POS layout remains touch-first with large controls.

## Dark Mode Support

Dark mode must support:

- Dashboard widgets
- Tables
- Forms
- Modals
- POS
- Charts
- Invoice preview
- Report screens

Rules:

- Status colors remain distinguishable.
- Error and warning states remain high contrast.
- Print layouts stay light by default.

## Loading, Empty, And Error States

Loading states:

- Skeleton rows for tables.
- Skeleton cards for dashboard widgets.
- Button spinner for submit actions.
- Full-page loading only for initial module load.

Empty states:

- No records found
- No results match filters
- No permissions
- No company selected
- No warehouse selected
- No stock movement history
- No documents attached

Error states:

- Validation errors inline.
- Permission denied page.
- Record not found page.
- Server error page.
- Integration failed page section.
- Posting failed message with reason.

Error pages:

- 401 Unauthorized
- 403 Forbidden
- 404 Not Found
- 419 Session Expired
- 422 Validation Failed
- 429 Too Many Requests
- 500 Server Error
- Maintenance Mode

## Module Pages And Flows

### Dashboard

#### Dashboard Home

Purpose: real-time operational overview.

Main sections: KPI cards, charts, pending work, alerts, quick actions, recent activity.

Buttons: Create Sale, Create Purchase, Open POS, Add Customer, Add Product, View Reports.

Tables: pending approvals, recent transactions, low-stock products.

Forms: dashboard filter panel.

Filters: company, branch, warehouse, date range, currency.

Actions: open widget detail, export visible widget where permitted.

User flow: user logs in, selects company context, reviews KPIs, opens a pending item or starts a transaction.

### Multi Business

#### Companies List

Purpose: manage companies available to the user.

Sections: company table, status summary.

Buttons: Add Company, Export.

Tables: companies with name, currency, timezone, status.

Filters: status, currency.

Actions: view, edit, switch, deactivate.

#### Company Create/Edit

Purpose: configure company profile.

Sections: legal details, currency, timezone, tax info, logo, address.

Buttons: Save, Cancel.

Forms: company profile form.

#### Branches

Purpose: manage company branches.

Sections: branch table, branch form.

Buttons: Add Branch.

Actions: view, edit, deactivate, restore.

#### Warehouses

Purpose: manage warehouses and branch warehouse mapping.

Sections: warehouse table, warehouse form.

Buttons: Add Warehouse.

Actions: view, edit, set default, deactivate.

User flow: create company, add branches, add warehouses, choose defaults.

### User & Role Management

#### Users List

Purpose: manage users and access.

Sections: user table, invitation status.

Buttons: Add User, Invite User, Export.

Tables: users with name, email, roles, branch, status, last login.

Filters: role, branch, status.

Actions: view, edit, activate, deactivate, reset password, view audit.

#### User Create/Edit

Purpose: create or update user profile and access.

Sections: profile, company access, branch access, roles.

Buttons: Save, Cancel.

Forms: user details, role assignment.

#### Roles List

Purpose: manage company roles.

Sections: role table.

Buttons: Add Role.

Actions: view, edit, duplicate, delete.

#### Role Create/Edit

Purpose: define permissions.

Sections: role info, permission matrix by module.

Buttons: Save, Cancel.

Forms: role name and permission checkboxes.

Modals: permission change confirmation.

User flow: admin creates role, assigns permissions, assigns users.

### Customer (CRM)

#### Customers List

Purpose: manage customer master records.

Sections: customer table, quick filters.

Buttons: Add Customer, Import, Export.

Tables: customer code, name, phone, email, branch, balance, status.

Filters: branch, status, customer type.

Actions: view, edit, create sale, receive payment, attach document, delete.

#### Customer Create/Edit

Purpose: maintain customer details.

Sections: basic info, contacts, addresses, financial defaults, notes.

Buttons: Save, Save & Create Sale, Cancel.

Forms: customer profile, contact rows, address rows.

#### Customer View

Purpose: show complete customer profile.

Sections: summary, sales, payments, returns, documents, activities, audit.

Buttons: Edit, New Sale, Receive Payment, Add Activity, Attach Document.

Tables: sales history, payment history, activity log.

User flow: add customer, record activity, create sale, collect payment.

### Supplier Management

#### Suppliers List

Purpose: manage supplier records.

Buttons: Add Supplier, Import, Export.

Tables: supplier code, name, phone, email, branch, payable balance, status.

Filters: branch, status, supplier type.

Actions: view, edit, create purchase, make payment, attach document, delete.

#### Supplier Create/Edit

Sections: basic info, contacts, addresses, payment terms, notes.

Buttons: Save, Save & Create Purchase, Cancel.

#### Supplier View

Sections: summary, purchases, payments, returns, documents, audit.

Buttons: Edit, New Purchase, Make Payment, Attach Document.

User flow: add supplier, create purchase, receive goods, pay supplier.

### Product Management

#### Products List

Purpose: manage product families and variants.

Buttons: Add Product, Import, Export.

Tables: product name, category, brand, variant count, status.

Filters: category, brand, status, product type.

Actions: view, edit, add variant, delete.

#### Product Create/Edit

Sections: product info, category, brand, base unit, description, images.

Buttons: Save, Add Variant, Cancel.

#### Product View

Sections: product summary, variants, documents, audit.

Tables: variants with SKU, barcode, cost, selling price, stock status.

Actions: edit product, create variant, view stock history.

#### Variant Create/Edit

Purpose: manage sellable or stockable variants.

Sections: SKU/barcode, attributes, unit, cost price, selling price, stock tracking.

Buttons: Save, Cancel.

Forms: variant form, attribute picker.

#### Categories, Brands, Units, Attributes

Purpose: maintain product classification and variant attributes.

Screens: list, create, edit, view.

User flow: create category and unit, create product, create variants, use variant in purchase, sale, inventory, and POS.

### Inventory Management

#### Stock Movement History

Purpose: source-of-truth inventory ledger.

Tables: date, movement number, variant, warehouse, direction, quantity, unit cost, source, user.

Filters: warehouse, variant, movement type, date range, source.

Actions: view source document, export.

#### Stock On Hand

Purpose: calculated inventory view from movements.

Tables: variant, SKU, warehouse, quantity on hand, reserved, available, value.

Filters: warehouse, category, brand, low stock.

Actions: view movement history, adjust stock, transfer stock.

#### Stock Transfer List/Create/View

Purpose: move stock between warehouses.

Sections: header, transfer items, approval/status, audit.

Buttons: Create Transfer, Save Draft, Submit, Approve, Complete, Cancel.

Tables: transfer list and transfer items.

Forms: source warehouse, destination warehouse, variant picker, quantities.

#### Stock Adjustment List/Create/View

Purpose: correct stock differences.

Buttons: Create Adjustment, Save Draft, Submit, Approve, Complete, Cancel.

Forms: warehouse, reason, counted quantities.

User flow: review stock, create transfer or adjustment, approve, complete, stock movements are created.

### Purchase Management

#### Purchase Orders

Purpose: manage supplier purchase commitments.

Buttons: New Purchase Order, Export.

Tables: order number, supplier, date, status, total.

Filters: supplier, status, date range, branch.

Actions: view, edit, approve, convert to purchase, receive goods, cancel.

#### Purchase Order Create/Edit/View

Sections: supplier, branch, currency, items, totals, documents, audit.

Buttons: Save Draft, Submit, Approve, Convert, Cancel, Print.

Forms: header form and line item table.

#### Purchases

Purpose: supplier bills/invoices.

Buttons: New Purchase.

Tables: purchase number, supplier, date, due date, status, total, paid, balance.

Actions: view, edit, approve, record payment, print, cancel.

#### Purchase Create/Edit/View

Sections: supplier, warehouse, currency, items, totals, payment summary, journal summary.

Buttons: Save Draft, Submit, Approve, Complete, Record Payment, Print.

#### Purchase Receipts

Purpose: receive goods into stock.

Buttons: New Receipt.

Tables: receipt number, supplier, warehouse, status, date.

Actions: view, edit, approve, complete.

User flow: purchase order, receipt goods, create purchase bill, post accounting, pay supplier.

### Sales Management

#### Sales Orders

Purpose: manage customer sales commitments.

Buttons: New Sales Order, Export.

Tables: order number, customer, date, status, total.

Filters: customer, status, date range, branch.

Actions: view, edit, approve, convert to sale, create delivery, cancel.

#### Sales

Purpose: customer invoices.

Buttons: New Sale, Export.

Tables: sale number, customer, sale date, due date, status, total, paid, balance.

Filters: customer, status, date range, branch.

Actions: view, edit, approve, complete, receive payment, print invoice, send to courier, cancel.

#### Sale Create/Edit/View

Sections: customer, warehouse, currency, items, totals, payment summary, delivery status, journal summary, documents, audit.

Buttons: Save Draft, Submit, Approve, Complete, Receive Payment, Print, Cancel.

Forms: header and sale item table.

#### Deliveries

Purpose: manage shipment of sold stock.

Buttons: New Delivery.

Tables: delivery number, customer, warehouse, status, date.

Actions: view, edit, complete, create courier shipment.

#### Returns

Purpose: manage sales and purchase returns.

Buttons: New Return.

Tables: return number, type, customer/supplier, status, total.

Actions: view, edit, approve, complete, print.

User flow: create sales order, convert to sale, deliver goods, post accounting, receive payment, handle returns if needed.

### POS

#### POS Terminal Screen

Purpose: fast retail checkout.

Layout:

- Left: product search, barcode input, category shortcuts, product grid.
- Center: cart line items.
- Right: customer, totals, discounts, tax, payment panel.
- Top: branch, warehouse, terminal, cashier, shift status.
- Bottom: hold, recall, clear, pay, print receipt.

Buttons:

- Open Shift
- Close Shift
- Add Customer
- Hold Sale
- Recall Sale
- Apply Discount
- Pay
- Print Receipt
- Cancel Sale

Tables:

- Cart items
- Held sales
- Recent receipts

Forms:

- Barcode/SKU search
- Payment form
- Customer quick create
- Close shift cash count

Modals:

- Open shift
- Close shift
- Payment
- Split payment
- Discount
- Hold sale
- Recall sale
- Insufficient stock
- Receipt preview

#### POS Sales History

Purpose: review POS transactions.

Tables: receipt number, cashier, shift, total, payment, status.

Filters: terminal, cashier, shift, date range, status.

Actions: view, print receipt, refund where approved.

User flow: cashier opens shift, scans items, takes payment, completes sale, prints receipt, closes shift.

### Accounting

#### Chart Of Accounts

Purpose: manage accounts and hierarchy.

Buttons: Add Account, Import, Export.

Tables: code, name, type, normal balance, active.

Actions: view, edit, deactivate.

#### Journals

Purpose: manage journal types.

Screens: list, create, edit.

#### Journal Entries

Purpose: review and create manual journals.

Buttons: New Journal Entry.

Tables: journal number, date, journal, status, debit, credit.

Filters: journal, account, status, date range.

Actions: view, edit draft, approve, post, cancel.

Forms: journal header and balanced debit/credit line table.

#### Fiscal Years And Periods

Purpose: manage posting periods.

Actions: create, close period, reopen with permission.

#### Customer Payments

Purpose: record receipts.

Screens: list, create, view.

Actions: allocate to sale, approve, complete, print receipt.

#### Supplier Payments

Purpose: record supplier payments.

Screens: list, create, view.

Actions: allocate to purchase, approve, complete, print voucher.

User flow: configure accounts, post business documents, review journals, receive and make payments, run financial reports.

### Courier Integration

#### Courier Providers

Purpose: manage courier services.

Screens: list, create, edit, view.

Actions: activate, deactivate, test connection.

#### Shipments

Purpose: track delivery shipments.

Tables: shipment number, tracking number, courier, sale/delivery, status, cost.

Filters: courier, status, date range.

Actions: create shipment, print label, track, retry sync, cancel.

User flow: create delivery, choose courier, create shipment, track status.

### E-commerce Integration

#### Channels

Purpose: manage online store connections.

Screens: list, create, edit, view.

Actions: activate, deactivate, sync products, sync orders, test connection.

#### Product Mappings

Purpose: map external products to ERP variants.

Tables: channel, external SKU, ERP SKU, status.

Actions: map, remap, unmap.

#### E-commerce Orders

Purpose: review imported orders and conversion status.

Tables: external order, channel, customer, total, status, sale link.

Actions: view, convert to sale, retry import, ignore.

User flow: connect channel, map variants, import orders, convert to sale and delivery.

### HR & Payroll

#### Employees

Purpose: manage employee master data.

Buttons: Add Employee, Import, Export.

Tables: employee number, name, branch, department, designation, status.

Actions: view, edit, deactivate, link user.

#### Departments And Designations

Purpose: maintain HR master data.

Screens: list, create, edit.

#### Attendance

Purpose: manage daily attendance.

Buttons: Mark Attendance, Import.

Tables: employee, date, status, check in, check out.

Filters: branch, department, date.

#### Payroll Periods

Purpose: define payroll cycles.

Screens: list, create, edit, close.

#### Payroll

Purpose: calculate and post employee payroll.

Tables: period, employee, gross, deductions, net, status.

Actions: generate, review, approve, complete, post accounting.

User flow: add employees, mark attendance, generate payroll, approve, complete, post accounting.

### Reports

Report screens use transactional data by default.

#### Report Center

Purpose: central list of available reports.

Sections: report categories, favorites, recent reports.

Filters: module, favorite.

Actions: open report, export if permitted.

#### Sales Reports

Reports:

- Sales Summary
- Sales By Customer
- Sales By Product Variant
- Receivables Aging
- Sales Return Report

Filters: date range, branch, customer, product variant, status, currency.

#### Purchase Reports

Reports:

- Purchase Summary
- Purchases By Supplier
- Payables Aging
- Purchase Return Report

#### Inventory Reports

Reports:

- Stock On Hand
- Stock Movement Ledger
- Low Stock
- Inventory Valuation
- Transfer Report
- Adjustment Report

#### Accounting Reports

Reports:

- Trial Balance
- General Ledger
- Profit & Loss
- Balance Sheet
- Cash/Bank Book
- Tax Summary

#### HR Reports

Reports:

- Employee List
- Attendance Summary
- Payroll Summary

#### Integration Reports

Reports:

- Courier Shipment Status
- E-commerce Order Sync
- Failed Syncs

Report screen sections:

- Filter panel
- Report table
- Summary totals
- Export actions
- Print action

User flow: choose report, apply filters, review real-time data, export or print if permitted.

### Document Management

#### Document Library

Purpose: browse and manage company files.

Sections: folder tree, document table, preview panel.

Buttons: Upload, New Folder.

Filters: folder, file type, related module, uploaded by.

Actions: preview, download, attach, rename, move, delete.

#### Document Upload

Purpose: upload and tag documents.

Forms: file upload, folder, related record, tags, notes.

Modals: attach to record, move folder, delete confirmation.

User flow: upload file, attach to business record, preview from related module.

### Settings

#### Company Settings

Purpose: configure company defaults.

Sections: profile, currency, timezone, formats, fiscal year.

Buttons: Save.

#### Branch Settings

Purpose: configure branch defaults.

Sections: default warehouse, address, operation settings.

#### Invoice Settings

Purpose: configure document numbers and print behavior.

Sections: prefixes, next numbers, invoice print options.

#### Tax Settings

Purpose: configure tax defaults.

Sections: tax rates, inclusive/exclusive behavior.

#### Accounting Settings

Purpose: configure default accounts.

Sections: receivable, payable, inventory, revenue, expense, cash/bank accounts.

#### POS Settings

Purpose: configure POS defaults.

Sections: receipt prefix, default payment method, negative stock rule.

User flow: admin configures company defaults before business transactions begin.

## Invoice Print Layout

Purpose: clean printable invoice for sales and purchase documents.

Sections:

- Company logo and details
- Invoice title and document number
- Customer or supplier details
- Billing and shipping address
- Invoice date and due date
- Currency
- Line item table
- Subtotal
- Discount
- Tax
- Grand total
- Paid total
- Balance due
- Amount in words
- Terms and notes
- Authorized signature
- QR code or public verification reference, if approved later

Rules:

- Print layout is light mode only.
- Fits A4 by default.
- Repeats table header on page breaks.
- Does not expose internal numeric IDs.

## Reusable CRUD Screen Pattern

List page:

- Header
- Filters
- Search
- Table
- Pagination
- Bulk actions

Create page:

- Header
- Form sections
- Save actions

View page:

- Summary
- Status
- Related tabs
- Audit
- Documents
- Actions

Edit page:

- Same sections as create
- Shows audit and status constraints

Delete flow:

- Confirmation modal
- Reason field for important records
- Soft delete
- Audit log entry

Restore flow:

- Restore confirmation
- Permission check
- Audit log entry

## Approval Gate

This document defines the UI/UX architecture only. No code, templates, HTML, or CSS should be generated from it until the phase is approved.
