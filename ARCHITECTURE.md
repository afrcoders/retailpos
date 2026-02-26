# RetailPOS — Architecture & Technical Overview

## Project Summary

**RetailPOS** is a comprehensive Point of Sale system built with Laravel 10. It provides everything needed to run a retail business including inventory management, sales processing, customer management, supplier tracking, and detailed reporting.

---

## Technology Stack

| Layer | Technology |
|-------|------------|
| **Backend Framework** | Laravel 10.x (PHP 8.1+) |
| **Database** | MySQL 8.0 |
| **Caching** | Redis 7 |
| **Queue System** | Laravel Queue with Redis driver |
| **Session Management** | Redis-backed sessions |
| **Authentication** | Laravel Sanctum |
| **Web Server** | Nginx (Alpine) |
| **Containerization** | Docker & Docker Compose |
| **Frontend** | Blade Templates |
| **Code Quality** | Laravel Pint, PHPUnit |

---

## Architecture Patterns

### 1. Service Layer Pattern
Business logic encapsulated in dedicated services:

- **SalesService** — Sales transaction processing, calculations, and validation
- **StockService** — Inventory management, stock movements, and level tracking
- **AuditService** — Comprehensive audit logging for all operations

### 2. Domain-Driven Models
Rich Eloquent models representing business entities:

| Model | Purpose |
|-------|---------|
| `Item` | Products/inventory items with pricing |
| `Category` | Product categorization |
| `Customer` | Customer records and history |
| `Sale` | Sales transactions |
| `SaleItem` | Line items within sales |
| `Supplier` | Vendor/supplier information |
| `PurchaseOrder` | Orders from suppliers |
| `PurchaseOrderItem` | Line items in purchase orders |
| `Stock` | Current inventory levels |
| `StockTransaction` | Stock movement history |
| `Role` | User role definitions |
| `Setting` | System configuration |
| `AuditLog` | Activity tracking |

### 3. RESTful API Design
Complete CRUD operations via REST endpoints for all resources.

---

## Key Features Implemented

### Sales Management
- **Point of Sale Interface** — Quick checkout system
- **Multiple Payment Methods** — Cash, card, mixed payments
- **Receipt Generation** — Printable/digital receipts
- **Sales History** — Complete transaction records
- **Returns Processing** — Handle refunds and exchanges

### Inventory Control
- **Stock Tracking** — Real-time inventory levels
- **Low Stock Alerts** — Automatic notifications
- **Stock Adjustments** — Manual corrections with reasons
- **Stock Movements** — Complete audit trail
- **Barcode Support** — Quick item lookup

### Customer Management
- **Customer Database** — Store customer information
- **Purchase History** — Track customer buying patterns
- **Customer Search** — Quick lookup by name/phone
- **Loyalty Tracking** — Purchase totals per customer

### Supplier & Purchasing
- **Supplier Directory** — Manage vendor information
- **Purchase Orders** — Create and track orders
- **Receiving** — Process incoming inventory
- **Supplier History** — Order history per supplier

### Reporting & Analytics
- **Sales Reports** — Daily, weekly, monthly summaries
- **Inventory Reports** — Stock levels and valuations
- **Audit Logs** — Track all system activities
- **User Activity** — Monitor staff actions

### User & Role Management
- **Role-Based Access** — Define user permissions
- **Multi-User Support** — Concurrent cashier sessions
- **Secure Authentication** — Token-based API access

---

## Infrastructure

### Docker Services
```
┌─────────────────────────────────────────────────┐
│                 Docker Network                   │
├──────────┬──────────┬──────────┬───────────────┤
│   App    │  Nginx   │  MySQL   │    Redis      │
│ PHP-FPM  │  Proxy   │   8.0    │    7.x        │
├──────────┴──────────┴──────────┴───────────────┤
│              Queue Worker (PHP)                  │
├─────────────────────────────────────────────────┤
│              Mailpit (Dev Email)                 │
└─────────────────────────────────────────────────┘
```

### Background Processing
- Receipt email delivery
- Low stock notifications
- Report generation
- Audit log processing

---

## API Design

### Items API
```
GET    /api/items           # List items with pagination
POST   /api/items           # Create new item
GET    /api/items/{id}      # Get item details
PUT    /api/items/{id}      # Update item
DELETE /api/items/{id}      # Delete item (soft delete)
```

### Sales API
```
GET    /api/sales           # List sales
POST   /api/sales           # Create sale transaction
GET    /api/sales/{id}      # Get sale details with items
```

### Customer API
```
GET    /api/customers       # List customers
POST   /api/customers       # Create customer
GET    /api/customers/{id}  # Get customer with history
```

---

## Database Design

### Core Relationships
```
Category ──┬── Item ──┬── Stock
           │          └── SaleItem ── Sale ── Customer
           │
Supplier ──┴── PurchaseOrder ── PurchaseOrderItem

User ── Role
     ── AuditLog
```

### Transaction Integrity
- Foreign key constraints
- Database transactions for sales
- Soft deletes for data preservation
- Timestamps for audit trails

---

## Code Organization

```
app/
├── Http/
│   └── Controllers/      # API and web controllers
├── Models/
│   ├── Item.php
│   ├── Sale.php
│   ├── Stock.php
│   └── ...               # Domain models
├── Services/
│   ├── SalesService.php  # Sales processing
│   ├── StockService.php  # Inventory management
│   └── AuditService.php  # Activity logging
└── Providers/            # Service providers
```

---

## Development Practices

- **Service Pattern** — Business logic in services, not controllers
- **Audit Trail** — All significant actions logged
- **Database Transactions** — Ensure data consistency
- **Soft Deletes** — Never lose historical data
- **API Versioning Ready** — Clean API structure

---

## Skills Demonstrated

- **PHP/Laravel** — Service layer pattern, complex relationships
- **Database Design** — Normalized POS schema with integrity
- **Inventory Systems** — Stock management and tracking
- **Transaction Processing** — ACID-compliant sales operations
- **API Design** — RESTful resource-based APIs
- **Docker/DevOps** — Complete containerized environment
- **Redis** — Caching, sessions, and queues
- **Audit Logging** — Comprehensive activity tracking
- **Role-Based Access** — Permission management
- **Financial Calculations** — Tax, discounts, totals handling
