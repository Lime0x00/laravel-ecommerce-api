# API Architecture

This project follows a REST API architecture designed for scalability and maintainability.

## Core Mandates

### 1. Controllers and Responses

- All controllers inherit from the base `Controller` class.
- All API responses are handled via the `ApiResponse` trait to ensure a consistent JSON structure across the entire application.

### 2. Data Access (Repository Pattern)

- All database logic and Eloquent queries are encapsulated within the Repository Pattern.
- No direct Eloquent calls are permitted inside Controllers or Services.
- All repositories implement a Contract (Interface) and inherit from the `BaseRepository`.

### 3. Testing Standards

- Testing is required for all new features.
- All tests are written using [Pest](https://pestphp.com/).
- Feature tests use a dedicated testing database file (`database/testing.sqlite`) for speed, isolation, and data persistence between test runs. The database is automatically refreshed using the `RefreshDatabase` trait.

### 4. API Documentation

- The API is designed "Contract-First" using OpenAPI 3.0.3.
- All endpoint changes are first reflected in the Postman collections and OpenAPI specs located in the `postman/` directory.

### 5. Code Quality & Linting

- **Static Analysis:** All code must pass **Larastan** (PHPStan) at Level 5 to prevent common bugs (undefined methods, null pointer exceptions).
- **Auto-Linting:** The project uses **Laravel Pint** to enforce a consistent coding style. Developers must run `composer lint` before opening a PR.

### 6. Authentication

- The API uses stateless JWT authentication via the `php-open-source-saver/jwt-auth` package.
- No session or state is stored on the server.
- The `api` guard is the default authentication guard.

## 7. Design Patterns

### 7.1. Factory Pattern

- Used for creating objects without specifying the exact class.
- **Example:** `PaymentGatewayFactory` facilitates multiple payment providers (Stripe, PayPal) through a common `PaymentGatewayInterface`.

### 7.2. Observer Pattern

- Implemented using Laravel's **Events and Listeners**.
- Decouples the primary action from side effects.
- **Subject:** `OrderPlaced` event.
- **Observers:** `SendOrderConfirmation`, `UpdateInventory` listeners.

## 8. Architectural Diagrams (UML)

Detailed UML blueprints for the system are located in the following dedicated files:

- **[Component Diagram](component-diagram.md):** High-level system layers and decoupling.
- **[Activity Diagram](activity-checkout.md):** Logic flow for the Checkout process.
- **[State Machine Diagram](state-order.md):** Order status lifecycle transitions.
- **[Use Case Diagram](use-case.md):** High-level actor interactions and system capabilities.
- **[Class Diagram](class-diagram.md):** Static structure and database entity relationships.
- **[EER Diagram](eer-diagram.md):** Enhanced Entity-Relationship diagram with detailed attributes and types.
- **[JWT Authentication Sequence](sequence-auth.md):** Chronological flow for stateless login.
- **[Checkout Flow Sequence](sequence-checkout.md):** Detailed request flow through Middleware and Repositories.

## 9. GitFlow, QA, & Branching Strategy

To maintain repository stability and code quality, the team adheres to the following GitFlow and Quality Assurance standards.

### Branching Rules

- **Direct Commits Forbidden:** No commits are allowed directly to the `main` or `develop` branches. All work is conducted on dedicated branches.
- **Naming Conventions:**
  - For new features, branch from `develop` using the prefix: `feature/ticket-name`.
  - For bug fixes, branch from `develop` using the prefix: `bugfix/issue-name`.

### Testing Synergy & Workflow

- **Unified Branch Execution:** To prevent merge conflicts and branch sprawl, the Developer and QA operate on the same branch.
  - The **Developer** pushes the feature logic to the branch.
  - **QA** pulls the exact branch to write, execute, and commit their Pest and Postman automated tests.

### The Merge Gate (Pull Requests)

- Merges into the `develop` branch occur only via a Pull Request (PR).
- **Approval Requirements:** Every PR requires:
  1. **1 Approval from the Lead SE:** To verify compliance with architectural patterns.
  2. **Green QA Pipelines:** Confirmation from QA that all Pest and Postman tests are passing on the PR branch.
