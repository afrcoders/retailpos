<h1 align="center">RetailPOS</h1>

<p align="center">
  <strong>A modern retail management and point of sale system</strong>
</p>

<p align="center">
  <a href="#features">Features</a> •
  <a href="#getting-started">Getting Started</a> •
  <a href="#api">API</a> •
  <a href="#contributing">Contributing</a>
</p>

---

## Overview

RetailPOS is a comprehensive Point of Sale system built with Laravel. It provides everything needed to run a retail business including inventory management, sales tracking, customer management, and reporting.

## Features

- **Sales Management** — Process sales with ease
- **Inventory Control** — Track stock levels and movements
- **Customer Database** — Manage customer information and history
- **Supplier Management** — Track suppliers and purchase orders
- **Category System** — Organize products by category
- **Barcode Support** — Scan barcodes for quick checkout
- **Role-Based Access** — Control who can do what
- **Reporting** — Sales reports, inventory reports, audit logs
- **Multi-User** — Support multiple cashiers

## Tech Stack

- **Framework:** Laravel 10.x
- **PHP:** 8.2+
- **Database:** MySQL 8.0
- **Cache/Queue:** Redis 7
- **Web Server:** Nginx
- **Containerization:** Docker & Docker Compose

## Getting Started

### Prerequisites

- Docker Desktop
- Git

### Quick Start

```bash
# Clone the repository
git clone https://github.com/afrcoders/retailpos.git
cd retailpos

# Install with Make
make install

# Or manually
cp .env.example .env
docker compose up -d --build
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
```

Access the application at http://localhost:8080

### Commands

```bash
make dev      # Start development
make down     # Stop containers
make logs     # View logs
make shell    # App shell access
make fresh    # Fresh migration
make test     # Run tests
```

### Environment

```env
APP_NAME=RetailPOS
DB_HOST=mysql
DB_DATABASE=retailpos
REDIS_HOST=redis
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
```

## Models

| Model | Description |
|-------|-------------|
| `Item` | Products/inventory items |
| `Category` | Product categories |
| `Customer` | Customer records |
| `Sale` | Sales transactions |
| `SaleItem` | Line items in sales |
| `Supplier` | Vendor information |
| `PurchaseOrder` | Purchase from suppliers |
| `Stock` | Inventory stock levels |
| `StockTransaction` | Stock movements |

## API

### Items

```bash
GET    /api/items           # List items
POST   /api/items           # Create item
GET    /api/items/{id}      # Get item
PUT    /api/items/{id}      # Update item
DELETE /api/items/{id}      # Delete item
```

### Sales

```bash
GET    /api/sales           # List sales
POST   /api/sales           # Create sale
GET    /api/sales/{id}      # Get sale details
```

### Customers

```bash
GET    /api/customers       # List customers
POST   /api/customers       # Create customer
GET    /api/customers/{id}  # Get customer
```

## Project Structure

```
retailpos/
├── app/
│   ├── Http/Controllers/
│   ├── Models/
│   └── Services/
├── config/
├── database/
│   ├── migrations/
│   └── seeders/
├── docker/
├── resources/views/
├── routes/
└── tests/
```

## Contributing

1. Fork the repository
2. Create feature branch (`git checkout -b feature/amazing`)
3. Commit changes (`git commit -m 'Add feature'`)
4. Push (`git push origin feature/amazing`)
5. Open Pull Request

## License

MIT License - see [LICENSE](LICENSE)
