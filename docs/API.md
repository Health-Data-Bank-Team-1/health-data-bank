# Health Data Bank API Documentation

This document describes the Health Data Bank API endpoints for **Provider** and **Admin** roles.

## Overview

The Health Data Bank API is a RESTful API built with Laravel that provides secure, authenticated access to patient health data and form management functionality. The API follows OpenAPI 3.0 specifications for consistency and compatibility with standard tools.

**For the complete API specification**, see [`api-specification.yaml`](./api-specification.yaml) in this directory.

## Table of Contents

- [Quick Start](#quick-start)
- [Authentication](#authentication)
- [Base URLs](#base-urls)
- [Endpoints Overview](#endpoints-overview)
- [Viewing the API Spec](#viewing-the-api-spec)
- [Common Patterns](#common-patterns)
- [Error Handling](#error-handling)
- [Examples](#examples)

---

## Quick Start

### 1. Obtain an API Token

Authenticate with the API to receive a Bearer token:

```bash
curl -X POST http://localhost/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "provider@example.com",
    "password": "password"
  }'
```

**Response:**
```json
{
  "token": "1|abc123xyz...",
  "user": {
    "id": "user-uuid",
    "name": "Dr. Jane Smith",
    "email": "provider@example.com",
    "role": "provider"
  }
}
```

### 2. Use the Token in Requests

Include the token in the `Authorization` header for all requests:

```bash
curl -X GET http://localhost/api/provider/patients/search \
  -H "Authorization: Bearer 1|abc123xyz..." \
  -H "Accept: application/json"
```

---

## Authentication

All API endpoints (except public endpoints) require Bearer token authentication via **Laravel Sanctum**.

### How to Authenticate

1. **Log in** using your credentials (see Quick Start above)
2. **Receive** a unique Bearer token in the response
3. **Include** the token in the `Authorization` header of all subsequent requests:

```
Authorization: Bearer YOUR_TOKEN_HERE
```

### Token Expiration & Renewal

- Tokens do not expire during a single session
- To log out, delete the token on the client side
- For long-lived sessions, implement token refresh logic

### User Roles & Permissions

The API enforces role-based access control (RBAC):

| Role | Permissions |
|------|-------------|
| **Admin** | Approve/reject forms, manage templates, view all data, system configuration |
| **Provider** | Search patients, view patient records, view dashboard, submit forms |
| **Researcher** | Access aggregated/anonymized data, generate reports |
| **Patient** | View own data, submit forms, manage preferences |

---

## Base URLs

| Environment | URL | Notes |
|-------------|-----|-------|
| **Local Development** | `http://localhost/api` | Docker Sail environment |
| **Production** | `https://api.healthdatabank.com/api` | HTTPS required |

All examples in this documentation use the **local development** URL.

---

## Endpoints Overview

### Admin Endpoints

Manage form templates, approvals, and versions. **Requires: Admin role**

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/admin/forms` | List all form templates |
| `POST` | `/admin/forms/{template}/approve` | Approve a form template |
| `POST` | `/admin/forms/{template}/reject` | Reject a form template |
| `POST` | `/admin/forms/{template}/submit` | Submit form for approval |
| `GET` | `/form-templates/{template}/versions` | View version history |
| `POST` | `/form-templates/{template}/rollback/{version}` | Rollback to previous version |

### Provider Endpoints

Manage patient data and access provider dashboard. **Requires: Provider role**

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/provider/patients/search` | Search for patients |
| `GET` | `/provider/patients/{patient}/record` | Get patient medical record |
| `GET` | `/provider/dashboard` | Get provider dashboard data |

### General Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/me/summary` | Get current user information |
| `GET` | `/user` | Get authenticated user details |

---

## Viewing the API Spec

### Option 1: Swagger UI (Interactive)

View and test endpoints interactively:

```bash
# If installed locally
docker run -p 8080:8080 \
  -e SWAGGER_JSON=/spec/api-specification.yaml \
  -v $(pwd)/docs:/spec \
  swaggerapi/swagger-ui

# Then visit: http://localhost:8080
```

### Option 2: ReDoc (Beautiful HTML)

Generate a static HTML documentation:

```bash
docker run -p 8081:8081 \
  -v $(pwd)/docs:/usr/share/nginx/html/spec \
  redoc-cli serve /spec/api-specification.yaml
```

### Option 3: Manual Review

Open `api-specification.yaml` in any text editor (YAML format is human-readable).

---

## Common Patterns

### Pagination

Many list endpoints support pagination:

```bash
curl -X GET "http://localhost/api/admin/forms?page=2&per_page=10" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Response includes:**
```json
{
  "data": [...],
  "pagination": {
    "total": 50,
    "per_page": 10,
    "current_page": 2,
    "last_page": 5
  }
}
```

### Filtering

Search and filter results:

```bash
# Search patients by name or email
curl -X GET "http://localhost/api/provider/patients/search?q=John+Doe" \
  -H "Authorization: Bearer YOUR_TOKEN"

# Filter form templates by status
curl -X GET "http://localhost/api/admin/forms?approval_status=pending" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Date Range Filtering

Filter by date ranges:

```bash
curl -X GET "http://localhost/api/provider/patients/12345/record?date_from=2024-01-01&date_to=2024-12-31" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

## Error Handling

### HTTP Status Codes

| Code | Meaning | Example |
|------|---------|---------|
| `200` | Success | Form approved |
| `201` | Created | New resource created |
| `400` | Bad Request | Validation error, missing required field |
| `401` | Unauthorized | Missing or invalid token |
| `403` | Forbidden | Insufficient permissions for role |
| `404` | Not Found | Patient/template doesn't exist |
| `409` | Conflict | Invalid state transition (e.g., approve already-approved form) |
| `422` | Unprocessable Entity | Request validation failed |
| `500` | Server Error | Unexpected error |

### Error Response Format

```json
{
  "message": "Validation failed",
  "errors": {
    "rejection_reason": ["The rejection reason field is required"]
  }
}
```

### Example Error Handling (JavaScript)

```javascript
const response = await fetch('http://localhost/api/admin/forms/template-1/approve', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({ notes: 'Approved' })
});

if (!response.ok) {
  const error = await response.json();
  console.error('API Error:', error.message);
  console.error('Errors:', error.errors);
}

const data = await response.json();
console.log('Success:', data.data);
```

---

## Troubleshooting

### "Unauthenticated" Error (401)

- Verify your token is correct
- Check the `Authorization` header format: `Bearer YOUR_TOKEN`
- Tokens may expire; request a new one if needed

### "This action is unauthorized" (403)

- Check your user role (admin vs. provider)
- Some endpoints require specific roles
- Verify your user account has the required permissions

### "Not Found" (404)

- Verify the resource exists
- Check if you're using the correct slug or ID
- Confirm the resource hasn't been deleted

### "Validation failed" (400/422)

- Review error details in the `errors` object
- Ensure all required fields are provided
- Check field types and formats match schema
