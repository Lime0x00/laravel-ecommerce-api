# Payment Gateway API - curl Test Commands

## Quick Testing Guide

Replace `YOUR_TOKEN` with your actual JWT token from login response.

---

## 1. USER AUTHENTICATION

### Register New User
```bash
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

### Login (Get JWT Token)
```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "password123"
  }'
```

### Get Current User
```bash
curl -X GET http://localhost:8000/api/auth/me \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json"
```

---

## 2. PRODUCT MANAGEMENT

### Get All Products
```bash
curl -X GET http://localhost:8000/api/products \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json"
```

### Get Product Details
```bash
curl -X GET http://localhost:8000/api/products/1 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json"
```

### Create Product (Admin)
```bash
curl -X POST http://localhost:8000/api/products \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Premium Laptop",
    "description": "High-performance laptop for professionals",
    "price": 1299.99,
    "category_id": 1,
    "stock": 50
  }'
```

---

## 3. SHOPPING CART

### Add Product to Cart
```bash
curl -X POST http://localhost:8000/api/cart/add \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "product_id": 1,
    "quantity": 2
  }'
```

### Get Cart Contents
```bash
curl -X GET http://localhost:8000/api/cart \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json"
```

### Update Cart Item Quantity
```bash
curl -X PUT http://localhost:8000/api/cart/1 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "quantity": 5
  }'
```

### Remove Item from Cart
```bash
curl -X DELETE http://localhost:8000/api/cart/1 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json"
```

---

## 4. PAYMENT GATEWAY - CHECKOUT

### ✅ Checkout with Stripe (Success)
```bash
curl -X POST http://localhost:8000/api/orders/checkout \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "shipping_address": "123 Main Street, New York, NY 10001",
    "payment_method": "stripe",
    "payment_token": "tok_visa",
    "payment_email": "customer@example.com"
  }'
```

**Expected Response (201 Created):**
```json
{
  "success": true,
  "message": "Checkout completed successfully.",
  "data": {
    "id": 1,
    "user_id": 1,
    "status": "pending",
    "total_price": "99.99",
    "shipping_address": "123 Main Street, New York, NY 10001",
    "payment_method": "stripe",
    "payment_id": "ch_1A1A1A1A1A1A1A1A",
    "payment_status": "completed",
    "created_at": "2026-05-09T02:51:05.000000Z",
    "updated_at": "2026-05-09T02:51:05.000000Z"
  }
}
```

---

### ❌ Checkout with Invalid Card (Declined)
```bash
curl -X POST http://localhost:8000/api/orders/checkout \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "shipping_address": "456 Oak Avenue, Boston, MA 02101",
    "payment_method": "stripe",
    "payment_token": "tok_chargeDeclined",
    "payment_email": "customer2@example.com"
  }'
```

**Expected Response (422 Validation Error):**
```json
{
  "status": "error",
  "message": "Validation failed.",
  "data": {
    "payment": [
      "Payment processing failed. Please try again."
    ]
  }
}
```

---

### ❌ Checkout Missing Required Field
```bash
curl -X POST http://localhost:8000/api/orders/checkout \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "payment_method": "stripe",
    "payment_token": "tok_visa"
  }'
```

**Expected Response (422 Validation Error):**
```json
{
  "status": "error",
  "message": "Validation failed.",
  "data": {
    "shipping_address": [
      "The shipping address field is required."
    ]
  }
}
```

---

### ❌ Checkout Invalid Payment Method
```bash
curl -X POST http://localhost:8000/api/orders/checkout \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "shipping_address": "789 Pine Road, Chicago, IL 60601",
    "payment_method": "bitcoin",
    "payment_token": "tok_visa"
  }'
```

**Expected Response (422 Validation Error):**
```json
{
  "status": "error",
  "message": "Validation failed.",
  "data": {
    "payment_method": [
      "The selected payment method is invalid."
    ]
  }
}
```

---

## 5. ORDER MANAGEMENT

### Get Order History
```bash
curl -X GET http://localhost:8000/api/orders \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json"
```

**Optional Query Parameters:**
```bash
# With pagination
curl -X GET "http://localhost:8000/api/orders?page=1&per_page=10" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json"
```

### Get Specific Order
```bash
curl -X GET http://localhost:8000/api/orders/1 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json"
```

**Expected Response:**
```json
{
  "success": true,
  "message": "Order retrieved successfully.",
  "data": {
    "id": 1,
    "user_id": 1,
    "status": "pending",
    "total_price": 99.99,
    "shipping_address": "123 Main Street, New York, NY 10001",
    "payment_method": "stripe",
    "payment_id": "ch_1A1A1A1A1A1A1A1A",
    "payment_status": "completed",
    "items": [
      {
        "id": 1,
        "order_id": 1,
        "product_id": 1,
        "quantity": 2,
        "unit_price": 49.99,
        "product": {
          "id": 1,
          "name": "Product Name",
          "description": "Product description",
          "price": 49.99
        }
      }
    ],
    "created_at": "2026-05-09T02:51:05.000000Z",
    "updated_at": "2026-05-09T02:51:05.000000Z"
  }
}
```

### Get All Orders (Admin)
```bash
curl -X GET http://localhost:8000/api/admin/orders \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN" \
  -H "Content-Type: application/json"
```

---

## 6. STRIPE TEST CARDS

### Success Cards
- **Visa**: `4242 4242 4242 4242`
- **Visa (Debit)**: `4000 0566 5566 5556`
- **Mastercard**: `5555 5555 5555 4444`
- **American Express**: `3782 822463 10005`
- **Discover**: `6011 1111 1111 1117`

### Failure Cards
- **Generic Decline**: `4000 0000 0000 0002`
- **Insufficient Funds**: `4000 0000 0000 9995`
- **Lost Card**: `4000 0002 5000 3155`
- **Stolen Card**: `4000 0100 0000 0019`

### Card Details
- **Expiry Date**: Any future date (e.g., 12/25)
- **CVC**: Any 3 digits (e.g., 123)
- **Name**: Any name

**Token Format**: Use `tok_visa` or other test tokens directly in curl

---

## 7. ERROR SCENARIOS TO TEST

### Authentication Errors
```bash
# Missing token
curl -X GET http://localhost:8000/api/cart \
  -H "Content-Type: application/json"
# Expected: 401 Unauthenticated

# Invalid token
curl -X GET http://localhost:8000/api/cart \
  -H "Authorization: Bearer invalid_token" \
  -H "Content-Type: application/json"
# Expected: 401 Unauthenticated
```

### Empty Cart Checkout
```bash
# Add nothing to cart, then checkout
curl -X POST http://localhost:8000/api/orders/checkout \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "shipping_address": "123 Main St",
    "payment_method": "stripe",
    "payment_token": "tok_visa"
  }'
# Expected: 422 with message "Cart is empty"
```

### Resource Not Found
```bash
# Non-existent order
curl -X GET http://localhost:8000/api/orders/99999 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json"
# Expected: 404 Not Found
```

---

## 8. COMPLETE WORKFLOW TEST

**Step 1: Register/Login**
```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password123"}'
```

**Step 2: Add to Cart**
```bash
curl -X POST http://localhost:8000/api/cart/add \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"product_id":1,"quantity":2}'
```

**Step 3: Checkout with Payment**
```bash
curl -X POST http://localhost:8000/api/orders/checkout \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "shipping_address":"123 Main St, City, State 12345",
    "payment_method":"stripe",
    "payment_token":"tok_visa",
    "payment_email":"user@example.com"
  }'
```

**Step 4: Check Order**
```bash
curl -X GET http://localhost:8000/api/orders \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json"
```

---

## 9. USEFUL curl OPTIONS

### Pretty Print JSON Response
```bash
curl -X GET http://localhost:8000/api/orders \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" | jq .
```

### Save Response to File
```bash
curl -X GET http://localhost:8000/api/orders \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" > response.json
```

### Verbose Output (See Headers)
```bash
curl -X POST http://localhost:8000/api/orders/checkout \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{...}' \
  -v
```

### Show Only Response Headers
```bash
curl -X GET http://localhost:8000/api/orders \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -i
```

### Follow Redirects
```bash
curl -X GET http://localhost:8000/api/orders \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -L
```

---

## 10. ENVIRONMENT SETUP FOR TESTING

### Make Script Executable (Linux/Mac)
```bash
chmod +x test_payment_gateway.sh
./test_payment_gateway.sh
```

### Or Run Commands Directly
```bash
# Copy paste each curl command into your terminal
# Replace YOUR_TOKEN with actual token
```

### Using Postman Alternative
```bash
# Install httpie for prettier output
brew install httpie  # Mac
sudo apt install httpie  # Ubuntu

# Use http instead of curl
http POST http://localhost:8000/api/auth/login \
  email=user@example.com \
  password=password123
```

---

## Summary

**Test Flow:**
1. ✅ Login to get token
2. ✅ Add products to cart
3. ✅ Checkout with Stripe payment
4. ✅ Verify order created
5. ✅ Check confirmation email sent
6. ✅ Test error scenarios

**Key Validations:**
- ✅ Payment succeeds with valid card
- ✅ Payment fails with invalid card
- ✅ Order created on success
- ✅ payment_id stored from Stripe
- ✅ Confirmation email queued
- ✅ Validation errors on missing fields
- ✅ Auth required on endpoints

Good luck testing! 🎉
