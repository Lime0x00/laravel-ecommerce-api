#!/bin/bash
# Payment Gateway API Testing Script
# Test all endpoints with curl

# Variables
BASE_URL="http://localhost:8000/api"
AUTH_TOKEN="your_jwt_token_here"  # Replace with actual token
STRIPE_TEST_CARD="tok_visa"

echo "=========================================="
echo "Payment Gateway API Testing Script"
echo "=========================================="
echo ""

# Color codes
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Test 1: User Authentication (Get Token)
echo -e "${YELLOW}Test 1: User Login (Get JWT Token)${NC}"
curl -X POST "$BASE_URL/auth/login" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "user@example.com",
    "password": "password123"
  }' \
  -v

echo -e "\n${YELLOW}Copy the token from response above${NC}\n"
read -p "Enter your JWT token: " AUTH_TOKEN

# Test 2: Get User Profile
echo -e "\n${YELLOW}Test 2: Get User Profile${NC}"
curl -X GET "$BASE_URL/auth/me" \
  -H "Authorization: Bearer $AUTH_TOKEN" \
  -H "Content-Type: application/json" \
  -v

# Test 3: Get Products
echo -e "\n${YELLOW}Test 3: Get Products${NC}"
curl -X GET "$BASE_URL/products" \
  -H "Authorization: Bearer $AUTH_TOKEN" \
  -H "Content-Type: application/json" \
  -v

# Test 4: Add Product to Cart
echo -e "\n${YELLOW}Test 4: Add Product to Cart${NC}"
curl -X POST "$BASE_URL/cart/add" \
  -H "Authorization: Bearer $AUTH_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "product_id": 1,
    "quantity": 2
  }' \
  -v

# Test 5: Get Cart
echo -e "\n${YELLOW}Test 5: Get Cart${NC}"
curl -X GET "$BASE_URL/cart" \
  -H "Authorization: Bearer $AUTH_TOKEN" \
  -H "Content-Type: application/json" \
  -v

# Test 6: Checkout with Stripe Payment (SUCCESS)
echo -e "\n${YELLOW}Test 6: Checkout with Stripe (Success)${NC}"
curl -X POST "$BASE_URL/orders/checkout" \
  -H "Authorization: Bearer $AUTH_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "shipping_address": "123 Main Street, New York, NY 10001",
    "payment_method": "stripe",
    "payment_token": "tok_visa",
    "payment_email": "customer@example.com"
  }' \
  -v

# Test 7: Checkout with Stripe Payment (FAILURE - Declined)
echo -e "\n${YELLOW}Test 7: Checkout with Stripe (Declined Card)${NC}"
curl -X POST "$BASE_URL/orders/checkout" \
  -H "Authorization: Bearer $AUTH_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "shipping_address": "456 Oak Avenue, Boston, MA 02101",
    "payment_method": "stripe",
    "payment_token": "tok_chargeDeclined",
    "payment_email": "customer2@example.com"
  }' \
  -v

# Test 8: Get Order History
echo -e "\n${YELLOW}Test 8: Get Order History${NC}"
curl -X GET "$BASE_URL/orders" \
  -H "Authorization: Bearer $AUTH_TOKEN" \
  -H "Content-Type: application/json" \
  -v

# Test 9: Get Specific Order
echo -e "\n${YELLOW}Test 9: Get Specific Order (ID: 1)${NC}"
curl -X GET "$BASE_URL/orders/1" \
  -H "Authorization: Bearer $AUTH_TOKEN" \
  -H "Content-Type: application/json" \
  -v

# Test 10: Missing Required Field (Validation Error)
echo -e "\n${YELLOW}Test 10: Checkout Missing shipping_address (422 Error)${NC}"
curl -X POST "$BASE_URL/orders/checkout" \
  -H "Authorization: Bearer $AUTH_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "payment_method": "stripe",
    "payment_token": "tok_visa"
  }' \
  -v

# Test 11: Invalid Payment Method
echo -e "\n${YELLOW}Test 11: Checkout with Invalid Payment Method (422 Error)${NC}"
curl -X POST "$BASE_URL/orders/checkout" \
  -H "Authorization: Bearer $AUTH_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "shipping_address": "789 Pine Road, Chicago, IL 60601",
    "payment_method": "bitcoin",
    "payment_token": "tok_visa"
  }' \
  -v

echo -e "\n${GREEN}=========================================="
echo "All tests completed!"
echo "==========================================${NC}\n"
