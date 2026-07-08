# Business Rules & Workflow Engine

## Scope

This document defines business workflows for the approved SME Business ERP modules only. It is a design document and does not include Laravel code, migrations, models, or SQL.

Standard workflow statuses:

- `draft`
- `pending`
- `approved`
- `completed`
- `cancelled`

Every workflow must respect company context, branch context when applicable, warehouse context when inventory is affected, permissions, audit logging, and database transactions.

## Global Workflow Rules

### Draft Workflow

Purpose: allow users to prepare records before business impact.

Rules:

- Draft records may be edited by authorized users.
- Draft records do not affect accounting.
- Draft records do not affect inventory, except optional non-posting reservations if approved later.
- Draft records may be deleted or cancelled based on permission.
- Draft records must still create audit logs.

Common steps:

1. User creates record.
2. System validates minimum required draft fields.
3. Record is saved as `draft`.
4. Audit log records creation.
5. User may edit, attach documents, add items, or submit for approval.

### Approval Workflow

Purpose: control business impact.

Rules:

- Records submitted for approval move from `draft` to `pending`.
- Approvers must have permission for the module and action.
- Approval moves records to `approved`.
- Completion moves records to `completed` and performs final stock/accounting effects.
- Some workflows may approve and complete in one step if policy allows.

Common steps:

1. Creator submits record.
2. System validates all required business fields.
3. Status becomes `pending`.
4. Approver receives notification.
5. Approver approves or rejects.
6. Approved record becomes eligible for completion or posting.

### Cancellation Workflow

Purpose: stop a workflow before or after approval.

Rules:

- Draft and pending records may usually be cancelled.
- Approved records may be cancelled only if no irreversible downstream document exists.
- Completed records should not be cancelled directly; they require reversal.
- Cancellation requires reason entry for important documents.

Common steps:

1. User requests cancellation.
2. System checks permission and downstream dependencies.
3. User provides cancellation reason.
4. Status becomes `cancelled`.
5. Audit log records old status, new status, and reason.
6. Notifications are sent to relevant users.

### Reversal Workflow

Purpose: correct completed business transactions without deleting history.

Rules:

- Completed financial or inventory transactions must be reversed by new reversing documents.
- Original records remain unchanged.
- Reversal must create audit logs.
- Reversal must create opposite accounting and inventory effects.

Examples:

- Sales return reverses sale inventory/accounting impact.
- Purchase return reverses purchase receipt and payable impact.
- Stock adjustment corrects inventory.
- Reversing journal corrects posted accounting entry.

## Workflow 1: Customer Registration

Preconditions:

- Company context is selected.
- User has customer creation permission.

Validation Rules:

- Customer name is required.
- Customer code must be unique within company.
- Email and phone format must be valid when provided.
- Default currency must exist when selected.

Business Rules:

- Customer may be branch-specific or company-wide.
- Customer cannot be used in sales if inactive.
- Credit limit rules may block sales later.

Database Changes:

- Create `customers`.
- Create `customer_contacts` if provided.
- Create `customer_addresses` if provided.
- Attach documents if uploaded.

Inventory Changes:

- None.

Accounting Impact:

- None during registration.
- Customer becomes available for receivables and payments.

Audit Log:

- Customer create.
- Contact/address create.

Notification:

- Optional notification to sales/admin team.

Permission Required:

- `crm.customers.create`

Possible Error Conditions:

- Duplicate customer code.
- Invalid company or branch.
- Missing required fields.
- Permission denied.

## Workflow 2: Supplier Registration

Preconditions:

- Company context is selected.
- User has supplier creation permission.

Validation Rules:

- Supplier name is required.
- Supplier code must be unique within company.
- Contact details must be valid when provided.

Business Rules:

- Supplier may be branch-specific or company-wide.
- Inactive suppliers cannot be used in purchase workflows.

Database Changes:

- Create `suppliers`.
- Create `supplier_contacts`.
- Create `supplier_addresses`.
- Attach documents if uploaded.

Inventory Changes:

- None.

Accounting Impact:

- None during registration.

Audit Log:

- Supplier create.

Notification:

- Optional notification to purchase/admin team.

Permission Required:

- `suppliers.create`

Possible Error Conditions:

- Duplicate supplier code.
- Invalid branch.
- Permission denied.

## Workflow 3: Product Creation

Preconditions:

- Category, unit, and company context exist.
- User has product creation permission.

Validation Rules:

- Product name is required.
- Product slug must be unique within company.
- Base unit is required.
- Product type must be valid.

Business Rules:

- Product is a parent/family record.
- SKU, barcode, cost, sale price, and stock are managed by variants.
- Product cannot be sold or purchased until at least one active variant exists.

Database Changes:

- Create `products`.
- Attach product documents/images if provided.

Inventory Changes:

- None.

Accounting Impact:

- None.

Audit Log:

- Product create.

Notification:

- Optional product creation notice.

Permission Required:

- `products.create`

Possible Error Conditions:

- Duplicate slug.
- Missing category/unit.
- Permission denied.

## Workflow 4: Product Variant Creation

Preconditions:

- Product exists and is active.
- Unit and currency exist.
- User has variant creation permission.

Validation Rules:

- SKU is required and unique within company.
- Barcode must be unique within company when provided.
- Cost price and selling price must be `DECIMAL(18,2)`.
- Attribute combinations must be unique for the product.

Business Rules:

- Variant is the sellable and stockable unit.
- Inventory transactions always reference product variant.
- Inactive variants cannot be used in new transactions.

Database Changes:

- Create `product_variants`.
- Create `product_variant_values` for selected attributes.

Inventory Changes:

- None unless opening stock is entered separately.

Accounting Impact:

- None.

Audit Log:

- Variant create.

Notification:

- Optional product team notification.

Permission Required:

- `products.variants.create`

Possible Error Conditions:

- Duplicate SKU/barcode.
- Invalid attribute combination.
- Missing currency or unit.

## Workflow 5: Opening Stock

Preconditions:

- Product variants exist.
- Warehouse exists.
- Opening stock date is within allowed period.
- User has inventory opening permission.

Validation Rules:

- Quantity must be greater than or equal to zero.
- Unit cost must be `DECIMAL(18,2)`.
- Variant must be stock-tracked.
- Opening stock should be allowed only once per variant/warehouse unless correction workflow is used.

Business Rules:

- Opening stock creates inventory history.
- Opening stock must not directly update product or variant quantity.
- Source of truth is `stock_movements`.

Database Changes:

- Create stock opening document if represented as adjustment.
- Create positive `stock_movements`.

Inventory Changes:

- Increase stock movement history for variant and warehouse.

Accounting Impact:

- If accounting opening balances are enabled: debit inventory, credit opening balance equity.

Audit Log:

- Opening stock created/completed.

Notification:

- Notify inventory manager when completed.

Permission Required:

- `inventory.opening-stock.create`
- `inventory.opening-stock.complete`

Possible Error Conditions:

- Duplicate opening stock.
- Closed accounting period.
- Missing inventory account.
- Permission denied.

## Workflow 6: Purchase Requisition

Preconditions:

- User has purchase requisition permission.
- Product variants exist.

Validation Rules:

- At least one item is required before submission.
- Quantity must be greater than zero.
- Branch is required.

Business Rules:

- Requisition is internal buying request.
- It does not affect stock or accounting.
- Approved requisition may create RFQ or purchase order.

Database Changes:

- Create requisition header and items.
- Status starts as `draft`, then `pending`, `approved`, or `cancelled`.

Inventory Changes:

- None.

Accounting Impact:

- None.

Audit Log:

- Requisition create, update, submit, approve, cancel.

Notification:

- Notify approver on submit.
- Notify requester on approval/cancellation.

Permission Required:

- `purchases.requisitions.create`
- `purchases.requisitions.approve`

Possible Error Conditions:

- Missing items.
- Inactive variant.
- Unauthorized branch.

## Workflow 7: RFQ

Preconditions:

- Supplier exists.
- Requisition may exist but is optional.
- User has RFQ permission.

Validation Rules:

- At least one supplier and one item are required.
- RFQ date is required.
- Quantity must be greater than zero.

Business Rules:

- RFQ requests supplier pricing.
- RFQ does not affect accounting or inventory.
- Approved RFQ may convert to purchase order.

Database Changes:

- Create RFQ header.
- Create RFQ items.
- Store supplier quotation responses if approved in scope.

Inventory Changes:

- None.

Accounting Impact:

- None.

Audit Log:

- RFQ create, send, update, approve, cancel.

Notification:

- Notify purchase team.

Permission Required:

- `purchases.rfq.create`
- `purchases.rfq.approve`

Possible Error Conditions:

- Supplier inactive.
- Missing items.
- Permission denied.

## Workflow 8: Purchase Order

Preconditions:

- Supplier exists and is active.
- Product variants exist.
- Currency exists.
- User has purchase order permission.

Validation Rules:

- Supplier, branch, currency, and order date are required.
- At least one item is required.
- Quantity must be greater than zero.
- Unit price must be `DECIMAL(18,2)`.
- Totals must equal item calculations.

Business Rules:

- Purchase order records buying commitment.
- It does not increase stock.
- It does not create accounting entries.
- Approved purchase order can be used for GRN and purchase invoice.

Database Changes:

- Create/update `purchase_orders`.
- Create/update `purchase_order_items`.
- Status follows draft, pending, approved, completed, cancelled.

Inventory Changes:

- None.

Accounting Impact:

- None.

Audit Log:

- Purchase order create, update, submit, approve, cancel.

Notification:

- Notify approver on submit.
- Notify requester/supplier contact if configured.

Permission Required:

- `purchases.orders.create`
- `purchases.orders.approve`

Possible Error Conditions:

- Inactive supplier.
- Invalid currency.
- Incorrect totals.
- Closed branch/company.

## Workflow 9: Goods Receive (GRN)

Preconditions:

- Supplier exists.
- Warehouse exists.
- Purchase order or purchase reference exists when required.
- User has GRN permission.

Validation Rules:

- Warehouse is required.
- Received quantity must be greater than zero.
- Received quantity must not exceed ordered quantity unless over-receipt is allowed.
- Product variant must be stock-tracked.

Business Rules:

- GRN increases inventory only when completed.
- Partial receipt is allowed if purchase order remains open.
- GRN cannot be completed twice.

Database Changes:

- Create `purchase_receipts`.
- Create `purchase_receipt_items`.
- On completion, create positive `stock_movements`.

Inventory Changes:

- Increase stock movement history for each variant in receiving warehouse.

Accounting Impact:

- If inventory accounting is posted on receipt: debit inventory, credit goods received not invoiced.
- If accounting is posted on purchase invoice only: no journal at GRN.

Audit Log:

- GRN create, approve, complete.
- Inventory movement logged.

Notification:

- Notify purchase and inventory team.

Permission Required:

- `purchases.receipts.create`
- `purchases.receipts.complete`

Possible Error Conditions:

- Over-receipt blocked.
- Inactive warehouse.
- Duplicate completion.
- Missing inventory account.

## Workflow 10: Purchase Return

Preconditions:

- Completed purchase or GRN exists.
- Supplier exists.
- Stock is available for return.

Validation Rules:

- Return quantity must be greater than zero.
- Return quantity cannot exceed received/purchased quantity minus prior returns.
- Warehouse is required.

Business Rules:

- Purchase return decreases stock.
- Purchase return reverses payable or creates supplier credit.
- Completed return creates stock and accounting reversal.

Database Changes:

- Create `returns` with return type `purchase`.
- Create `return_items`.
- Create negative `stock_movements` on completion.

Inventory Changes:

- Decrease stock from warehouse.

Accounting Impact:

- Debit accounts payable or supplier credit.
- Credit inventory or purchase expense.
- Reverse tax where applicable.

Audit Log:

- Purchase return create, approve, complete.

Notification:

- Notify purchase, inventory, and accounting.

Permission Required:

- `purchases.returns.create`
- `purchases.returns.approve`
- `purchases.returns.complete`

Possible Error Conditions:

- Insufficient stock.
- Return exceeds received quantity.
- Closed accounting period.

## Workflow 11: Sales Quotation

Preconditions:

- Customer exists or quick customer creation is permitted.
- Product variants exist.

Validation Rules:

- Customer, branch, currency, and quotation date are required.
- At least one item is required.
- Quantity and prices must be valid.

Business Rules:

- Quotation is non-posting.
- It does not reserve stock unless reservation is approved later.
- Approved quotation may convert to sales order.

Database Changes:

- Create quotation header and items if quotation tables are approved under sales workflow.
- Otherwise record as sales order draft with quotation type if chosen later.

Inventory Changes:

- None.

Accounting Impact:

- None.

Audit Log:

- Quotation create, update, approve, cancel.

Notification:

- Notify sales team or customer if sending is enabled.

Permission Required:

- `sales.quotations.create`
- `sales.quotations.approve`

Possible Error Conditions:

- Inactive customer.
- Invalid price.
- Permission denied.

## Workflow 12: Sales Order

Preconditions:

- Customer exists and is active.
- Product variants exist.
- User has sales order permission.

Validation Rules:

- Customer, branch, currency, and order date are required.
- At least one item is required.
- Quantity must be greater than zero.
- Totals must match line calculations.

Business Rules:

- Sales order records commitment.
- It does not create accounting entries.
- Stock reservation may be added later if approved.
- Approved sales order may create sale invoice and delivery.

Database Changes:

- Create `sales_orders`.
- Create `sales_order_items`.

Inventory Changes:

- None by default.

Accounting Impact:

- None.

Audit Log:

- Sales order create, update, approve, cancel.

Notification:

- Notify approver.
- Notify sales user after approval.

Permission Required:

- `sales.orders.create`
- `sales.orders.approve`

Possible Error Conditions:

- Customer inactive.
- Invalid line totals.
- Credit hold if policy enabled.

## Workflow 13: Sales Invoice

Preconditions:

- Customer exists and is active.
- Product variants exist.
- Currency exists.
- User has sales invoice permission.

Validation Rules:

- Customer, branch, sale date, currency, and at least one item are required.
- Unit prices and totals must be valid.
- Due date must be equal to or after sale date.
- Warehouse is required if sale also delivers stock.

Business Rules:

- Invoice creates receivable when approved or completed based on posting policy.
- Invoice does not reduce stock unless linked delivery or direct invoice-delivery workflow is used.
- Completed invoice cannot be edited; reversal or credit note/return is required.

Database Changes:

- Create `sales`.
- Create `sale_items`.
- Create journal entry and lines when posted.

Inventory Changes:

- None unless direct sale completion also creates delivery stock movements.

Accounting Impact:

- Debit accounts receivable.
- Credit revenue.
- Credit tax payable where applicable.

Audit Log:

- Invoice create, update, approve, complete, print.
- Accounting posting audit.

Notification:

- Notify accounting on posting.
- Notify customer if invoice sending is enabled.

Permission Required:

- `sales.invoices.create`
- `sales.invoices.approve`
- `sales.invoices.complete`

Possible Error Conditions:

- Invalid totals.
- Missing receivable/revenue/tax account.
- Closed accounting period.
- Customer credit limit exceeded.

## Workflow 14: Sales Return

Preconditions:

- Completed sale or delivery exists.
- Customer exists.
- Warehouse exists.

Validation Rules:

- Return quantity must be greater than zero.
- Return quantity cannot exceed sold/delivered quantity minus prior returns.
- Return reason is required.

Business Rules:

- Sales return increases stock when goods are returned.
- Sales return reverses receivable, revenue, tax, COGS, and inventory as needed.
- Completed return cannot be edited.

Database Changes:

- Create `returns` with return type `sales`.
- Create `return_items`.
- Create positive `stock_movements`.
- Create reversal journal entry.

Inventory Changes:

- Increase stock for returned variants.

Accounting Impact:

- Debit sales return/revenue reversal.
- Debit tax payable reversal where applicable.
- Credit accounts receivable or customer credit.
- Debit inventory and credit COGS for returned stock where applicable.

Audit Log:

- Sales return create, approve, complete.

Notification:

- Notify sales, inventory, and accounting.

Permission Required:

- `sales.returns.create`
- `sales.returns.approve`
- `sales.returns.complete`

Possible Error Conditions:

- Return exceeds sold quantity.
- Missing stock valuation.
- Closed accounting period.

## Workflow 15: POS Sale

Preconditions:

- POS terminal exists.
- Shift is open.
- Warehouse exists.
- Product variants are active.
- User has POS permission.

Validation Rules:

- Cart must contain at least one item.
- Quantity must be greater than zero.
- Payment amount must cover total unless credit sale is allowed.
- Stock must be available unless negative stock is allowed.

Business Rules:

- POS sale is completed in one controlled workflow.
- It records sale, payment, inventory movement, and accounting posting.
- Receipt number must be unique per company.

Database Changes:

- Create `pos_sales`.
- Create `pos_sale_items`.
- Create `pos_payments`.
- Create negative `stock_movements`.
- Create balanced journal entries.

Inventory Changes:

- Decrease stock from POS warehouse.

Accounting Impact:

- Debit cash/bank/payment clearing.
- Credit revenue.
- Credit tax payable where applicable.
- Debit COGS.
- Credit inventory.

Audit Log:

- POS sale completed.
- Payment collected.
- Inventory movement.
- Journal posting.

Notification:

- Notify only for exceptions such as large discount, cancelled sale, failed posting, or low stock.

Permission Required:

- `pos.sales.create`
- `pos.sales.complete`
- `pos.discounts.apply` when discount is used.

Possible Error Conditions:

- No open shift.
- Insufficient stock.
- Payment mismatch.
- Missing payment method account.
- Printer failure should not reverse completed sale.

## Workflow 16: Customer Payment

Preconditions:

- Customer exists.
- Sale invoice exists or unapplied payment is allowed.
- Payment method is mapped to account.

Validation Rules:

- Amount must be greater than zero.
- Currency is required.
- Payment date must be in open accounting period.
- Allocation cannot exceed invoice balance.

Business Rules:

- Payment reduces customer receivable.
- Payment may be allocated to one or more invoices if allocation tables are approved later.
- Completed payment cannot be edited; reversal is required.

Database Changes:

- Create `customer_payments`.
- Update sale paid/balance summary if denormalized.
- Create journal entry and lines.

Inventory Changes:

- None.

Accounting Impact:

- Debit cash/bank/payment account.
- Credit accounts receivable.

Audit Log:

- Customer payment create, approve, complete.

Notification:

- Notify sales/accounting.
- Optional customer receipt notification.

Permission Required:

- `accounting.customer-payments.create`
- `accounting.customer-payments.complete`

Possible Error Conditions:

- Payment exceeds balance.
- Missing payment account.
- Closed period.

## Workflow 17: Supplier Payment

Preconditions:

- Supplier exists.
- Purchase invoice exists or advance payment is allowed.
- Payment method is mapped to account.

Validation Rules:

- Amount must be greater than zero.
- Allocation cannot exceed purchase balance.
- Payment date must be in open period.

Business Rules:

- Payment reduces supplier payable.
- Completed payment requires reversal to correct.

Database Changes:

- Create `supplier_payments`.
- Update purchase paid/balance summary if denormalized.
- Create journal entry and lines.

Inventory Changes:

- None.

Accounting Impact:

- Debit accounts payable.
- Credit cash/bank/payment account.

Audit Log:

- Supplier payment create, approve, complete.

Notification:

- Notify purchase/accounting.

Permission Required:

- `accounting.supplier-payments.create`
- `accounting.supplier-payments.complete`

Possible Error Conditions:

- Payment exceeds payable balance.
- Missing payment account.
- Closed period.

## Workflow 18: Expense Entry

Preconditions:

- Expense account exists.
- Payment method exists.
- User has expense permission.

Validation Rules:

- Expense date, account, amount, currency, and branch are required.
- Amount must be greater than zero.
- Date must be in open accounting period.

Business Rules:

- Expense entry records non-inventory operational expense.
- Expense may be paid immediately or accrued depending on payment type.

Database Changes:

- Create expense document if approved under accounting workflow.
- Create journal entry and lines on completion.
- Attach receipt document if provided.

Inventory Changes:

- None.

Accounting Impact:

- Debit expense account.
- Credit cash/bank or payable account.

Audit Log:

- Expense create, approve, complete.

Notification:

- Notify approver/accounting.

Permission Required:

- `accounting.expenses.create`
- `accounting.expenses.approve`

Possible Error Conditions:

- Missing account.
- Closed period.
- Permission denied.

## Workflow 19: Income Entry

Preconditions:

- Income account exists.
- Payment method exists.

Validation Rules:

- Income date, account, amount, currency, and branch are required.
- Amount must be greater than zero.
- Date must be in open accounting period.

Business Rules:

- Income entry records non-sales income.
- It should not be used for normal sales invoices.

Database Changes:

- Create income document if approved under accounting workflow.
- Create journal entry and lines on completion.

Inventory Changes:

- None.

Accounting Impact:

- Debit cash/bank or receivable.
- Credit income account.

Audit Log:

- Income create, approve, complete.

Notification:

- Notify accounting.

Permission Required:

- `accounting.income.create`
- `accounting.income.approve`

Possible Error Conditions:

- Missing income account.
- Closed period.
- Invalid payment method.

## Workflow 20: Stock Transfer

Preconditions:

- Source and destination warehouses exist.
- Product variants exist.
- User has transfer permission.

Validation Rules:

- Source and destination warehouses cannot be the same.
- Quantity must be greater than zero.
- Source warehouse must have sufficient stock unless negative stock is allowed.

Business Rules:

- Transfer creates paired stock movements.
- Source movement is negative.
- Destination movement is positive.
- Transfer has no accounting impact unless warehouse valuation policy requires it.

Database Changes:

- Create stock transfer header and items.
- On completion, create stock movements for source and destination.

Inventory Changes:

- Decrease source warehouse.
- Increase destination warehouse.

Accounting Impact:

- Usually none.
- If branch-level inventory accounting is enabled, move value between inventory accounts.

Audit Log:

- Transfer create, approve, complete.
- Stock movements logged.

Notification:

- Notify source and destination warehouse users.

Permission Required:

- `inventory.transfers.create`
- `inventory.transfers.approve`
- `inventory.transfers.complete`

Possible Error Conditions:

- Insufficient source stock.
- Same warehouse selected.
- Inactive warehouse.

## Workflow 21: Stock Adjustment

Preconditions:

- Warehouse exists.
- Product variants exist.
- User has adjustment permission.

Validation Rules:

- Reason is required.
- Counted quantity must be zero or greater.
- Adjustment date must be valid.

Business Rules:

- Adjustment corrects stock based on physical count.
- Adjustment creates movement only for difference quantity.
- Completed adjustment cannot be edited.

Database Changes:

- Create stock adjustment header and items.
- Create positive or negative stock movements on completion.

Inventory Changes:

- Increase or decrease stock based on difference.

Accounting Impact:

- Debit or credit inventory adjustment account.
- Opposite side is inventory account.

Audit Log:

- Adjustment create, approve, complete.

Notification:

- Notify inventory manager and accounting.

Permission Required:

- `inventory.adjustments.create`
- `inventory.adjustments.approve`

Possible Error Conditions:

- Missing reason.
- Missing adjustment account.
- Closed period.

## Workflow 22: Damage Stock

Preconditions:

- Warehouse exists.
- Product variant exists.
- User has damage stock permission.

Validation Rules:

- Damaged quantity must be greater than zero.
- Stock must be available.
- Damage reason is required.

Business Rules:

- Damage stock is a controlled stock decrease.
- It may be represented as a stock adjustment with damage reason.
- Damaged stock should be auditable separately in reports.

Database Changes:

- Create stock adjustment or damage document.
- Create negative stock movement with movement type `damage`.

Inventory Changes:

- Decrease stock from warehouse.

Accounting Impact:

- Debit inventory loss/damage expense.
- Credit inventory.

Audit Log:

- Damage stock record create and complete.

Notification:

- Notify inventory manager and accounting.

Permission Required:

- `inventory.damage.create`
- `inventory.damage.approve`

Possible Error Conditions:

- Insufficient stock.
- Missing damage account.
- Missing reason.

## Workflow 23: Courier Booking

Preconditions:

- Courier provider is active.
- Sale or delivery exists.
- Customer shipping address exists.

Validation Rules:

- Delivery address is required.
- Courier provider is required.
- Package information is required if provider needs it.

Business Rules:

- Courier booking should not complete delivery automatically.
- Courier status sync updates shipment status.
- Courier failure should not cancel sale automatically.

Database Changes:

- Create `courier_shipments`.
- Store tracking number when provider returns it.

Inventory Changes:

- None unless delivery completion is part of separate workflow.

Accounting Impact:

- Shipping cost may create expense or be added to sale depending on settings.

Audit Log:

- Courier booking create.
- Tracking update.
- Sync failure.

Notification:

- Notify sales/customer if shipment notification enabled.

Permission Required:

- `courier.shipments.create`
- `courier.shipments.sync`

Possible Error Conditions:

- Missing address.
- Courier API failure.
- Invalid provider credentials.

## Workflow 24: Delivery Complete

Preconditions:

- Delivery exists and is approved.
- Warehouse has stock.
- User has delivery completion permission.

Validation Rules:

- Delivery items must be valid.
- Delivered quantity must be greater than zero.
- Stock must be available unless negative stock is allowed.

Business Rules:

- Delivery completion creates stock decrease.
- Delivery may trigger courier status update.
- Completed delivery cannot be edited; return or reversal is required.

Database Changes:

- Update delivery status to `completed`.
- Create negative `stock_movements`.

Inventory Changes:

- Decrease stock from delivery warehouse.

Accounting Impact:

- If COGS posts on delivery: debit COGS, credit inventory.
- If COGS posts on invoice completion: no separate delivery journal.

Audit Log:

- Delivery completed.
- Inventory movement logged.

Notification:

- Notify customer/sales/courier if enabled.

Permission Required:

- `sales.deliveries.complete`

Possible Error Conditions:

- Insufficient stock.
- Delivery already completed.
- Missing COGS/inventory account.

## Workflow 25: Employee Salary

Preconditions:

- Employee exists.
- Salary structure or salary amount is configured.
- Currency exists.

Validation Rules:

- Employee number is required.
- Salary amount must be `DECIMAL(18,2)`.
- Effective date is required.

Business Rules:

- Salary setup does not post accounting.
- Salary changes must be audited.
- Historical payroll should preserve salary used at calculation time.

Database Changes:

- Update employee compensation fields or salary structure records if approved.

Inventory Changes:

- None.

Accounting Impact:

- None until payroll posting.

Audit Log:

- Salary create/update.

Notification:

- Notify HR/payroll approver.

Permission Required:

- `hr.salary.update`

Possible Error Conditions:

- Missing currency.
- Invalid effective date.
- Permission denied.

## Workflow 26: Attendance

Preconditions:

- Employee exists and is active.
- Attendance period/date is valid.

Validation Rules:

- Attendance date is required.
- One attendance record per employee per date.
- Status must be valid.

Business Rules:

- Attendance affects payroll calculation.
- Attendance can be imported or manually marked.
- Changes after payroll generation require permission and may require payroll recalculation.

Database Changes:

- Create or update attendance record.

Inventory Changes:

- None.

Accounting Impact:

- None until payroll posting.

Audit Log:

- Attendance create/update/import.

Notification:

- Notify HR for exceptions such as absence or late entry.

Permission Required:

- `hr.attendance.create`
- `hr.attendance.update`

Possible Error Conditions:

- Duplicate attendance.
- Employee inactive.
- Payroll already completed.

## Workflow 27: Payroll

Preconditions:

- Payroll period exists.
- Employees exist.
- Attendance and salary data are available.
- Payroll accounts are configured.

Validation Rules:

- Payroll period must be open.
- Employee cannot have duplicate payroll for same period.
- Gross, deductions, and net salary must be valid money values.

Business Rules:

- Payroll generation creates draft payroll records.
- Approval locks payroll calculations.
- Completion posts salary expense and payable/payment accounting.
- Completed payroll requires reversal to correct.

Database Changes:

- Create `payrolls`.
- Create `payroll_items`.
- Create journal entries on completion.

Inventory Changes:

- None.

Accounting Impact:

- Debit salary expense.
- Credit salary payable or cash/bank depending on payment flow.

Audit Log:

- Payroll generated, approved, completed, posted.

Notification:

- Notify HR, accounting, and employees if payslip notification is enabled.

Permission Required:

- `hr.payroll.generate`
- `hr.payroll.approve`
- `hr.payroll.complete`

Possible Error Conditions:

- Missing salary account.
- Duplicate payroll.
- Closed period.
- Incomplete attendance.

## Workflow 28: Profit & Loss Calculation

Preconditions:

- Chart of accounts exists.
- Journal entries are posted.
- User has financial report permission.

Validation Rules:

- Date range is required.
- Fiscal period must be valid.
- Company context is required.

Business Rules:

- P&L is generated from `journal_lines`.
- Only posted/completed accounting entries are included.
- Revenue, income, COGS, expense, and tax treatment follows account type.
- Reports are real-time unless caching is explicitly approved later.

Database Changes:

- None.

Inventory Changes:

- None.

Accounting Impact:

- None; this is reporting only.

Audit Log:

- Optional report viewed/exported audit.

Notification:

- None by default.

Permission Required:

- `reports.financial.profit-loss.view`

Possible Error Conditions:

- Missing accounts.
- Unposted transactions excluded.
- Invalid date range.

## Workflow 29: Trial Balance Generation

Preconditions:

- Chart of accounts exists.
- Journal entries are posted.
- User has trial balance permission.

Validation Rules:

- Date or period is required.
- Company context is required.

Business Rules:

- Trial balance is generated from journal lines.
- Total debit must equal total credit.
- Any imbalance indicates posting error and must be flagged.
- Report is real-time unless cached reporting is approved later.

Database Changes:

- None.

Inventory Changes:

- None.

Accounting Impact:

- None; this is reporting only.

Audit Log:

- Optional report viewed/exported audit.

Notification:

- Notify accounting admin if imbalance is detected.

Permission Required:

- `reports.financial.trial-balance.view`

Possible Error Conditions:

- Unbalanced ledger.
- Invalid period.
- Permission denied.

## Workflow 30: Dashboard KPI Update

Preconditions:

- User has dashboard permission.
- Company context is selected.

Validation Rules:

- Date range, branch, warehouse, and currency filters must be valid.
- User can only see permitted data.

Business Rules:

- Dashboard KPIs are generated from transactional data.
- KPIs are not stored as report tables unless future cached reporting is approved.
- Widgets must respect company, branch, warehouse, soft delete, and permission rules.

Database Changes:

- None for real-time KPI view.

Inventory Changes:

- None.

Accounting Impact:

- None.

Audit Log:

- Optional dashboard viewed audit for sensitive finance widgets.

Notification:

- Dashboard may surface existing alerts: low stock, overdue invoices, overdue payables, failed syncs, pending approvals.

Permission Required:

- `dashboard.view`
- Widget-specific permissions, such as `reports.financial.view` or `inventory.view`.

Possible Error Conditions:

- No company selected.
- Permission denied for widget.
- Query timeout on large data range.

## Workflow Engine Design

### Status Transitions

Allowed default transitions:

- `draft` to `pending`
- `pending` to `approved`
- `approved` to `completed`
- `draft` to `cancelled`
- `pending` to `cancelled`
- `approved` to `cancelled` only when no completed downstream impact exists

Not allowed by default:

- `completed` to `draft`
- `completed` to `cancelled`
- `cancelled` to `completed`

Completed records require reversal.

### Business Transaction Boundaries

The following workflows must run in database transactions:

- Goods receive completion
- Purchase return completion
- Sales invoice posting
- Sales return completion
- POS sale completion
- Customer payment completion
- Supplier payment completion
- Expense completion
- Income completion
- Stock transfer completion
- Stock adjustment completion
- Damage stock completion
- Delivery completion
- Payroll completion
- Manual journal posting

### Reversal Rules

Reversal must:

- Reference original document.
- Create new reversing document or reversing journal.
- Reverse inventory movements where inventory was affected.
- Reverse accounting entries where accounting was affected.
- Preserve original audit trail.
- Require reversal reason.
- Notify affected module owners.

### Audit Rules

Audit logs are required for:

- Create
- Update
- Delete
- Submit
- Approve
- Complete
- Cancel
- Reverse
- Login
- Logout
- Permission change
- Settings change
- Inventory movement
- Accounting posting
- Integration sync failure

Audit log must include:

- Company
- Branch where applicable
- Warehouse where applicable
- User
- Action
- Record type
- Record UUID
- Old values when applicable
- New values when applicable
- IP address
- User agent
- Request ID
- Timestamp

### Notification Rules

Notifications are required for:

- Approval requests
- Approval decisions
- Completion of major documents
- Payment completion
- Low stock
- Failed courier or e-commerce sync
- Payroll completion
- Trial balance imbalance
- Permission changes

Notifications should be permission-aware and company-scoped.

## Approval Gate

This document defines business rules and workflows only. No code, migrations, models, SQL, Blade templates, or CSS should be generated from it until this phase is approved.
