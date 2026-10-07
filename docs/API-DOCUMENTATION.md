# INVENTORA API Documentation

## Base URL
```
http://localhost:8000/api
```

## Authentication

All API endpoints (except login) require authentication using Laravel Sanctum tokens.

### Login
```http
POST /api/login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "user@example.com",
      "role": "owner"
    },
    "token": "1|abcdefghijklmnopqrstuvwxyz..."
  }
}
```

### Using the Token
Include the token in the Authorization header:
```http
GET /api/v1/products
Authorization: Bearer 1|abcdefghijklmnopqrstuvwxyz...
Accept: application/json
```

### Logout
```http
POST /api/logout
Authorization: Bearer {token}
```

### Get Current User
```http
GET /api/user
Authorization: Bearer {token}
```

### List User Tokens
```http
GET /api/tokens
Authorization: Bearer {token}
```

### Revoke Token
```http
DELETE /api/tokens/{tokenId}
Authorization: Bearer {token}
```

---

## API v1 Endpoints

All v1 endpoints require authentication and are prefixed with `/api/v1/`

### Products

#### List Products
```http
GET /api/v1/products
```

**Query Parameters:**
- `search` - Search by name or barcode
- `kategori_id` - Filter by category
- `per_page` - Items per page (default: 15)

**Response:**
```json
{
  "success": true,
  "message": "Success",
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "nama_barang": "Product Name",
        "barcode": "1234567890",
        "stok": 100,
        "harga_jual": 50000,
        "kategori_id": 1,
        "kategori": {
          "id": 1,
          "nama_kategori": "Category Name"
        }
      }
    ],
    "total": 50,
    "per_page": 15
  }
}
```

#### Get Product by ID
```http
GET /api/v1/products/{id}
```

#### Get Product by Barcode
```http
GET /api/v1/products/by-barcode/{barcode}
```

#### Adjust Stock
```http
POST /api/v1/products/{id}/adjust-stock
Authorization: Bearer {token}
Content-Type: application/json

{
  "type": "add|subtract|set",
  "quantity": 10,
  "reason": "Stock adjustment reason"
}
```

**Authorization:** Owner, Admin, or Karyawan (with policy check)

---

### Categories

#### List Categories
```http
GET /api/v1/categories
```

#### Create Category
```http
POST /api/v1/categories
Authorization: Bearer {token}
Content-Type: application/json

{
  "nama_kategori": "New Category"
}
```

#### Update Category
```http
PUT /api/v1/categories/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
  "nama_kategori": "Updated Category"
}
```

#### Delete Category
```http
DELETE /api/v1/categories/{id}
Authorization: Bearer {token}
```

**Note:** Cannot delete category with products

---

### Transactions (POS)

#### List Transactions
```http
GET /api/v1/transactions
```

**Query Parameters:**
- `date_from` - Start date (YYYY-MM-DD)
- `date_to` - End date (YYYY-MM-DD)
- `user_id` - Filter by user
- `per_page` - Items per page (default: 15)

#### Get Transaction
```http
GET /api/v1/transactions/{id}
```

#### Create Transaction
```http
POST /api/v1/transactions
Authorization: Bearer {token}
Content-Type: application/json

{
  "items": [
    {
      "product_id": 1,
      "quantity": 2
    },
    {
      "product_id": 2,
      "quantity": 1
    }
  ],
  "paid_amount": 150000
}
```

**Response:**
```json
{
  "success": true,
  "message": "Transaction created successfully",
  "data": {
    "id": 1,
    "invoice_number": "INV-20261007-ABC123",
    "tanggal": "2026-10-07",
    "total_amount": 150000,
    "paid_amount": 150000,
    "change_amount": 0,
    "user_id": 1,
    "details": [
      {
        "product_id": 1,
        "jumlah": 2,
        "harga": 50000,
        "subtotal": 100000,
        "product": { ... }
      }
    ]
  }
}
```

#### Today's Summary
```http
GET /api/v1/transactions/today-summary
```

**Response:**
```json
{
  "success": true,
  "message": "Success",
  "data": {
    "total_transactions": 25,
    "total_revenue": 5000000,
    "total_paid": 5000000
  }
}
```

---

### Stock Movements

#### List Stock Movements
```http
GET /api/v1/stock-movements
```

**Query Parameters:**
- `product_id` - Filter by product
- `type` - Filter by type (incoming, outgoing, adjustment)
- `transaction_type` - Filter by transaction type
- `date_from` - Start date
- `date_to` - End date
- `per_page` - Items per page (default: 15)

#### Get Stock Movement
```http
GET /api/v1/stock-movements/{id}
```

#### Product Stock History
```http
GET /api/v1/stock-movements/product/{productId}
```

---

### Barang Masuk (Incoming Goods)

#### List Incoming Goods
```http
GET /api/v1/barang-masuk
```

**Query Parameters:**
- `status` - Filter by status (pending, verified)
- `product_id` - Filter by product
- `per_page` - Items per page (default: 15)

**Note:** Karyawan can only see their own records

#### Create Incoming Goods
```http
POST /api/v1/barang-masuk
Authorization: Bearer {token}
Content-Type: application/json

{
  "product_id": 1,
  "jumlah": 50,
  "harga_beli": 30000,
  "keterangan": "Purchase from Supplier A"
}
```

#### Update Incoming Goods
```http
PUT /api/v1/barang-masuk/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
  "jumlah": 60,
  "harga_beli": 31000
}
```

**Authorization:** Only owner/admin or own pending record

#### Update Purchase Price
```http
POST /api/v1/barang-masuk/{id}/update-harga
Authorization: Bearer {token}
Content-Type: application/json

{
  "harga_beli": 32000
}
```

#### Verify Incoming Goods
```http
POST /api/v1/barang-masuk/{id}/verify
Authorization: Bearer {token}
```

**Authorization:** Owner only

**Note:** Price must be set before verification

#### Delete Incoming Goods
```http
DELETE /api/v1/barang-masuk/{id}
Authorization: Bearer {token}
```

**Authorization:** Owner only

---

### Barang Rusak (Damaged Goods)

#### List Damaged Goods
```http
GET /api/v1/barang-rusak
```

**Query Parameters:**
- `status` - Filter by status (menunggu, disetujui, ditolak)
- `produk_id` - Filter by product
- `per_page` - Items per page (default: 15)

#### Create Damaged Goods Report
```http
POST /api/v1/barang-rusak
Authorization: Bearer {token}
Content-Type: multipart/form-data

produk_id: 1
jumlah: 5
alasan: Damaged during shipping
foto: [image file] (optional)
```

#### Approve Damaged Goods
```http
POST /api/v1/barang-rusak/{id}/approve
Authorization: Bearer {token}
```

**Authorization:** Owner only

**Effect:** Decrements stock and creates stock movement

#### Reject Damaged Goods
```http
POST /api/v1/barang-rusak/{id}/reject
Authorization: Bearer {token}
```

**Authorization:** Owner only

#### Delete Damaged Goods
```http
DELETE /api/v1/barang-rusak/{id}
Authorization: Bearer {token}
```

**Authorization:** Owner only

---

### Retur (Returns)

#### List Returns
```http
GET /api/v1/retur
```

**Query Parameters:**
- `status` - Filter by status (menunggu, disetujui, ditolak)
- `product_id` - Filter by product
- `per_page` - Items per page (default: 15)

**Note:** Karyawan can only see their own records

#### Create Return Request
```http
POST /api/v1/retur
Authorization: Bearer {token}
Content-Type: application/json

{
  "product_id": 1,
  "jumlah": 2,
  "alasan": "Customer returned defective item"
}
```

#### Approve Return
```http
POST /api/v1/retur/{id}/approve
Authorization: Bearer {token}
```

**Authorization:** Owner only

**Effect:** Increments stock and creates stock movement

#### Reject Return
```http
POST /api/v1/retur/{id}/reject
Authorization: Bearer {token}
```

**Authorization:** Owner only

---

## Error Responses

All error responses follow this format:

```json
{
  "success": false,
  "message": "Error message",
  "errors": { ... } // Optional validation errors
}
```

**Common HTTP Status Codes:**
- `200` - Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `422` - Validation Error / Business Logic Error

---

## Authorization

The API uses Laravel Policies for resource-level authorization:

- **Owner**: Full access to all resources
- **Admin**: Can manage products, categories, verify incoming goods
- **Karyawan**: Can create incoming goods, damaged goods, returns (own records only)

---

## Testing with cURL

### Example: Login and Get Products
```bash
# Login
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email":"owner@example.com","password":"password"}'

# Response includes token: "1|abc..."

# Get products with token
curl -X GET http://localhost:8000/api/v1/products \
  -H "Authorization: Bearer 1|abc..." \
  -H "Accept: application/json"
```

### Example: Create Transaction
```bash
curl -X POST http://localhost:8000/api/v1/transactions \
  -H "Authorization: Bearer 1|abc..." \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "items": [
      {"product_id": 1, "quantity": 2}
    ],
    "paid_amount": 100000
  }'
```

---

## Notes

- All timestamps are in ISO 8601 format
- Dates are in YYYY-MM-DD format
- Monetary values are in decimal format (e.g., 50000.00)
- Pagination follows Laravel's standard format
- Stock adjustments are logged in stock_movements table
- All transactions automatically update product stock
