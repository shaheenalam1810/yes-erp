# Database Architecture

## Scope

This document defines the database architecture for the approved SME Business ERP modules only:

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

This is a design document only. It does not contain migrations, SQL, Laravel models, or application code.

## Mandatory Database Rules

The ERP uses one shared MySQL database.

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

Standard workflow status values:

- `draft`
- `pending`
- `approved`
- `completed`
- `cancelled`

Money fields:

- All money columns must use `DECIMAL(18,2)`.
- Every document or payment table that stores money must include `currency_id`.
- Exchange-rate-ready transaction tables should include `exchange_rate DECIMAL(18,8)` and base-currency totals where needed.

Public references:

- Use `uuid` for URLs, APIs, integrations, imports, and exports.
- Use numeric `id` internally for joins and performance.

Soft deletes:

- Every business table must support soft deletes.
- Soft-deleted records remain available for audit and historical reports.

## Naming Conventions

Tables use plural snake case.

Document tables use header and line separation:

- `sales` and `sale_items`
- `purchases` and `purchase_items`
- `returns` and `return_items`
- `purchase_orders` and `purchase_order_items`
- `stock_transfers` and `stock_transfer_items`

Foreign keys use singular names:

- `company_id`
- `branch_id`
- `warehouse_id`
- `product_variant_id`
- `currency_id`

Document numbers use the document name:

- `sale_number`
- `purchase_number`
- `return_number`
- `journal_number`

Indexes should place `company_id` first for tenant-scoped queries.

## Company, Branch, and Warehouse Strategy

`company_id` is mandatory on every business table and is the tenant boundary.

`branch_id` is used when a transaction, user, employee, customer, supplier, POS terminal, or document belongs to a branch.

`warehouse_id` is used when stock is stored, moved, received, sold, transferred, adjusted, or returned.

Rules:

- Branches belong to one company.
- Warehouses belong to one company.
- A warehouse may optionally belong to a branch.
- A stock movement always references a warehouse.
- A stock movement always references a product variant, not only a product.
- A business document may reference both branch and warehouse when the workflow affects both operations and inventory.

## Master Tables

### Multi Business

#### companies

Purpose: Stores each business tenant.

Primary key: `id`

Foreign keys: user tracking fields reference `users.id`.

Required fields: `id`, `uuid`, `name`, `legal_name`, `tax_number`, `base_currency_id`, `timezone`, `is_active`, global audit fields.

Unique constraints: `uuid`, `tax_number` when present.

Indexes: `base_currency_id`, `is_active`.

Relationships: one company has many branches, warehouses, users, customers, suppliers, products, transactions, settings, and documents.

#### branches

Purpose: Stores company branches, outlets, or business locations.

Primary key: `id`

Foreign keys: `company_id`, user tracking fields.

Required fields: `id`, `uuid`, `company_id`, `name`, `code`, `address`, `is_active`, global audit fields.

Unique constraints: `company_id + uuid`, `company_id + code`.

Indexes: `company_id`, `company_id + is_active`.

Relationships: one company has many branches; one branch has many users, customers, suppliers, sales, purchases, POS shifts, employees, and reports.

#### warehouses

Purpose: Stores physical or virtual inventory locations.

Primary key: `id`

Foreign keys: `company_id`, `branch_id` nullable, user tracking fields.

Required fields: `id`, `uuid`, `company_id`, `name`, `code`, `is_default`, `is_active`, global audit fields.

Unique constraints: `company_id + uuid`, `company_id + code`.

Indexes: `company_id`, `company_id + branch_id`, `company_id + is_active`.

Relationships: one company has many warehouses; one branch may have many warehouses; one warehouse has many stock movements.

### User & Role Management

#### users

Purpose: Stores application users.

Primary key: `id`

Foreign keys: user tracking fields nullable for system-created users.

Required fields: `id`, `uuid`, `name`, `email`, `password`, `is_active`, global audit fields.

Unique constraints: `uuid`, `email`.

Indexes: `email`, `is_active`.

Relationships: many users belong to many companies; many users have many roles; one user may be linked to one employee.

#### company_user

Purpose: Links users to companies and stores company access state.

Primary key: `id`

Foreign keys: `company_id`, `user_id`, `default_branch_id`, user tracking fields.

Required fields: `id`, `uuid`, `company_id`, `user_id`, `is_owner`, `is_active`, global audit fields.

Unique constraints: `company_id + user_id`.

Indexes: `company_id`, `user_id`, `company_id + is_active`.

Relationships: many users belong to many companies.

#### roles

Purpose: Stores company-scoped roles.

Primary key: `id`

Foreign keys: `company_id`, user tracking fields.

Required fields: `id`, `uuid`, `company_id`, `name`, `guard_name`, `is_system`, global audit fields.

Unique constraints: `company_id + name + guard_name`.

Indexes: `company_id`, `guard_name`.

Relationships: many roles belong to many users and many permissions.

#### permissions

Purpose: Stores module-aware permission definitions.

Primary key: `id`

Foreign keys: user tracking fields.

Required fields: `id`, `uuid`, `name`, `guard_name`, `module`, `action`, global audit fields.

Unique constraints: `name + guard_name`.

Indexes: `module`, `action`.

Relationships: many permissions belong to many roles.

#### role_user

Purpose: Assigns roles to users within a company.

Primary key: `id`

Foreign keys: `company_id`, `role_id`, `user_id`, user tracking fields.

Required fields: `id`, `uuid`, `company_id`, `role_id`, `user_id`, global audit fields.

Unique constraints: `company_id + role_id + user_id`.

Indexes: `company_id + user_id`, `company_id + role_id`.

#### role_permission

Purpose: Assigns permissions to roles within a company.

Primary key: `id`

Foreign keys: `company_id`, `role_id`, `permission_id`, user tracking fields.

Required fields: `id`, `uuid`, `company_id`, `role_id`, `permission_id`, global audit fields.

Unique constraints: `company_id + role_id + permission_id`.

Indexes: `company_id + role_id`, `permission_id`.

### Settings

Settings are split into dedicated configuration tables. A single generic `settings` table is not used for core configuration.

#### company_settings

Purpose: Stores company-level preferences.

Primary key: `id`

Foreign keys: `company_id`, `default_currency_id`, user tracking fields.

Required fields: `id`, `uuid`, `company_id`, `default_currency_id`, `timezone`, `date_format`, `number_format`, `fiscal_year_start_month`, global audit fields.

Unique constraints: `company_id`.

Indexes: `company_id`, `default_currency_id`.

#### branch_settings

Purpose: Stores branch-level defaults.

Primary key: `id`

Foreign keys: `company_id`, `branch_id`, `default_warehouse_id`, user tracking fields.

Required fields: `id`, `uuid`, `company_id`, `branch_id`, `default_warehouse_id`, global audit fields.

Unique constraints: `company_id + branch_id`.

Indexes: `company_id + branch_id`.

#### invoice_settings

Purpose: Stores sales and purchase document numbering and invoice behavior.

Primary key: `id`

Foreign keys: `company_id`, user tracking fields.

Required fields: `id`, `uuid`, `company_id`, `sale_prefix`, `purchase_prefix`, `return_prefix`, `next_sale_number`, `next_purchase_number`, `next_return_number`, global audit fields.

Unique constraints: `company_id`.

Indexes: `company_id`.

#### tax_settings

Purpose: Stores company tax defaults.

Primary key: `id`

Foreign keys: `company_id`, user tracking fields.

Required fields: `id`, `uuid`, `company_id`, `tax_name`, `tax_rate`, `is_tax_inclusive`, `is_active`, global audit fields.

Unique constraints: `company_id + tax_name`.

Indexes: `company_id + is_active`.

#### accounting_settings

Purpose: Stores default posting accounts.

Primary key: `id`

Foreign keys: `company_id`, `sales_account_id`, `purchase_account_id`, `inventory_account_id`, `cash_account_id`, `accounts_receivable_id`, `accounts_payable_id`, user tracking fields.

Required fields: `id`, `uuid`, `company_id`, global audit fields.

Unique constraints: `company_id`.

Indexes: all account foreign keys.

#### pos_settings

Purpose: Stores POS behavior and receipt defaults.

Primary key: `id`

Foreign keys: `company_id`, user tracking fields.

Required fields: `id`, `uuid`, `company_id`, `receipt_prefix`, `allow_negative_stock`, `default_payment_method_id`, global audit fields.

Unique constraints: `company_id`.

Indexes: `company_id`.

### Currency

#### currencies

Purpose: Stores currencies for multi-currency readiness.

Primary key: `id`

Foreign keys: user tracking fields.

Required fields: `id`, `uuid`, `code`, `name`, `symbol`, `decimal_places`, `is_active`, global audit fields.

Unique constraints: `code`, `uuid`.

Indexes: `is_active`.

Relationships: one currency may be used by companies, documents, payments, journals, suppliers, customers, and payroll.

#### exchange_rates

Purpose: Stores exchange rates per company and date.

Primary key: `id`

Foreign keys: `company_id`, `from_currency_id`, `to_currency_id`, user tracking fields.

Required fields: `id`, `uuid`, `company_id`, `from_currency_id`, `to_currency_id`, `rate`, `effective_date`, global audit fields.

Unique constraints: `company_id + from_currency_id + to_currency_id + effective_date`.

Indexes: `company_id + effective_date`, `from_currency_id + to_currency_id`.

### Customer (CRM)

#### customers

Purpose: Stores customer master records.

Primary key: `id`

Foreign keys: `company_id`, `branch_id` nullable, `currency_id` nullable, user tracking fields.

Required fields: `id`, `uuid`, `company_id`, `customer_code`, `name`, `customer_type`, `status`, global audit fields.

Unique constraints: `company_id + uuid`, `company_id + customer_code`.

Indexes: `company_id + branch_id`, `company_id + status`, `company_id + name`.

Relationships: one customer has many contacts, addresses, activities, sales, payments, returns, documents, and audit logs.

#### customer_contacts

Purpose: Stores customer contact people.

Primary key: `id`

Foreign keys: `company_id`, `customer_id`, user tracking fields.

Required fields: `id`, `uuid`, `company_id`, `customer_id`, `name`, `is_primary`, global audit fields.

Unique constraints: `company_id + uuid`.

Indexes: `company_id + customer_id`, `email`, `phone`.

#### customer_addresses

Purpose: Stores customer billing and shipping addresses.

Primary key: `id`

Foreign keys: `company_id`, `customer_id`, user tracking fields.

Required fields: `id`, `uuid`, `company_id`, `customer_id`, `address_type`, `address_line_1`, `country`, global audit fields.

Unique constraints: `company_id + uuid`.

Indexes: `company_id + customer_id`, `address_type`.

#### customer_activities

Purpose: Stores calls, meetings, notes, follow-ups, and CRM activities.

Primary key: `id`

Foreign keys: `company_id`, `customer_id`, `assigned_to`, user tracking fields.

Required fields: `id`, `uuid`, `company_id`, `customer_id`, `activity_type`, `subject`, `status`, global audit fields.

Unique constraints: `company_id + uuid`.

Indexes: `company_id + customer_id`, `company_id + assigned_to`, `company_id + status`, `scheduled_at`.

### Supplier Management

#### suppliers

Purpose: Stores supplier/vendor master records.

Primary key: `id`

Foreign keys: `company_id`, `branch_id` nullable, `currency_id` nullable, user tracking fields.

Required fields: `id`, `uuid`, `company_id`, `supplier_code`, `name`, `supplier_type`, `status`, global audit fields.

Unique constraints: `company_id + uuid`, `company_id + supplier_code`.

Indexes: `company_id + branch_id`, `company_id + status`, `company_id + name`.

Relationships: one supplier has many contacts, addresses, purchases, payments, returns, documents, and audit logs.

#### supplier_contacts

Purpose: Stores supplier contacts.

Primary key: `id`

Foreign keys: `company_id`, `supplier_id`, user tracking fields.

Required fields: `id`, `uuid`, `company_id`, `supplier_id`, `name`, `is_primary`, global audit fields.

Unique constraints: `company_id + uuid`.

Indexes: `company_id + supplier_id`, `email`, `phone`.

#### supplier_addresses

Purpose: Stores supplier addresses.

Primary key: `id`

Foreign keys: `company_id`, `supplier_id`, user tracking fields.

Required fields: `id`, `uuid`, `company_id`, `supplier_id`, `address_type`, `address_line_1`, `country`, global audit fields.

Unique constraints: `company_id + uuid`.

Indexes: `company_id + supplier_id`, `address_type`.

### Product Management

#### product_categories

Purpose: Stores product category hierarchy.

Primary key: `id`

Foreign keys: `company_id`, `parent_id` nullable, user tracking fields.

Required fields: `id`, `uuid`, `company_id`, `name`, `slug`, `is_active`, global audit fields.

Unique constraints: `company_id + slug`.

Indexes: `company_id`, `company_id + parent_id`.

#### product_brands

Purpose: Stores product brands.

Primary key: `id`

Foreign keys: `company_id`, user tracking fields.

Required fields: `id`, `uuid`, `company_id`, `name`, `is_active`, global audit fields.

Unique constraints: `company_id + name`.

Indexes: `company_id + is_active`.

#### units

Purpose: Stores measurement units.

Primary key: `id`

Foreign keys: `company_id`, user tracking fields.

Required fields: `id`, `uuid`, `company_id`, `name`, `symbol`, `is_active`, global audit fields.

Unique constraints: `company_id + symbol`.

Indexes: `company_id`.

#### products

Purpose: Stores product family/master records. SKU, barcode, cost, selling price, and stock are not stored here because they belong to variants.

Primary key: `id`

Foreign keys: `company_id`, `category_id`, `brand_id` nullable, `base_unit_id`, user tracking fields.

Required fields: `id`, `uuid`, `company_id`, `name`, `product_type`, `base_unit_id`, `is_active`, global audit fields.

Unique constraints: `company_id + uuid`, `company_id + slug`.

Indexes: `company_id + category_id`, `company_id + brand_id`, `company_id + name`, `company_id + is_active`.

Relationships: one product has many variants, documents, sale items, purchase items, and return items through variants.

#### product_attributes

Purpose: Defines variant attributes such as color, size, model, or material.

Primary key: `id`

Foreign keys: `company_id`, user tracking fields.

Required fields: `id`, `uuid`, `company_id`, `name`, `is_active`, global audit fields.

Unique constraints: `company_id + name`.

Indexes: `company_id + is_active`.

#### product_attribute_values

Purpose: Stores values for product attributes.

Primary key: `id`

Foreign keys: `company_id`, `product_attribute_id`, user tracking fields.

Required fields: `id`, `uuid`, `company_id`, `product_attribute_id`, `value`, global audit fields.

Unique constraints: `company_id + product_attribute_id + value`.

Indexes: `company_id + product_attribute_id`.

#### product_variants

Purpose: Stores sellable and stockable product variants with independent SKU, barcode, cost price, selling price, and stock identity.

Primary key: `id`

Foreign keys: `company_id`, `product_id`, `unit_id`, `currency_id`, user tracking fields.

Required fields: `id`, `uuid`, `company_id`, `product_id`, `sku`, `barcode`, `name`, `unit_id`, `currency_id`, `cost_price DECIMAL(18,2)`, `selling_price DECIMAL(18,2)`, `is_stock_tracked`, `is_active`, global audit fields.

Unique constraints: `company_id + sku`, `company_id + barcode` when present, `company_id + uuid`.

Indexes: `company_id + product_id`, `company_id + sku`, `company_id + barcode`, `company_id + is_active`.

Relationships: one product has many variants; one variant has many stock movements, sale items, purchase items, return items, and POS items.

#### product_variant_values

Purpose: Links variants to attribute values.

Primary key: `id`

Foreign keys: `company_id`, `product_variant_id`, `product_attribute_id`, `product_attribute_value_id`, user tracking fields.

Required fields: `id`, `uuid`, `company_id`, `product_variant_id`, `product_attribute_id`, `product_attribute_value_id`, global audit fields.

Unique constraints: `company_id + product_variant_id + product_attribute_id`.

Indexes: `company_id + product_variant_id`, `company_id + product_attribute_value_id`.

## Transaction Tables

### Inventory Management

Inventory is designed around stock movement history. Current stock is derived from movements. A separate balance projection may be added for performance, but the source of truth is always `stock_movements`.

#### stock_movements

Purpose: Append-only inventory ledger for every stock increase, decrease, transfer, adjustment, sale, purchase receipt, POS sale, and return.

Primary key: `id`

Foreign keys: `company_id`, `branch_id` nullable, `warehouse_id`, `product_variant_id`, user tracking fields.

Required fields: `id`, `uuid`, `company_id`, `warehouse_id`, `product_variant_id`, `movement_number`, `movement_type`, `direction`, `quantity`, `unit_cost DECIMAL(18,2)`, `total_cost DECIMAL(18,2)`, `currency_id`, `exchange_rate`, `source_type`, `source_id`, `moved_at`, global audit fields.

Unique constraints: `company_id + movement_number`, `company_id + uuid`.

Indexes: `company_id + warehouse_id + product_variant_id`, `company_id + movement_type`, `company_id + moved_at`, `source_type + source_id`.

Relationships: one product variant has many stock movements; one warehouse has many stock movements.

#### stock_transfer_headers

Purpose: Stores warehouse-to-warehouse transfer headers.

Primary key: `id`

Foreign keys: `company_id`, `branch_id` nullable, `from_warehouse_id`, `to_warehouse_id`, user tracking fields.

Required fields: `id`, `uuid`, `company_id`, `transfer_number`, `from_warehouse_id`, `to_warehouse_id`, `status`, `transfer_date`, global audit fields.

Unique constraints: `company_id + transfer_number`.

Indexes: `company_id + status`, `company_id + transfer_date`, `from_warehouse_id`, `to_warehouse_id`.

#### stock_transfer_items

Purpose: Stores stock transfer line items.

Primary key: `id`

Foreign keys: `company_id`, `stock_transfer_header_id`, `product_variant_id`, user tracking fields.

Required fields: `id`, `uuid`, `company_id`, `stock_transfer_header_id`, `product_variant_id`, `quantity`, `unit_cost DECIMAL(18,2)`, global audit fields.

Unique constraints: `company_id + stock_transfer_header_id + product_variant_id`.

Indexes: `stock_transfer_header_id`, `company_id + product_variant_id`.

#### stock_adjustments

Purpose: Stores stock adjustment headers.

Primary key: `id`

Foreign keys: `company_id`, `branch_id` nullable, `warehouse_id`, user tracking fields.

Required fields: `id`, `uuid`, `company_id`, `adjustment_number`, `warehouse_id`, `status`, `adjustment_date`, `reason`, global audit fields.

Unique constraints: `company_id + adjustment_number`.

Indexes: `company_id + warehouse_id`, `company_id + status`, `company_id + adjustment_date`.

#### stock_adjustment_items

Purpose: Stores stock adjustment line items.

Primary key: `id`

Foreign keys: `company_id`, `stock_adjustment_id`, `product_variant_id`, user tracking fields.

Required fields: `id`, `uuid`, `company_id`, `stock_adjustment_id`, `product_variant_id`, `system_quantity`, `counted_quantity`, `difference_quantity`, `unit_cost DECIMAL(18,2)`, global audit fields.

Unique constraints: `company_id + stock_adjustment_id + product_variant_id`.

Indexes: `stock_adjustment_id`, `company_id + product_variant_id`.

#### stock_balance_snapshots

Purpose: Optional performance snapshot generated from `stock_movements`. This is not the source of truth.

Primary key: `id`

Foreign keys: `company_id`, `warehouse_id`, `product_variant_id`, user tracking fields.

Required fields: `id`, `uuid`, `company_id`, `warehouse_id`, `product_variant_id`, `quantity_on_hand`, `quantity_reserved`, `snapshot_at`, global audit fields.

Unique constraints: `company_id + warehouse_id + product_variant_id + snapshot_at`.

Indexes: `company_id + warehouse_id + product_variant_id`, `snapshot_at`.

### Purchase Management

#### purchase_orders

Purpose: Stores purchase order headers.

Primary key: `id`

Foreign keys: `company_id`, `branch_id`, `supplier_id`, `currency_id`, user tracking fields.

Required fields: `id`, `uuid`, `company_id`, `branch_id`, `supplier_id`, `currency_id`, `exchange_rate`, `purchase_order_number`, `order_date`, `status`, `subtotal DECIMAL(18,2)`, `discount_total DECIMAL(18,2)`, `tax_total DECIMAL(18,2)`, `grand_total DECIMAL(18,2)`, global audit fields.

Unique constraints: `company_id + purchase_order_number`.

Indexes: `company_id + supplier_id`, `company_id + branch_id`, `company_id + status`, `company_id + order_date`.

#### purchase_order_items

Purpose: Stores purchase order line items.

Primary key: `id`

Foreign keys: `company_id`, `purchase_order_id`, `product_variant_id`, user tracking fields.

Required fields: `id`, `uuid`, `company_id`, `purchase_order_id`, `product_variant_id`, `quantity`, `unit_price DECIMAL(18,2)`, `discount_amount DECIMAL(18,2)`, `tax_amount DECIMAL(18,2)`, `line_total DECIMAL(18,2)`, global audit fields.

Unique constraints: `company_id + uuid`.

Indexes: `purchase_order_id`, `company_id + product_variant_id`.

#### purchases

Purpose: Stores purchase invoice/bill headers from suppliers.

Primary key: `id`

Foreign keys: `company_id`, `branch_id`, `warehouse_id` nullable, `supplier_id`, `purchase_order_id` nullable, `currency_id`, user tracking fields.

Required fields: `id`, `uuid`, `company_id`, `branch_id`, `supplier_id`, `currency_id`, `exchange_rate`, `purchase_number`, `purchase_date`, `due_date`, `status`, `subtotal DECIMAL(18,2)`, `discount_total DECIMAL(18,2)`, `tax_total DECIMAL(18,2)`, `grand_total DECIMAL(18,2)`, `paid_total DECIMAL(18,2)`, `balance_due DECIMAL(18,2)`, global audit fields.

Unique constraints: `company_id + purchase_number`.

Indexes: `company_id + supplier_id`, `company_id + branch_id`, `company_id + status`, `company_id + purchase_date`, `company_id + due_date`.

#### purchase_items

Purpose: Stores purchase invoice/bill line items.

Primary key: `id`

Foreign keys: `company_id`, `purchase_id`, `purchase_order_item_id` nullable, `product_variant_id`, user tracking fields.

Required fields: `id`, `uuid`, `company_id`, `purchase_id`, `product_variant_id`, `description`, `quantity`, `unit_price DECIMAL(18,2)`, `discount_amount DECIMAL(18,2)`, `tax_amount DECIMAL(18,2)`, `line_total DECIMAL(18,2)`, global audit fields.

Unique constraints: `company_id + uuid`.

Indexes: `purchase_id`, `company_id + product_variant_id`.

#### purchase_receipts

Purpose: Stores goods receipt headers for stock received from suppliers.

Primary key: `id`

Foreign keys: `company_id`, `branch_id`, `warehouse_id`, `supplier_id`, `purchase_order_id` nullable, `purchase_id` nullable, user tracking fields.

Required fields: `id`, `uuid`, `company_id`, `branch_id`, `warehouse_id`, `supplier_id`, `receipt_number`, `receipt_date`, `status`, global audit fields.

Unique constraints: `company_id + receipt_number`.

Indexes: `company_id + supplier_id`, `company_id + warehouse_id`, `company_id + status`, `company_id + receipt_date`.

#### purchase_receipt_items

Purpose: Stores products received into stock.

Primary key: `id`

Foreign keys: `company_id`, `purchase_receipt_id`, `purchase_item_id` nullable, `product_variant_id`, user tracking fields.

Required fields: `id`, `uuid`, `company_id`, `purchase_receipt_id`, `product_variant_id`, `quantity_received`, `unit_cost DECIMAL(18,2)`, global audit fields.

Unique constraints: `company_id + uuid`.

Indexes: `purchase_receipt_id`, `company_id + product_variant_id`.

### Sales Management

#### sales_orders

Purpose: Stores customer sales order headers.

Primary key: `id`

Foreign keys: `company_id`, `branch_id`, `customer_id`, `currency_id`, user tracking fields.

Required fields: `id`, `uuid`, `company_id`, `branch_id`, `customer_id`, `currency_id`, `exchange_rate`, `sales_order_number`, `order_date`, `status`, `subtotal DECIMAL(18,2)`, `discount_total DECIMAL(18,2)`, `tax_total DECIMAL(18,2)`, `grand_total DECIMAL(18,2)`, global audit fields.

Unique constraints: `company_id + sales_order_number`.

Indexes: `company_id + customer_id`, `company_id + branch_id`, `company_id + status`, `company_id + order_date`.

#### sales_order_items

Purpose: Stores customer sales order line items.

Primary key: `id`

Foreign keys: `company_id`, `sales_order_id`, `product_variant_id`, user tracking fields.

Required fields: `id`, `uuid`, `company_id`, `sales_order_id`, `product_variant_id`, `description`, `quantity`, `unit_price DECIMAL(18,2)`, `discount_amount DECIMAL(18,2)`, `tax_amount DECIMAL(18,2)`, `line_total DECIMAL(18,2)`, global audit fields.

Unique constraints: `company_id + uuid`.

Indexes: `sales_order_id`, `company_id + product_variant_id`.

#### sales

Purpose: Stores sales invoice headers.

Primary key: `id`

Foreign keys: `company_id`, `branch_id`, `warehouse_id` nullable, `customer_id`, `sales_order_id` nullable, `currency_id`, user tracking fields.

Required fields: `id`, `uuid`, `company_id`, `branch_id`, `customer_id`, `currency_id`, `exchange_rate`, `sale_number`, `sale_date`, `due_date`, `status`, `subtotal DECIMAL(18,2)`, `discount_total DECIMAL(18,2)`, `tax_total DECIMAL(18,2)`, `grand_total DECIMAL(18,2)`, `paid_total DECIMAL(18,2)`, `balance_due DECIMAL(18,2)`, global audit fields.

Unique constraints: `company_id + sale_number`.

Indexes: `company_id + customer_id`, `company_id + branch_id`, `company_id + status`, `company_id + sale_date`, `company_id + due_date`.

#### sale_items

Purpose: Stores sales invoice line items.

Primary key: `id`

Foreign keys: `company_id`, `sale_id`, `sales_order_item_id` nullable, `product_variant_id`, user tracking fields.

Required fields: `id`, `uuid`, `company_id`, `sale_id`, `product_variant_id`, `description`, `quantity`, `unit_price DECIMAL(18,2)`, `discount_amount DECIMAL(18,2)`, `tax_amount DECIMAL(18,2)`, `line_total DECIMAL(18,2)`, global audit fields.

Unique constraints: `company_id + uuid`.

Indexes: `sale_id`, `company_id + product_variant_id`.

#### deliveries

Purpose: Stores sales delivery headers.

Primary key: `id`

Foreign keys: `company_id`, `branch_id`, `warehouse_id`, `customer_id`, `sale_id` nullable, `sales_order_id` nullable, user tracking fields.

Required fields: `id`, `uuid`, `company_id`, `branch_id`, `warehouse_id`, `customer_id`, `delivery_number`, `delivery_date`, `status`, global audit fields.

Unique constraints: `company_id + delivery_number`.

Indexes: `company_id + customer_id`, `company_id + warehouse_id`, `company_id + status`, `company_id + delivery_date`.

#### delivery_items

Purpose: Stores delivered product quantities.

Primary key: `id`

Foreign keys: `company_id`, `delivery_id`, `sale_item_id` nullable, `sales_order_item_id` nullable, `product_variant_id`, user tracking fields.

Required fields: `id`, `uuid`, `company_id`, `delivery_id`, `product_variant_id`, `quantity_delivered`, global audit fields.

Unique constraints: `company_id + uuid`.

Indexes: `delivery_id`, `company_id + product_variant_id`.

### Returns

#### returns

Purpose: Stores sales return or purchase return headers.

Primary key: `id`

Foreign keys: `company_id`, `branch_id`, `warehouse_id`, `customer_id` nullable, `supplier_id` nullable, `sale_id` nullable, `purchase_id` nullable, `currency_id`, user tracking fields.

Required fields: `id`, `uuid`, `company_id`, `branch_id`, `warehouse_id`, `currency_id`, `exchange_rate`, `return_number`, `return_type`, `return_date`, `status`, `subtotal DECIMAL(18,2)`, `tax_total DECIMAL(18,2)`, `grand_total DECIMAL(18,2)`, global audit fields.

Unique constraints: `company_id + return_number`.

Indexes: `company_id + return_type`, `company_id + status`, `company_id + return_date`, `company_id + customer_id`, `company_id + supplier_id`.

#### return_items

Purpose: Stores returned product line items.

Primary key: `id`

Foreign keys: `company_id`, `return_id`, `product_variant_id`, `sale_item_id` nullable, `purchase_item_id` nullable, user tracking fields.

Required fields: `id`, `uuid`, `company_id`, `return_id`, `product_variant_id`, `quantity`, `unit_price DECIMAL(18,2)`, `line_total DECIMAL(18,2)`, global audit fields.

Unique constraints: `company_id + uuid`.

Indexes: `return_id`, `company_id + product_variant_id`.

### POS

#### pos_terminals

Purpose: Stores POS registers.

Primary key: `id`

Foreign keys: `company_id`, `branch_id`, `warehouse_id`, user tracking fields.

Required fields: `id`, `uuid`, `company_id`, `branch_id`, `warehouse_id`, `name`, `code`, `is_active`, global audit fields.

Unique constraints: `company_id + code`.

Indexes: `company_id + branch_id`, `company_id + warehouse_id`.

#### pos_shifts

Purpose: Tracks cashier sessions.

Primary key: `id`

Foreign keys: `company_id`, `branch_id`, `warehouse_id`, `pos_terminal_id`, `opened_by`, `closed_by` nullable, user tracking fields.

Required fields: `id`, `uuid`, `company_id`, `branch_id`, `warehouse_id`, `pos_terminal_id`, `opening_cash DECIMAL(18,2)`, `closing_cash DECIMAL(18,2)`, `status`, `opened_at`, global audit fields.

Unique constraints: `company_id + uuid`.

Indexes: `company_id + pos_terminal_id`, `company_id + status`, `opened_at`.

#### pos_sales

Purpose: Stores POS sale headers.

Primary key: `id`

Foreign keys: `company_id`, `branch_id`, `warehouse_id`, `pos_shift_id`, `customer_id` nullable, `currency_id`, user tracking fields.

Required fields: `id`, `uuid`, `company_id`, `branch_id`, `warehouse_id`, `pos_shift_id`, `currency_id`, `exchange_rate`, `receipt_number`, `sale_date`, `status`, `subtotal DECIMAL(18,2)`, `discount_total DECIMAL(18,2)`, `tax_total DECIMAL(18,2)`, `grand_total DECIMAL(18,2)`, `paid_total DECIMAL(18,2)`, `change_total DECIMAL(18,2)`, global audit fields.

Unique constraints: `company_id + receipt_number`.

Indexes: `company_id + pos_shift_id`, `company_id + customer_id`, `company_id + sale_date`, `company_id + status`.

#### pos_sale_items

Purpose: Stores POS sale line items.

Primary key: `id`

Foreign keys: `company_id`, `pos_sale_id`, `product_variant_id`, user tracking fields.

Required fields: `id`, `uuid`, `company_id`, `pos_sale_id`, `product_variant_id`, `quantity`, `unit_price DECIMAL(18,2)`, `discount_amount DECIMAL(18,2)`, `tax_amount DECIMAL(18,2)`, `line_total DECIMAL(18,2)`, global audit fields.

Unique constraints: `company_id + uuid`.

Indexes: `pos_sale_id`, `company_id + product_variant_id`.

#### pos_payments

Purpose: Stores payments collected for POS sales.

Primary key: `id`

Foreign keys: `company_id`, `pos_sale_id`, `payment_method_id`, `currency_id`, user tracking fields.

Required fields: `id`, `uuid`, `company_id`, `pos_sale_id`, `payment_method_id`, `currency_id`, `exchange_rate`, `amount DECIMAL(18,2)`, `paid_at`, global audit fields.

Unique constraints: `company_id + uuid`.

Indexes: `pos_sale_id`, `company_id + payment_method_id`, `paid_at`.

### Complete Double Entry Accounting

Accounting is designed as a strict double-entry ledger. Every posted journal entry must have total debits equal total credits in the same company and currency context. Operational modules do not write balances directly; they create approved source documents, then accounting posting creates journal entries and journal lines.

#### account_groups

Purpose: Groups accounts for reporting hierarchy.

Primary key: `id`

Foreign keys: `company_id`, `parent_id` nullable, user tracking fields.

Required fields: `id`, `uuid`, `company_id`, `name`, `account_type`, `normal_balance`, global audit fields.

Unique constraints: `company_id + name`.

Indexes: `company_id + account_type`, `company_id + parent_id`.

#### accounts

Purpose: Stores chart of accounts.

Primary key: `id`

Foreign keys: `company_id`, `account_group_id`, `parent_id` nullable, `currency_id` nullable, user tracking fields.

Required fields: `id`, `uuid`, `company_id`, `code`, `name`, `account_type`, `normal_balance`, `is_control_account`, `is_posting`, `is_active`, global audit fields.

Unique constraints: `company_id + code`.

Indexes: `company_id + account_type`, `company_id + account_group_id`, `company_id + parent_id`.

Relationships: one account has many journal lines; one account may have child accounts.

#### fiscal_years

Purpose: Defines company fiscal years.

Primary key: `id`

Foreign keys: `company_id`, user tracking fields.

Required fields: `id`, `uuid`, `company_id`, `name`, `start_date`, `end_date`, `status`, global audit fields.

Unique constraints: `company_id + name`, `company_id + start_date + end_date`.

Indexes: `company_id + status`.

#### accounting_periods

Purpose: Defines posting periods.

Primary key: `id`

Foreign keys: `company_id`, `fiscal_year_id`, user tracking fields.

Required fields: `id`, `uuid`, `company_id`, `fiscal_year_id`, `name`, `start_date`, `end_date`, `status`, global audit fields.

Unique constraints: `company_id + fiscal_year_id + name`.

Indexes: `company_id + status`, `start_date + end_date`.

#### journals

Purpose: Stores journal types such as general, sales, purchase, cash, bank, payroll, and inventory.

Primary key: `id`

Foreign keys: `company_id`, user tracking fields.

Required fields: `id`, `uuid`, `company_id`, `code`, `name`, `journal_type`, `is_active`, global audit fields.

Unique constraints: `company_id + code`.

Indexes: `company_id + journal_type`, `company_id + is_active`.

#### journal_entries

Purpose: Stores accounting journal headers.

Primary key: `id`

Foreign keys: `company_id`, `branch_id` nullable, `journal_id`, `accounting_period_id`, `currency_id`, user tracking fields.

Required fields: `id`, `uuid`, `company_id`, `journal_id`, `currency_id`, `exchange_rate`, `journal_number`, `entry_date`, `posted_at`, `status`, `memo`, `source_type`, `source_id`, global audit fields.

Unique constraints: `company_id + journal_number`.

Indexes: `company_id + entry_date`, `company_id + posted_at`, `company_id + status`, `source_type + source_id`.

Relationships: one journal entry has many journal lines.

#### journal_lines

Purpose: Stores debit and credit details for journal entries.

Primary key: `id`

Foreign keys: `company_id`, `journal_entry_id`, `account_id`, `currency_id`, user tracking fields.

Required fields: `id`, `uuid`, `company_id`, `journal_entry_id`, `account_id`, `currency_id`, `description`, `debit DECIMAL(18,2)`, `credit DECIMAL(18,2)`, `base_debit DECIMAL(18,2)`, `base_credit DECIMAL(18,2)`, global audit fields.

Unique constraints: `company_id + uuid`.

Indexes: `journal_entry_id`, `company_id + account_id`, `company_id + currency_id`.

Rules:

- A line may have debit or credit, never both.
- A posted journal entry must balance: total debit equals total credit.
- Base debit and base credit support multi-currency reporting.

#### account_balances

Purpose: Optional period balance projection for faster trial balance and financial reports. Source of truth remains `journal_lines`.

Primary key: `id`

Foreign keys: `company_id`, `account_id`, `accounting_period_id`, `currency_id`, user tracking fields.

Required fields: `id`, `uuid`, `company_id`, `account_id`, `accounting_period_id`, `currency_id`, `opening_debit DECIMAL(18,2)`, `opening_credit DECIMAL(18,2)`, `period_debit DECIMAL(18,2)`, `period_credit DECIMAL(18,2)`, `closing_debit DECIMAL(18,2)`, `closing_credit DECIMAL(18,2)`, global audit fields.

Unique constraints: `company_id + account_id + accounting_period_id + currency_id`.

Indexes: `company_id + accounting_period_id`, `company_id + account_id`.

#### payment_methods

Purpose: Stores payment methods and their mapped ledger account.

Primary key: `id`

Foreign keys: `company_id`, `account_id`, user tracking fields.

Required fields: `id`, `uuid`, `company_id`, `name`, `payment_type`, `account_id`, `is_active`, global audit fields.

Unique constraints: `company_id + name`.

Indexes: `company_id + payment_type`, `company_id + is_active`.

#### customer_payments

Purpose: Stores receipts from customers.

Primary key: `id`

Foreign keys: `company_id`, `branch_id`, `customer_id`, `sale_id` nullable, `payment_method_id`, `currency_id`, user tracking fields.

Required fields: `id`, `uuid`, `company_id`, `branch_id`, `customer_id`, `currency_id`, `exchange_rate`, `payment_number`, `payment_date`, `amount DECIMAL(18,2)`, `status`, global audit fields.

Unique constraints: `company_id + payment_number`.

Indexes: `company_id + customer_id`, `company_id + sale_id`, `company_id + payment_date`, `company_id + status`.

#### supplier_payments

Purpose: Stores payments made to suppliers.

Primary key: `id`

Foreign keys: `company_id`, `branch_id`, `supplier_id`, `purchase_id` nullable, `payment_method_id`, `currency_id`, user tracking fields.

Required fields: `id`, `uuid`, `company_id`, `branch_id`, `supplier_id`, `currency_id`, `exchange_rate`, `payment_number`, `payment_date`, `amount DECIMAL(18,2)`, `status`, global audit fields.

Unique constraints: `company_id + payment_number`.

Indexes: `company_id + supplier_id`, `company_id + purchase_id`, `company_id + payment_date`, `company_id + status`.

### Courier Integration

#### courier_providers

Purpose: Stores courier provider configuration.

Primary key: `id`

Foreign keys: `company_id`, user tracking fields.

Required fields: `id`, `uuid`, `company_id`, `name`, `code`, `is_active`, global audit fields.

Unique constraints: `company_id + code`.

Indexes: `company_id + is_active`.

#### courier_shipments

Purpose: Tracks shipment requests and statuses.

Primary key: `id`

Foreign keys: `company_id`, `courier_provider_id`, `delivery_id` nullable, `sale_id` nullable, user tracking fields.

Required fields: `id`, `uuid`, `company_id`, `courier_provider_id`, `shipment_number`, `tracking_number`, `status`, `shipping_cost DECIMAL(18,2)`, `currency_id`, global audit fields.

Unique constraints: `company_id + shipment_number`, `company_id + tracking_number` when present.

Indexes: `company_id + courier_provider_id`, `company_id + status`, `tracking_number`.

### E-commerce Integration

#### ecommerce_channels

Purpose: Stores marketplace or store channels.

Primary key: `id`

Foreign keys: `company_id`, `branch_id` nullable, `warehouse_id` nullable, `currency_id` nullable, user tracking fields.

Required fields: `id`, `uuid`, `company_id`, `name`, `channel_type`, `status`, global audit fields.

Unique constraints: `company_id + name`.

Indexes: `company_id + channel_type`, `company_id + status`.

#### ecommerce_product_mappings

Purpose: Maps ERP product variants to external products.

Primary key: `id`

Foreign keys: `company_id`, `ecommerce_channel_id`, `product_variant_id`, user tracking fields.

Required fields: `id`, `uuid`, `company_id`, `ecommerce_channel_id`, `product_variant_id`, `external_product_id`, `external_sku`, global audit fields.

Unique constraints: `company_id + ecommerce_channel_id + external_product_id`, `company_id + ecommerce_channel_id + product_variant_id`.

Indexes: `company_id + product_variant_id`, `ecommerce_channel_id`.

#### ecommerce_orders

Purpose: Stores imported external orders before conversion to ERP documents.

Primary key: `id`

Foreign keys: `company_id`, `ecommerce_channel_id`, `customer_id` nullable, `sale_id` nullable, `currency_id`, user tracking fields.

Required fields: `id`, `uuid`, `company_id`, `ecommerce_channel_id`, `external_order_id`, `order_date`, `status`, `subtotal DECIMAL(18,2)`, `tax_total DECIMAL(18,2)`, `grand_total DECIMAL(18,2)`, global audit fields.

Unique constraints: `company_id + ecommerce_channel_id + external_order_id`.

Indexes: `company_id + status`, `company_id + order_date`, `ecommerce_channel_id`.

#### ecommerce_order_items

Purpose: Stores imported external order lines.

Primary key: `id`

Foreign keys: `company_id`, `ecommerce_order_id`, `product_variant_id` nullable, user tracking fields.

Required fields: `id`, `uuid`, `company_id`, `ecommerce_order_id`, `external_product_id`, `external_sku`, `quantity`, `unit_price DECIMAL(18,2)`, `line_total DECIMAL(18,2)`, global audit fields.

Unique constraints: `company_id + uuid`.

Indexes: `ecommerce_order_id`, `company_id + product_variant_id`.

### HR & Payroll

#### departments

Purpose: Stores company departments.

Primary key: `id`

Foreign keys: `company_id`, user tracking fields.

Required fields: `id`, `uuid`, `company_id`, `name`, `is_active`, global audit fields.

Unique constraints: `company_id + name`.

Indexes: `company_id + is_active`.

#### designations

Purpose: Stores employee job titles.

Primary key: `id`

Foreign keys: `company_id`, user tracking fields.

Required fields: `id`, `uuid`, `company_id`, `name`, `is_active`, global audit fields.

Unique constraints: `company_id + name`.

Indexes: `company_id + is_active`.

#### employees

Purpose: Stores employee master records.

Primary key: `id`

Foreign keys: `company_id`, `branch_id`, `department_id` nullable, `designation_id` nullable, `user_id` nullable, `currency_id` nullable, user tracking fields.

Required fields: `id`, `uuid`, `company_id`, `branch_id`, `employee_number`, `name`, `employment_status`, `joined_at`, global audit fields.

Unique constraints: `company_id + employee_number`, `company_id + user_id` when present.

Indexes: `company_id + branch_id`, `company_id + department_id`, `company_id + employment_status`.

#### attendances

Purpose: Stores employee attendance.

Primary key: `id`

Foreign keys: `company_id`, `branch_id`, `employee_id`, user tracking fields.

Required fields: `id`, `uuid`, `company_id`, `branch_id`, `employee_id`, `attendance_date`, `status`, global audit fields.

Unique constraints: `company_id + employee_id + attendance_date`.

Indexes: `company_id + branch_id + attendance_date`, `company_id + status`.

#### payroll_periods

Purpose: Defines payroll periods.

Primary key: `id`

Foreign keys: `company_id`, user tracking fields.

Required fields: `id`, `uuid`, `company_id`, `name`, `start_date`, `end_date`, `status`, global audit fields.

Unique constraints: `company_id + name`, `company_id + start_date + end_date`.

Indexes: `company_id + status`.

#### payrolls

Purpose: Stores employee payroll headers.

Primary key: `id`

Foreign keys: `company_id`, `branch_id`, `payroll_period_id`, `employee_id`, `currency_id`, user tracking fields.

Required fields: `id`, `uuid`, `company_id`, `branch_id`, `payroll_period_id`, `employee_id`, `currency_id`, `exchange_rate`, `gross_salary DECIMAL(18,2)`, `deduction_total DECIMAL(18,2)`, `net_salary DECIMAL(18,2)`, `status`, global audit fields.

Unique constraints: `company_id + payroll_period_id + employee_id`.

Indexes: `company_id + branch_id`, `company_id + status`, `payroll_period_id`.

#### payroll_items

Purpose: Stores payroll components such as allowance, deduction, tax, and bonus.

Primary key: `id`

Foreign keys: `company_id`, `payroll_id`, user tracking fields.

Required fields: `id`, `uuid`, `company_id`, `payroll_id`, `component_type`, `name`, `amount DECIMAL(18,2)`, global audit fields.

Unique constraints: `company_id + uuid`.

Indexes: `payroll_id`, `company_id + component_type`.

### Document Management

#### document_folders

Purpose: Stores document folder hierarchy.

Primary key: `id`

Foreign keys: `company_id`, `parent_id` nullable, user tracking fields.

Required fields: `id`, `uuid`, `company_id`, `name`, `is_system`, global audit fields.

Unique constraints: `company_id + parent_id + name`.

Indexes: `company_id + parent_id`.

#### documents

Purpose: Stores document metadata and file location.

Primary key: `id`

Foreign keys: `company_id`, `folder_id` nullable, user tracking fields.

Required fields: `id`, `uuid`, `company_id`, `name`, `file_name`, `mime_type`, `disk`, `path`, `size`, `documentable_type`, `documentable_id`, global audit fields.

Unique constraints: `company_id + uuid`.

Indexes: `company_id + folder_id`, `company_id + mime_type`, `documentable_type + documentable_id`.

### Reports And Dashboard

Reports and dashboards are generated from transactional data. Report data is not stored in report tables unless a future requirement explicitly approves cached reports.

Primary sources:

- Sales reports: `sales`, `sale_items`, `pos_sales`, `pos_sale_items`, `customer_payments`.
- Purchase reports: `purchases`, `purchase_items`, `supplier_payments`.
- Inventory reports: `stock_movements`.
- Accounting reports: `journal_entries`, `journal_lines`, `accounts`, `account_groups`.
- HR reports: `employees`, `attendances`, `payrolls`, `payroll_items`.
- Courier reports: `courier_shipments`.
- E-commerce reports: `ecommerce_orders`, `ecommerce_order_items`.

No `report_definitions` or `report_runs` tables are part of the initial design.

### Audit Log

#### audit_logs

Purpose: Stores append-only audit records for security and important business activity.

Primary key: `id`

Foreign keys: `company_id` nullable, `branch_id` nullable, `warehouse_id` nullable, `user_id` nullable.

Required fields: `id`, `uuid`, `company_id`, `event_name`, `action`, `auditable_type`, `auditable_uuid`, `old_values`, `new_values`, `metadata`, `ip_address`, `user_agent`, `request_id`, `occurred_at`.

Unique constraints: `uuid`.

Indexes: `company_id + occurred_at`, `company_id + event_name`, `company_id + user_id`, `auditable_type + auditable_uuid`, `request_id`.

Audited actions:

- Login
- Logout
- Sale create, update, delete
- Purchase create, update, delete
- Product and product variant update
- Inventory movement
- Payment
- Settings changes
- User changes
- Permission changes
- Accounting posting
- POS sale completion
- Payroll posting

## Relationship Summary

One-to-One:

- Company to company settings
- Branch to branch settings
- User to employee, optional

One-to-Many:

- Company to branches, warehouses, products, variants, customers, suppliers, sales, purchases, journals, stock movements, documents, audit logs.
- Product to product variants.
- Product variant to stock movements, sale items, purchase items, POS items, return items.
- Customer to sales and customer payments.
- Supplier to purchases and supplier payments.
- Warehouse to stock movements.
- Journal entry to journal lines.

Many-to-Many:

- Users to companies through `company_user`.
- Users to roles through `role_user`.
- Roles to permissions through `role_permission`.
- Product variants to attribute values through `product_variant_values`.

Polymorphic:

- `documents` attach to business records using `documentable_type` and `documentable_id`.
- `stock_movements` reference source documents using `source_type` and `source_id`.
- `journal_entries` reference source documents using `source_type` and `source_id`.

## Accounting, Inventory, Sales, And Purchase Connection

Purchase flow:

1. `purchase_orders` and `purchase_order_items` record buying intent.
2. `purchase_receipts` and `purchase_receipt_items` receive goods into a warehouse.
3. Completed receipt creates positive `stock_movements`.
4. `purchases` and `purchase_items` record supplier bills.
5. Approved or completed purchases create double-entry journals: debit inventory or expense, credit accounts payable.
6. `supplier_payments` create journals: debit accounts payable, credit cash or bank.

Sales flow:

1. `sales_orders` and `sales_order_items` record customer intent.
2. `sales` and `sale_items` record invoices.
3. `deliveries` and `delivery_items` ship stock from a warehouse.
4. Completed delivery creates negative `stock_movements`.
5. Approved or completed sales create journals: debit accounts receivable, credit revenue and tax payable.
6. Cost of goods sold entries may be created from stock movement valuation: debit COGS, credit inventory.
7. `customer_payments` create journals: debit cash or bank, credit accounts receivable.

POS flow:

1. `pos_shifts` controls cashier sessions.
2. `pos_sales` and `pos_sale_items` record retail sale headers and lines.
3. Completed POS sales create negative `stock_movements`.
4. `pos_payments` records received money.
5. Completed POS sale creates balanced journal entries for sale, tax, payment, inventory, and COGS.

Returns flow:

1. `returns` and `return_items` record sales or purchase returns.
2. Sales returns increase stock and reverse receivable, revenue, tax, COGS, and inventory effects as required.
3. Purchase returns decrease stock and reverse payable, inventory, tax, or expense effects as required.

Inventory valuation:

- `stock_movements` is the inventory source of truth.
- Current stock is calculated from movement history.
- Optional snapshots are performance projections only.
- Product variants are the stock unit, not product headers.

## Complete ERP Data Flow

1. Company, branch, warehouse, currency, fiscal year, chart of accounts, roles, permissions, and dedicated settings are configured.
2. Customers, suppliers, product categories, brands, units, attributes, products, and product variants are created.
3. Purchases create purchasing documents, stock receipts, stock movements, supplier payable journals, and supplier payments.
4. Sales create customer documents, deliveries, stock movements, receivable journals, payment journals, and courier shipments where required.
5. POS completes sale, payment, inventory, and accounting posting in one controlled workflow.
6. E-commerce imports orders and maps external products to product variants before converting to sales documents.
7. HR payroll posts salary obligations and payments through double-entry journals.
8. Documents attach to master and transaction records.
9. Audit logs capture important actions.
10. Dashboard and reports read directly from transactional tables unless future cached reports are approved.

## Performance Considerations

Indexes:

- Index every foreign key.
- Use `company_id` as the first column in tenant-scoped composite indexes.
- Index `company_id + status` for workflow tables.
- Index `company_id + date` for reporting-heavy transaction tables.
- Index `company_id + warehouse_id + product_variant_id` on `stock_movements`.
- Index `company_id + account_id` and `company_id + entry_date` on accounting tables.

Data volume:

- High-volume tables include `stock_movements`, `journal_lines`, `audit_logs`, `pos_sales`, `pos_sale_items`, `sale_items`, and `purchase_items`.
- Archiving or partitioning may be considered later for very large tenants.

Reporting:

- Reports use transactional data by default.
- Cached report tables are not included unless explicitly approved later.
- Heavy reports should use optimized joins, indexed filters, and optional read replicas before introducing cached tables.

Concurrency:

- Posting purchases, sales, POS sales, returns, payments, payroll, and stock transfers must use database transactions.
- Stock calculations must lock relevant product variant and warehouse movement scope or use safe posting sequences to avoid race conditions.
- Journal posting must validate balanced debit and credit totals before completion.

Multi-currency:

- Documents and payments store `currency_id` and `exchange_rate`.
- Journal lines store transaction and base-currency debit/credit values.
- Base currency comes from the company.
