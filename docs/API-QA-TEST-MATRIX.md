# API QA Test Matrix

This checklist is focused on API reliability for:
- Authentication
- Products
- Cart
- Checkout
- Orders
- Admin order management

## Automated Coverage (Pest Feature)

- Authentication
  - register success
  - duplicate/invalid registration payloads
  - login success and invalid credentials
  - refresh token (authorized and unauthorized)
  - profile (authorized and unauthorized)
- Products
  - list with pagination
  - search and category filtering
  - show existing and missing product IDs
  - admin-only create/update/delete
  - invalid product payload validation
- Cart
  - guest and authenticated add item
  - duplicate add increments quantity
  - update quantity
  - remove item
  - show cart details/subtotals/total
  - clear cart
  - guest cart merge after login
  - merge with existing user item increments quantity
  - invalid quantity and invalid product ID validation
- Checkout and Orders
  - checkout success
  - unauthenticated checkout blocked
  - empty cart checkout blocked (422)
  - checkout validation failures
  - order and order items creation
  - cart cleared after checkout
  - list own orders
  - show own order
  - cannot access another user order
- Admin Orders
  - admin list/update status
  - customer forbidden on admin list/update

## Manual Scenarios

1. Register -> Login -> Profile -> Refresh flow
2. Guest cart operations with `session_id`
3. Login with `session_id` to verify guest cart merge
4. Add multiple products and verify cart totals/subtotals
5. Checkout and verify order payload contains items + product details
6. Verify admin order endpoints require `role=admin`
7. Verify invalid IDs return correct status/error shape

## Response Contract Checks

- Validation failures return `422` with:
  - `status = error`
  - `message = Validation failed.`
  - `data` containing field errors
- Unauthorized failures return `401`
- Forbidden failures return `403`
- Not found returns `404`

## Local QA Commands

```bash
php artisan test
vendor/bin/pest
vendor/bin/pint
```
