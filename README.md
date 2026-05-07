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

## 🚀 Installation & Setup

### Prerequisites
- PHP 8.3+
- Composer
- Docker & Docker Compose (recommended)
- MySQL 8 or PostgreSQL
- Redis (for caching and queues)

### 1. Clone the Repository
```bash
git clone <repository-url>
cd laravel-ecommerce-api
```

### 2. Install Dependencies
```bash
composer install
```

### 3. Environment Setup
```bash
cp .env.example .env
```

Edit `.env` with your database and other configurations:
```env
APP_NAME="Laravel E-Commerce API"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_ecommerce
DB_USERNAME=your_username
DB_PASSWORD=your_password

CACHE_STORE=redis
QUEUE_CONNECTION=redis

REDIS_HOST=127.0.0.1
REDIS_PORT=6379

JWT_SECRET=
```

### 4. Generate Application Key
```bash
php artisan key:generate
```

### 5. Generate JWT Secret
```bash
php artisan jwt:secret
```

### 6. Run Migrations
```bash
php artisan migrate
```

### 7. Seed Database (Optional)
```bash
php artisan db:seed
```

### 8. Serve the Application
```bash
php artisan serve
```

The API will be available at `http://localhost:8000`.

### Docker Setup (Alternative)
```bash
docker-compose up -d --build
```

---

## 🧪 Testing

### Run Tests
```bash
php artisan test
# or
vendor/bin/pest
```

### Code Quality
```bash
# Static Analysis
vendor/bin/phpstan analyze

# Code Formatting
vendor/bin/pint
```

---

## 🔐 API Authentication

This API uses JWT (JSON Web Tokens) for authentication.

### Register
```bash
POST /api/auth/register
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "role": "customer" // optional, defaults to customer
}
```

### Login
```bash
POST /api/auth/login
Content-Type: application/json

{
  "email": "john@example.com",
  "password": "password123"
}
```

Response includes `access_token`. Include this in Authorization header for protected routes:
```
Authorization: Bearer <access_token>
```

### Refresh Token
```bash
POST /api/auth/refresh
Authorization: Bearer <access_token>
```

### Get Profile
```bash
GET /api/auth/profile
Authorization: Bearer <access_token>
```

---

## 📚 API Endpoints

### Public Endpoints
- `GET /api/products` - List available products (paginated)

### Protected Endpoints (Customer)
- `GET /api/orders` - User order history (paginated)

### Admin Endpoints
- `GET /api/admin/orders` - All orders (paginated)
- `PUT /api/admin/orders/{id}/status` - Update order status

---

## 🧪 Testing & Quality Assurance
The project emphasizes automated verification using **Pest**.

```bash
# Run all tests
php artisan test

# Run Static Analysis (Larastan)
./vendor/bin/phpstan analyze

# Run Linting (Laravel Pint)
./vendor/bin/pint
```

## 📖 Key Documentation
- **API Spec:** `postman/specs/index.yaml` (OpenAPI 3.0.3 Source of Truth)

---

## 📄 License
This project is open-sourced software licensed under the [MIT license](LICENSE).
