# 🛒 Laravel E-Commerce REST API

![Laravel Version](https://img.shields.io/badge/Laravel-13.x-red?style=flat-square&logo=laravel)
![PHP Version](https://img.shields.io/badge/PHP-8.3+-777bb4?style=flat-square&logo=php)
![License](https://img.shields.io/badge/License-MIT-green?style=flat-square)
![Architecture](https://img.shields.io/badge/Architecture-Clean_Shell-blue?style=flat-square)

A strictly decoupled, headless REST API foundation engineered for high scalability, stateless security, and university-grade architectural compliance. 

---

## 🎯 Project Vision
This repository provides a **Zero-Defect Architectural Shell**. It implements the structural infrastructure (Repositories, Services, Factories, Observers) and API contracts, allowing the team to focus purely on high-quality business logic implementation.

## 🏗 Architectural Pillars
- **Stateless Security:** Strict JWT implementation using `php-open-source-saver/jwt-auth`. No Sanctum, no Session state.
- **Design Patterns:** Formal enforcement of Repository, Factory, and Observer patterns.
- **Contract-First:** OpenAPI 3.0.3 specification drives all controller and route development.
- **Environment Parity:** Dockerized services (Nginx, PHP 8.3, MySQL 8, Redis) for 1:1 parity between local, test, and production environments.

---

## 🚀 Environment Readiness

### 1. Developer Setup (Docker First)
The environment is pre-configured for immediate team collaboration.

```bash
# Initialize local environment
cp .env.dev .env

# Launch core services
docker-compose up -d --build

# Generate security keys
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan jwt:secret
docker-compose exec app php artisan migrate --seed
```

### 2. QA & Tester Workflow
The API uses a **Postman-driven contract**. 
- **Collections:** Located in `postman/collections/`. Includes pre-built JWT injection scripts.
- **Environments:** Pre-configured in `postman/environments/`.
- **Validation:** Testers should run the collection against the Docker container (Port 8000).

---

## 🛠 Design Pattern Implementation
| Pattern | Implementation File | Purpose |
| :--- | :--- | :--- |
| **Repository** | `app/Repositories/` | Decouples Eloquent models from the Service layer. |
| **Factory** | `app/Factories/` | Polymorphic Payment Gateway instantiation for Stripe/PayPal. |
| **Observer** | `app/Events/` & `app/Listeners/` | Decouples Order creation from post-checkout side-effects. |

---

## 📂 Project Structure
```text
├── app/
│   ├── Factories/       # Design Pattern: Factory Implementation
│   ├── Http/
│   │   ├── Controllers/Api/ # REST Controllers (Purely structural)
│   │   └── Requests/    # Form Validation logic
│   ├── Models/          # Eloquent Models (Architectural shells)
│   ├── Repositories/    # Design Pattern: Repository implementation
│   └── Services/        # Business Logic Layer
├── database/
│   └── migrations/      # Normalized Database Schema
├── docs/                # Architectural Reports & Blueprints
├── postman/
│   ├── collections/     # Pre-configured API Tests
│   └── specs/           # OpenAPI 3.0.3 Source of Truth
└── tests/               # Pest Test Suite
```

---

## 🧪 Testing & Quality Assurance
The project emphasizes automated verification using **Pest**.

```bash
# Run all tests
docker-compose exec app php artisan test

# Run Static Analysis (Larastan)
docker-compose exec app ./vendor/bin/phpstan analyze

# Run Linting (Laravel Pint)
docker-compose exec app ./vendor/bin/pint
```

## 📖 Key Documentation
- **Architecture Blueprints:** `docs/ARCHITECTURE.md`
- **Audit Reports:** `docs/SE_ARCHITECTURAL_REPORT.md`
- **API Spec:** `postman/specs/index.yaml`

---

## 📄 License
This project is open-sourced software licensed under the [MIT license](LICENSE).
