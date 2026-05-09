# Tester Workflow

This project utilizes **Pest PHP** for a modern, expressive testing experience. All business logic and API endpoints are covered by automated tests.

## Testing Strategy

### 1. Feature Tests (`tests/Feature`)
These tests verify entire API flows and endpoint behavior.
- **Authentication**: Registration, Login, Profile, and Token Refresh.
- **Storefront**: Product listing, Category filtering, and Search.
- **Cart/Checkout**: Complex state transitions from adding to cart to finalizing an order.
- **Admin**: Role-based access control (RBAC) and inventory management.

### 2. Unit Tests (`tests/Unit`)
Focused on isolated business logic within Services or helper classes, mocking dependencies where necessary.

## Running Tests

### Standard Execution
```bash
php artisan test
```

### With Coverage
```bash
php artisan test --coverage
```

### Running Specific Suites
```bash
./vendor/bin/pest tests/Feature/AuthenticationTest.php
```

## Environment Configuration
The project uses an in-memory SQLite database or `testing.sqlite` for rapid test execution.
- **RefreshDatabase**: Most tests use this trait to ensure a clean state before each test run.
- **Factories**: Eloquent Factories are used extensively to generate mock data (Users, Products, etc.).

## Quality Standards
- All new features must include corresponding Feature tests.
- Bug fixes must be preceded by a reproduction test case.
- PRs are automatically checked via the GitHub Actions CI pipeline (`.github/workflows/ci.yml`).
