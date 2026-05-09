# Laravel E-Commerce REST API

[![Laravel](https://img.shields.io/badge/Laravel-11.x-red.svg?style=flat-square&logo=laravel)](https://laravel.com)
[![JWT](https://img.shields.io/badge/Security-Stateless_JWT-blue.svg?style=flat-square)](https://jwt.io)
[![Pest](https://img.shields.io/badge/Testing-Pest_PHP-brightgreen.svg?style=flat-square)](https://pestphp.com)
[![Architecture](https://img.shields.io/badge/Pattern-Service--Repository-orange.svg?style=flat-square)](docs/ARCHITECTURE.md)

A strictly decoupled, headless e-commerce backend designed for scalability and production-grade reliability. This API implements formal software engineering patterns (Service-Repository, Factory, Event-Driven) to handle complex workflows like atomic fulfillment and cross-device cart synchronization.

---

## 🚀 Getting Started

### Prerequisites
- PHP 8.2+
- Composer
- SQLite (or your preferred database)

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/Lime0x00/laravel-ecommerce-api.git
   cd laravel-ecommerce-api
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Configure Environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   php artisan jwt:secret
   ```

4. **Database Setup**
   ```bash
   touch database/database.sqlite
   php artisan migrate --seed
   ```

5. **Start the Server**
   ```bash
   php artisan serve
   ```

---

## 🏛 Architecture & Design

This project is built with a focus on **Clean Architecture**. For a deep dive into our design decisions, please refer to the following documentation:

- 📘 [**Technical Architecture Guide**](docs/ARCHITECTURE.md): Request lifecycles, design patterns, and domain modeling.
- 📐 [**Architectural Diagrams**](docs/visuals/): Raw Mermaid (.mmd) files for Class, Sequence, EER, Deployment, **Payment Factory**, and **Mail Queue** diagrams.
- 📜 [**Architectural Report (HLD)**](docs/SE_ARCHITECTURAL_REPORT.md): Formal engineering rationale and system design trade-offs.

### Key Pillars
- **Service-Repository Pattern**: Decoupled business logic from data access.
- **Stateless JWT Security**: Effortless horizontal scaling.
- **Atomic Transactions**: Guaranteed data integrity during checkout.

---

## 🛠 Features

- **🔐 Identity**: Secure JWT-based auth and registration.
- **🛒 Cart**: Intelligent guest-to-user cart synchronization.
- **📦 Checkout**: Atomic order fulfillment with stock validation.
- **🔍 Catalog**: Paginated product discovery with category filtering.
- **🛡 Admin**: Role-Based Access Control (RBAC) for order management.

---

## 🧪 Testing & Quality

We maintain a high-fidelity verification suite using **Pest PHP**.

```bash
# Run all tests
php artisan test

# Static Analysis
./vendor/bin/phpstan analyze

# Formatting
./vendor/bin/pint
```

### 💡 Presentation Pro-Tip: Advanced Migration Strategy
If asked about the database structure during your presentation, you can demonstrate technical mastery by explaining how we resolve circular dependencies (Parent-Child tables with identical timestamps) without modifying original filenames. 

We use a **Sequential Provisioning Pipeline** to ensure 100% relational integrity:
```bash
# Atomic Provisioning Sequence
php artisan db:wipe
php artisan migrate --path=database/migrations/0001_01_01_000000_create_users_table.php
# ... (Followed by logical dependency order: Categories > Products > Carts > Orders)
php artisan db:seed
```

---

## 🤝 Contributing

We follow a strict **Feature Branch Workflow**.
1. **Branching**: `git checkout -b feature/SCRUM-ID-description` (e.g., `feature/SCRUM-99-new-logic`)
2. **Standardization**: Ensure code passes `./vendor/bin/pint` and `./vendor/bin/phpstan`.
3. **Verification**: All new logic must include Pest tests.
4. **Integration**: Submit a PR with a clear summary of architectural changes.

---

## 📄 License
Distributed under the MIT License. See `LICENSE` for more information.
