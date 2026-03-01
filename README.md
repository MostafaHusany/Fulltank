# FullTank - Fleet Fuel Management System

نظام إدارة الوقود للأساطيل - FullTank

## Overview

FullTank is a comprehensive fleet fuel management system designed for Egyptian markets. It provides a complete solution for managing fuel transactions between fleet owners (clients), their drivers, and fuel stations.

## Installation

```bash
# Clone the repository
git clone <repository-url>
cd FullTank

# Install dependencies
composer install
npm install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Run migrations and seeders
php artisan migrate:fresh --seed

# Start the development server
php artisan serve
```

## Default Test Credentials

| Category | Username | Password |
|----------|----------|----------|
| Admin | `admin` | `123456` |
| Client | `fast_transport` | `123456` |
| Driver | `driver_fast_1` | `123456` |
| Worker | `worker_nasr_1` | `123456` |
| Station Manager | `station_nasr` | `123456` |

---

# Mobile API Documentation

Base URL: `/api/mobile`

All API responses follow a consistent JSON structure:

```json
{
    "status": true,
    "message": "Response message",
    "data": { ... }
}
```

**Error Response:**
```json
{
    "status": false,
    "message": "Error message",
    "errors": { ... }
}
```

## Authentication

### Login

Authenticate a driver or worker to obtain an access token.

**Endpoint:** `POST /api/mobile/login`

**Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Request Body:**
```json
{
    "username": "driver_fast_1",
    "password": "123456"
}
```

**Success Response (200):**

For **Driver**:
```json
{
    "status": true,
    "message": "تم تسجيل الدخول بنجاح",
    "data": {
        "id": 6,
        "name": "أحمد سامي",
        "username": "driver_fast_1",
        "phone": "01300000001",
        "email": "driver_fast_1@fulltank.com",
        "category": "driver",
        "picture": null,
        "vehicle_id": 1,
        "client_id": 3,
        "vehicle": {
            "id": 1,
            "plate_number": "ق ص ر 1234",
            "model": "تويوتا هايس 2022",
            "fuel_type": "بنزين 92",
            "status": "active"
        },
        "monthly_quota": {
            "amount_limit": 5000.00,
            "consumed_amount": 1200.00,
            "remaining_amount": 3800.00,
            "reset_cycle": "monthly",
            "last_reset_date": "2026-02-01"
        }
    },
    "token": "1|abcdef123456..."
}
```

For **Worker**:
```json
{
    "status": true,
    "message": "تم تسجيل الدخول بنجاح",
    "data": {
        "id": 9,
        "name": "عبدالله محمد",
        "username": "worker_nasr_1",
        "phone": "01500100001",
        "email": "worker_nasr_1@fulltank.com",
        "category": "worker",
        "picture": null,
        "worker_id": 1,
        "station_id": 1,
        "station": {
            "id": 1,
            "name": "محطة مصر للبترول - مدينة نصر",
            "address": "شارع عباس العقاد، مدينة نصر",
            "latitude": 30.0626,
            "longitude": 31.3456
        }
    },
    "token": "2|xyz789..."
}
```

**Error Response (401):**
```json
{
    "status": false,
    "message": "بيانات الدخول غير صحيحة"
}
```

---

### Logout

Revoke the current access token.

**Endpoint:** `POST /api/mobile/logout`

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Success Response (200):**
```json
{
    "status": true,
    "message": "تم تسجيل الخروج بنجاح"
}
```

---

### Get Profile

Retrieve the authenticated user's profile.

**Endpoint:** `GET /api/mobile/profile`

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Success Response (200):**
Same structure as login response data.

---

### Refresh Profile

Refresh and retrieve updated profile data.

**Endpoint:** `GET /api/mobile/profile/refresh`

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

---

## Driver Endpoints

All driver endpoints require authentication with a driver account.

**Required Headers:**
```
Authorization: Bearer {driver_token}
Accept: application/json
Content-Type: application/json
```

---

### Get Driver Dashboard

**Endpoint:** `GET /api/mobile/driver/dashboard`

**Success Response (200) - With Vehicle:**
```json
{
    "status": true,
    "data": {
        "has_vehicle": true,
        "vehicle": {
            "id": 1,
            "plate_number": "ق ص ر 1234",
            "model": "تويوتا هايس 2022",
            "fuel_type": "بنزين 92",
            "status": "active"
        },
        "quota": {
            "amount_limit": 5000.00,
            "consumed_amount": 1200.00,
            "remaining_amount": 3800.00,
            "reset_cycle": "monthly"
        }
    }
}
```

**Success Response (200) - No Vehicle:**
```json
{
    "status": true,
    "data": {
        "has_vehicle": false,
        "message": "لا يوجد مركبة مخصصة لك"
    }
}
```

---

### Create Fuel Request

Create a new fueling request with OTP code.

**Endpoint:** `POST /api/mobile/driver/request`

**Request Body:**
```json
{
    "amount": 50,
    "fuel_type_id": 2,
    "latitude": 30.0626,
    "longitude": 31.3456
}
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| amount | float | Yes | Requested liters (1-500) |
| fuel_type_id | integer | Yes | Fuel type ID |
| latitude | float | No | Driver's current latitude |
| longitude | float | No | Driver's current longitude |

**Success Response (200):**
```json
{
    "status": true,
    "message": "تم إنشاء طلب التزود بالوقود",
    "data": {
        "request_id": 15,
        "otp_code": "482910",
        "requested_liters": 50.00,
        "estimated_cost": 625.00,
        "fuel_type": "بنزين 92",
        "expires_at": "2026-02-28T18:15:00+02:00",
        "expires_in_seconds": 900
    }
}
```

**Error Response (400) - Insufficient Balance:**
```json
{
    "status": false,
    "message": "الرصيد غير كافي. المتاح: 500.00 ج.م، المطلوب: 625.00 ج.م",
    "data": {
        "available_balance": 500.00,
        "required_amount": 625.00
    }
}
```

**Error Response (400) - Quota Exceeded:**
```json
{
    "status": false,
    "message": "الكمية المطلوبة تتجاوز الحد المتبقي: 30.00 لتر",
    "data": {
        "remaining_quota": 30.00
    }
}
```

---

### Get Active Request

Check for pending/active fueling requests.

**Endpoint:** `GET /api/mobile/driver/request/active`

**Success Response (200) - Has Active Request:**
```json
{
    "status": true,
    "data": {
        "has_active_request": true,
        "request": {
            "id": 15,
            "otp_code": "482910",
            "requested_liters": 50.00,
            "estimated_cost": 625.00,
            "fuel_type": "بنزين 92",
            "expires_at": "2026-02-28T18:15:00+02:00",
            "expires_in_seconds": 540,
            "created_at": "2026-02-28T18:00:00+02:00"
        }
    }
}
```

**Success Response (200) - No Active Request:**
```json
{
    "status": true,
    "data": {
        "has_active_request": false
    }
}
```

---

### Cancel Fuel Request

Cancel a pending fueling request.

**Endpoint:** `POST /api/mobile/driver/request/{id}/cancel`

**Success Response (200):**
```json
{
    "status": true,
    "message": "تم إلغاء الطلب بنجاح"
}
```

---

### Get Request History

Retrieve past fueling requests.

**Endpoint:** `GET /api/mobile/driver/request/history`

**Success Response (200):**
```json
{
    "status": true,
    "data": {
        "requests": [
            {
                "id": 14,
                "requested_liters": 40.00,
                "estimated_cost": 500.00,
                "fuel_type": "بنزين 92",
                "status": "completed",
                "station": "محطة مصر للبترول - مدينة نصر",
                "created_at": "2026-02-27T14:30:00+02:00",
                "completed_at": "2026-02-27T14:35:00+02:00"
            },
            {
                "id": 13,
                "requested_liters": 30.00,
                "estimated_cost": 375.00,
                "fuel_type": "بنزين 92",
                "status": "cancelled",
                "station": null,
                "created_at": "2026-02-26T10:00:00+02:00",
                "completed_at": null
            }
        ]
    }
}
```

---

### Get Nearby Stations

Find fuel stations near the driver's location.

**Endpoint:** `GET /api/mobile/driver/nearby-stations`

**Query Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| lat | float | Yes | Latitude (-90 to 90) |
| lng | float | Yes | Longitude (-180 to 180) |
| radius | float | No | Search radius in km (default: 10, max: 50) |
| fuel_type_id | integer | No | Filter by fuel type |
| search | string | No | Search by name/address |
| per_page | integer | No | Results per page (5-50, default: 15) |

**Example:** `GET /api/mobile/driver/nearby-stations?lat=30.0626&lng=31.3456&radius=5`

**Success Response (200):**
```json
{
    "status": true,
    "data": {
        "stations": [
            {
                "id": 1,
                "name": "محطة مصر للبترول - مدينة نصر",
                "address": "شارع عباس العقاد، مدينة نصر",
                "nearby_landmarks": "بجوار سيتي ستارز",
                "location": {
                    "latitude": 30.0626,
                    "longitude": 31.3456
                },
                "contact": {
                    "phone_1": "01500000001",
                    "phone_2": "0222600001"
                },
                "governorate": "القاهرة",
                "district": "مدينة نصر",
                "fuel_types": [
                    {
                        "id": 1,
                        "name": "بنزين 80",
                        "price_per_liter": 11.00
                    },
                    {
                        "id": 2,
                        "name": "بنزين 92",
                        "price_per_liter": 12.50
                    }
                ],
                "distance": {
                    "value": 850,
                    "unit": "m",
                    "formatted": "850 m"
                }
            }
        ],
        "pagination": {
            "current_page": 1,
            "last_page": 1,
            "per_page": 15,
            "total": 3,
            "has_more": false
        },
        "search_params": {
            "lat": 30.0626,
            "lng": 31.3456,
            "radius": 5
        }
    }
}
```

---

### Get Station Details

**Endpoint:** `GET /api/mobile/driver/stations/{id}`

**Query Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| lat | float | No | Calculate distance from this latitude |
| lng | float | No | Calculate distance from this longitude |

**Success Response (200):**
```json
{
    "status": true,
    "data": {
        "id": 1,
        "name": "محطة مصر للبترول - مدينة نصر",
        "address": "شارع عباس العقاد، مدينة نصر",
        "nearby_landmarks": "بجوار سيتي ستارز",
        "location": {
            "latitude": 30.0626,
            "longitude": 31.3456
        },
        "contact": {
            "phone_1": "01500000001",
            "phone_2": "0222600001"
        },
        "governorate": "القاهرة",
        "district": "مدينة نصر",
        "fuel_types": [
            {
                "id": 2,
                "name": "بنزين 92",
                "price_per_liter": 12.50
            }
        ],
        "distance": {
            "value": 2.3,
            "unit": "km",
            "formatted": "2.3 km"
        }
    }
}
```

---

### Get Fuel Types

List all available fuel types.

**Endpoint:** `GET /api/mobile/driver/fuel-types`

**Success Response (200):**
```json
{
    "status": true,
    "data": {
        "fuel_types": [
            {
                "id": 1,
                "name": "بنزين 80",
                "price_per_liter": 11.00
            },
            {
                "id": 2,
                "name": "بنزين 92",
                "price_per_liter": 12.50
            },
            {
                "id": 3,
                "name": "بنزين 95",
                "price_per_liter": 14.00
            },
            {
                "id": 4,
                "name": "سولار",
                "price_per_liter": 10.00
            },
            {
                "id": 5,
                "name": "غاز طبيعي",
                "price_per_liter": 5.50
            }
        ]
    }
}
```

---

## Worker Endpoints

All worker endpoints require authentication with a worker account.

**Required Headers:**
```
Authorization: Bearer {worker_token}
Accept: application/json
Content-Type: application/json
```

---

### Get Worker Dashboard

**Endpoint:** `GET /api/mobile/worker/dashboard`

**Success Response (200):**
```json
{
    "status": true,
    "data": {
        "station": {
            "id": 1,
            "name": "محطة مصر للبترول - مدينة نصر",
            "address": "شارع عباس العقاد، مدينة نصر"
        },
        "today_stats": {
            "transactions": 12,
            "liters": 480.50,
            "amount": 6006.25
        }
    }
}
```

**Error Response (400) - Not Assigned:**
```json
{
    "status": false,
    "message": "لم يتم تعيينك لأي محطة"
}
```

---

### Verify OTP / Request

Verify a fueling request using the OTP code provided by the driver.

**Endpoint:** `POST /api/mobile/worker/verify-request`

**Request Body:**
```json
{
    "otp_code": "482910"
}
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| otp_code | string | Yes | 6-digit OTP code |

**Success Response (200):**
```json
{
    "status": true,
    "message": "جاهز للتزويد",
    "data": {
        "request_id": 15,
        "vehicle": {
            "plate_number": "ق ص ر 1234",
            "model": "تويوتا هايس 2022"
        },
        "driver": {
            "name": "أحمد سامي",
            "phone": "01300000001"
        },
        "fuel_type": "بنزين 92",
        "requested_liters": 50.00,
        "estimated_cost": 625.00,
        "expires_in_seconds": 540
    }
}
```

**Error Response (404) - Invalid OTP:**
```json
{
    "status": false,
    "message": "لم يتم العثور على الطلب"
}
```

**Error Response (400) - Expired:**
```json
{
    "status": false,
    "message": "انتهت صلاحية الطلب"
}
```

---

### Confirm Fueling

Complete the fueling transaction.

**Endpoint:** `POST /api/mobile/worker/confirm-fueling`

**Request Body:**
```json
{
    "request_id": 15,
    "actual_liters": 48.5
}
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| request_id | integer | Yes | The verified request ID |
| actual_liters | float | Yes | Actual liters dispensed (0.1-500) |

**Success Response (200):**
```json
{
    "status": true,
    "message": "تم إتمام عملية التزود بالوقود",
    "data": {
        "transaction_id": 101,
        "reference_no": "FT-ABCD1234-1709132400",
        "actual_liters": 48.50,
        "total_amount": 606.25,
        "station": "محطة مصر للبترول - مدينة نصر"
    }
}
```

**Error Response (400) - Insufficient Balance:**
```json
{
    "status": false,
    "message": "رصيد العميل غير كافي. المتاح: 500.00، المطلوب: 606.25"
}
```

---

### Upload Meter Proof

Upload pump meter image as proof of transaction.

**Endpoint:** `POST /api/mobile/worker/upload-proof`

**Content-Type:** `multipart/form-data`

**Request Body:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| transaction_id | integer | Yes | Transaction ID |
| pump_meter_image | file | Yes | Image file (JPEG/PNG, max 5MB) |

**Success Response (200):**
```json
{
    "status": true,
    "message": "تم رفع صورة العداد بنجاح",
    "data": {
        "transaction_id": 101,
        "image_path": "fuel_transactions/meters/meter_101_1709132400.jpg"
    }
}
```

---

### Get Today's Stats

Get worker's statistics for today.

**Endpoint:** `GET /api/mobile/worker/today-stats`

**Success Response (200):**
```json
{
    "status": true,
    "data": {
        "today": {
            "transactions": 8,
            "liters": 320.50,
            "amount": 4006.25
        }
    }
}
```

---

### Get Recent Transactions

Get worker's recent transactions.

**Endpoint:** `GET /api/mobile/worker/recent-transactions`

**Success Response (200):**
```json
{
    "status": true,
    "data": {
        "transactions": [
            {
                "id": 101,
                "reference_no": "FT-ABCD1234-1709132400",
                "vehicle_plate": "ق ص ر 1234",
                "fuel_type": "بنزين 92",
                "liters": 48.50,
                "amount": 606.25,
                "time": "14:35",
                "date": "2026-02-28"
            },
            {
                "id": 100,
                "reference_no": "FT-WXYZ5678-1709128800",
                "vehicle_plate": "ذ هـ ب 1111",
                "fuel_type": "سولار",
                "liters": 75.00,
                "amount": 750.00,
                "time": "13:20",
                "date": "2026-02-28"
            }
        ]
    }
}
```

---

## Error Codes

| HTTP Code | Description |
|-----------|-------------|
| 200 | Success |
| 400 | Bad Request - Invalid input or business rule violation |
| 401 | Unauthorized - Invalid or missing token |
| 403 | Forbidden - Access denied for this resource |
| 404 | Not Found - Resource does not exist |
| 422 | Validation Error - Invalid request body |
| 500 | Internal Server Error |

---

## Fueling Flow

The complete fueling process follows these steps:

```
┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│   DRIVER    │    │   SYSTEM    │    │   WORKER    │
└──────┬──────┘    └──────┬──────┘    └──────┬──────┘
       │                  │                  │
       │ Create Request   │                  │
       │─────────────────>│                  │
       │                  │                  │
       │<─────────────────│                  │
       │ OTP Code (6 dig) │                  │
       │                  │                  │
       │ Tell OTP to Worker                  │
       │────────────────────────────────────>│
       │                  │                  │
       │                  │    Verify OTP    │
       │                  │<─────────────────│
       │                  │                  │
       │                  │─────────────────>│
       │                  │ Request Details  │
       │                  │                  │
       │                  │  Confirm Fueling │
       │                  │<─────────────────│
       │                  │                  │
       │                  │ Process:         │
       │                  │ • Deduct wallet  │
       │                  │ • Update quota   │
       │                  │ • Create TX      │
       │                  │                  │
       │                  │─────────────────>│
       │                  │  Transaction ID  │
       │                  │                  │
       │                  │  Upload Proof    │
       │                  │<─────────────────│
       │                  │                  │
       └──────────────────┴──────────────────┘
```

---

## Rate Limiting

API requests are limited to prevent abuse:
- 60 requests per minute for authenticated users
- 30 requests per minute for unauthenticated requests

---

## Testing

Use the built-in API Test Lab in the Admin Panel:
1. Login as Admin: `/admin/login`
2. Navigate to: Developer Tools → API Test Lab
3. Test all endpoints with real database records

---

## Support

For technical support or questions, contact the development team.
