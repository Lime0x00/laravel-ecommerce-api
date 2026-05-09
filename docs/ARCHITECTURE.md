# Technical Architecture: E-Commerce REST Ecosystem

## 1. Architectural Philosophy
This project is engineered as a **Headless REST API**, prioritizing strict decoupling between the presentation layer and business logic. The architecture adheres to **Clean Architecture** principles, utilizing specific Laravel design patterns to ensure long-term maintainability and high testability.

---

## 2. Core Design Patterns

### 2.1 Service-Repository Pattern
To prevent the common "Fat Controller" and "Fat Model" anti-patterns, the system implements a strict Service-Repository orchestration.

| Layer | Responsibility | Key Files |
| :--- | :--- | :--- |
| **Service Layer** | Business logic orchestration, validation of business rules, and side-effect management (Events/Emails). | `app/Services/*` |
| **Repository Layer** | Data persistence abstraction. Decouples the Service layer from the Eloquent ORM implementation. | `app/Repositories/*` |
| **Eloquent Models** | Domain entity definitions, attribute casting, and relational mapping. | `app/Models/*` |

**Visualization**: [N-Tier Class Diagram](visuals/class-diagram.mmd)

### 2.2 Event-Driven Architecture (EDA)
The system utilizes a decoupled workflow for non-blocking operations.
- **Primary Transaction**: The `OrderService` handles the synchronous fulfillment (payment processing, order creation).
- **Secondary Side-Effects**: Upon success, an `OrderPlaced` event is dispatched.
- **Listeners**: `SendOrderConfirmation` reacts asynchronously by dispatching the `OrderConfirmationMail` to the user via the configured SMTP server.

### 2.3 Factory Pattern (Payment Orchestration)
The system is prepared for multi-gateway payment processing via the `PaymentGatewayFactory`. This allows for dynamic resolution of payment providers (e.g., Stripe, PayPal) while keeping the `OrderService` decoupled from specific vendor implementations through the `PaymentGatewayInterface`.

---

## 3. Request Lifecycle & Pipeline

The system processes incoming HTTP requests through a standardized pipeline:

1.  **Entry Point**: `routes/api.php` resolves the URI to a specific Controller method.
2.  **Guard Layer (Middleware)**:
    - `auth:api`: Enforces stateless JWT verification.
    - `CORS/Throttle`: Manages cross-origin access and rate limiting.
3.  **Input Validation**: **FormRequests** (`app/Http/Requests`) intercept the request before it reaches the controller, ensuring payload integrity and returning `422` errors on failure.
4.  **Controller Orchestration**: The Controller receives validated data and delegates the execution to the appropriate **Service**.
5.  **Logic Execution**: The Service interacts with **Repositories** to persist or retrieve data.
6.  **Response Synthesis**: The `ApiResponse` trait ensures every response (Success or Error) is wrapped in a consistent JSON envelope.

**Visualization**: [Architectural Framework](visuals/component-diagram.mmd)

---

## 4. Domain Model & Persistence

### 4.1 Relationship Mapping
The domain follows a normalized relational structure:
- **User (1) <-> Order (*)**: A customer can place multiple orders.
- **User (1) <-> Cart (1)**: A unique persistent cart per user.
- **Category (1) <-> Product (*)**: Hierarchical catalog organization.
- **Order (1) <-> OrderItem (*)**: Transactional snapshots including price-at-time-of-purchase.

**Visualization**: [EER Relational Model](visuals/eer-diagram.mmd)

### 4.2 Data Integrity
- **Atomicity**: The checkout process uses database transactions. If inventory decrement fails, the order creation is rolled back automatically.
- **Statelessness**: The system uses **JWT** exclusively. No session cookies are used, enabling the API to be served across multiple server nodes without session synchronization issues.

**Visualization**: [Order Fulfillment Sequence](visuals/sequence-checkout.mmd)

---

## 5. Security & Authorization

### 5.1 Authentication Pipeline
- **Provider**: `php-open-source-saver/jwt-auth`.
- **Strategy**: Stateless Bearer tokens.
- **Refresh Flow**: Supports secure token rotation to minimize the risk of leaked long-lived tokens.

**Visualization**: [Auth & Sync Sequence](visuals/sequence-auth.mmd)

### 5.2 Access Control
- **RBAC**: The `role` attribute on the `User` model defines access levels.
- **Authorization Guards**: Controllers use Laravel's Authorization features (Policies/Gates) to ensure `Admin` users are the only actors allowed to mutate the catalog or update order statuses.

---

## 6. Engineering Standards
- **Testing**: **Pest PHP** is the primary verification tool.
- **Statics**: **PHPStan** (Larastan) enforces type safety.
- **Contract**: **OpenAPI 3.0.3** serves as the single source of truth for the API contract.
