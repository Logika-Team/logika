# API: Contracts and payload formats for Logika School

Date: 2026-07-10  
Project: Logika School  
Stack: WordPress REST API + `logika-core` + `logika-leads` + CRM/webhook

## 1. Document purpose

This document describes API contracts for the future WordPress implementation of Logika School:

- public lead submission from frontend;
- reference endpoints for cities and courses;
- admin actions for leads;
- server-to-server CRM integration;
- DTOs, response formats and error codes.

Main rule:

> Public frontend communicates only with WordPress endpoint. CRM API is called only server-side from WordPress.

## 2. General API rules

Base namespace:

```text
/wp-json/logika/v1
```

Data format:

- request body: JSON;
- response body: JSON;
- encoding: UTF-8;
- timestamps: ISO 8601 UTC;
- phone: normalized to E.164 where possible;
- WordPress IDs may be numeric, lead public IDs are strings.

Mandatory headers for JSON requests:

```http
Content-Type: application/json
Accept: application/json
```

Public form requests should send one of:

```http
X-WP-Nonce: <nonce>
```

or:

```http
X-Logika-Form-Token: <short_lived_form_token>
```

If standard WordPress nonce conflicts with page cache/CDN, use separate short-lived form token.

## 3. Standard response envelope

Success:

```json
{
  "success": true,
  "data": {},
  "meta": {
    "request_id": "req_20260710_abc123"
  }
}
```

Error:

```json
{
  "success": false,
  "error": {
    "code": "validation_error",
    "message": "Request contains invalid fields",
    "fields": {
      "phone": "Enter a valid phone number"
    }
  },
  "meta": {
    "request_id": "req_20260710_abc123"
  }
}
```

Rules:

- `success` is always boolean;
- `data` appears only on success;
- `error` appears only on error;
- message for public frontend does not expose internal details;
- `request_id` is written to server logs for diagnostics;
- secrets, CRM tokens, webhook signatures and stack traces are never returned.

## 4. HTTP status codes

| Status | When to use |
|---:|---|
| 200 | Successful read or action where lead was saved locally |
| 201 | New lead or entity created |
| 202 | Request accepted, processing/CRM send continues later |
| 400 | Invalid JSON or invalid request structure |
| 401 | Missing nonce/token/session on protected endpoint |
| 403 | Insufficient capability |
| 404 | Entity not found or not available |
| 409 | Idempotency conflict or duplicate |
| 422 | JSON valid but business validation failed |
| 429 | Rate limit hit |
| 500 | Internal server error |
| 503 | External service temporarily unavailable |

For public form, CRM failure after local lead save should not return `503` to user. User-facing response stays successful and lead gets internal status `failed` or `retrying`.

## 5. Error codes

| Code | Meaning |
|---|---|
| `invalid_json` | Request body is not valid JSON |
| `invalid_method` | Invalid HTTP method |
| `missing_token` | Missing nonce/form token |
| `invalid_token` | Nonce/form token failed |
| `rate_limited` | Rate limit triggered |
| `validation_error` | Field validation failed |
| `unknown_form` | Unknown `form_id` |
| `unknown_city` | City not found or inactive |
| `unknown_course` | Course not found or inactive |
| `lead_duplicate` | Duplicate request with same idempotency key |
| `lead_not_found` | Lead not found |
| `permission_denied` | Not enough permissions |
| `crm_unavailable` | CRM unavailable |
| `crm_rejected` | CRM rejected payload |
| `internal_error` | Unexpected internal error |

## 6. DTOs

### 6.1. LeadCreateRequest

Public lead submission DTO.

```json
{
  "form_id": "hero_trial_lesson",
  "name": "Name",
  "phone": "+380501234567",
  "child_age": 10,
  "city_id": 123,
  "city_slug": "kyiv",
  "city_name": "Kyiv",
  "course_id": 456,
  "course_slug": "python-start",
  "comment": "Convinient after 16:00",
  "source_url": "https://example.com/cities/kyiv/",
  "referrer": "https://google.com/",
  "utm": {
    "source": "google",
    "medium": "cpc",
    "campaign": "summer",
    "term": "programming for kids",
    "content": "hero"
  },
  "consent": {
    "accepted": true,
    "text_version": "privacy_2026_07_10"
  },
  "client": {
    "timezone": "Europe/Kyiv",
    "language": "uk",
    "screen": "1440x900"
  },
  "idempotency_key": "lead_kyiv_phone_hash_source_hash"
}
```

Validation:

| Field | Required | Rules |
|---|---|---|
| `form_id` | yes | allowlist e.g. `hero_trial_lesson`, `course_trial_lesson`, `city_trial_lesson`, `footer_callback` |
| `name` | yes | string, 2-80 chars, sanitize text |
| `phone` | yes | string, normalize to E.164 when possible |
| `child_age` | conditional | integer 4-18, required for child-focused forms |
| `city_id` / `city_slug` | no | if present, server validates active city |
| `course_id` / `course_slug` | no | if present, server validates active course |
| `comment` | no | string, max 1000 chars |
| `source_url` | yes | valid URL on current domain |
| `referrer` | no | valid URL |
| `utm` | no | strings, max 200 chars each |
| `consent.accepted` | yes | must be `true` |
| `idempotency_key` | no | generated by server if missing |

Unknown fields:

- public endpoint should ignore or reject allowlist violations;
- private fields like `crm_status`, `lead_status`, `admin_note` cannot be accepted from frontend.

### 6.2. LeadDTO

Public lead DTO returned after submit.

```json
{
  "lead_id": "ld_20260710_abc123",
  "status": "received",
  "message": "Request received"
}
```

Public statuses:

| Status | Meaning |
|---|---|
| `received` | lead accepted by site |
| `duplicate_received` | similar lead already received, CRM duplicate send skipped |

Public DTO does not include:

- CRM response;
- internal CRM lead ID;
- stack trace;
- retry state;
- admin notes.

### 6.3. LeadAdminDTO

Admin lead DTO.

```json
{
  "lead_id": "ld_20260710_abc123",
  "wp_post_id": 789,
  "created_at": "2026-07-10T10:15:00Z",
  "updated_at": "2026-07-10T10:16:02Z",
  "status": "failed",
  "crm_status": "timeout",
  "crm_attempts": 2,
  "crm_last_error": {
    "code": "timeout",
    "http_status": null,
    "message": "CRM request timed out"
  },
  "form": {
    "form_id": "hero_trial_lesson",
    "source_url": "https://example.com/cities/kyiv/"
  },
  "person": {
    "name": "Name",
    "phone_masked": "+38050***4567",
    "child_age": 10
  },
  "context": {
    "city_id": 123,
    "city_name": "Kyiv",
    "course_id": 456,
    "course_name": "Python Start"
  }
}
```

Admin DTO can optionally include:

- `crm_payload_preview` only if audit policy allows masking;
- `utm` in full key-value form;
- `hidden_fields` parsed into normalized shape.

### 6.4. CityDTO

```json
{
  "id": 123,
  "slug": "kyiv",
  "name": "Kyiv",
  "region": "Kyiv region",
  "url": "https://example.com/cities/kyiv/",
  "is_active": true,
  "has_branches": true,
  "coordinates": {
    "lat": 50.4501,
    "lng": 30.5234
  }
}
```

### 6.5. CourseDTO

```json
{
  "id": 456,
  "slug": "python-start",
  "title": "Python Start",
  "age_range": "12-14",
  "direction": "programming",
  "format": "online_or_offline",
  "url": "https://example.com/courses/python-start/",
  "is_active": true
}
```

## 7. Public endpoints

### 7.1. Submit lead

```http
POST /wp-json/logika/v1/leads
```

Purpose:

- accept lead from public form;
- save locally;
- send to CRM server-side;
- return safe frontend response.

Auth:

- public endpoint;
- requires nonce or form token;
- rate limited.

Request:

```json
{
  "form_id": "hero_trial_lesson",
  "name": "Name",
  "phone": "+380501234567",
  "child_age": 10,
  "city_slug": "kyiv",
  "course_slug": "python-start",
  "source_url": "https://example.com/cities/kyiv/",
  "utm": {
    "source": "google",
    "medium": "cpc",
    "campaign": "summer"
  },
  "consent": {
    "accepted": true,
    "text_version": "privacy_2026_07_10"
  }
}
```

Response `201`:

```json
{
  "success": true,
  "data": {
    "lead_id": "ld_20260710_abc123",
    "status": "received",
    "message": "Request received"
  },
  "meta": {
    "request_id": "req_20260710_def456"
  }
}
```

Validation response `422`:

```json
{
  "success": false,
  "error": {
    "code": "validation_error",
    "message": "Request contains invalid fields",
    "fields": {
      "phone": "Enter a valid phone number",
      "consent.accepted": "Consent with privacy policy is required"
    }
  },
  "meta": {
    "request_id": "req_20260710_def456"
  }
}
```

Rate limit response `429`:

```json
{
  "success": false,
  "error": {
    "code": "rate_limited",
    "message": "Too many requests. Please try again later."
  },
  "meta": {
    "request_id": "req_20260710_def456"
  }
}
```

Implementation notes:

- if local save succeeds and CRM fails, return `201` or `202`, not raw CRM error;
- store internal status as `failed` or `retrying`;
- log `request_id`, `lead_id`, CRM status and HTTP code;
- do not log full personal payload in public logs.

### 7.2. Get form token

```http
GET /wp-json/logika/v1/forms/token?form_id=hero_trial_lesson
```

Purpose:

- refresh short-lived token when page cache makes embedded nonce stale.

Auth:

- public endpoint;
- rate limited.

Response `200`:

```json
{
  "success": true,
  "data": {
    "form_id": "hero_trial_lesson",
    "token": "ft_abc123",
    "expires_in": 900
  },
  "meta": {
    "request_id": "req_20260710_token"
  }
}
```

This endpoint must not return CRM settings or private form configuration.

### 7.3. List active cities

```http
GET /wp-json/logika/v1/cities
```

Purpose:

- provide city selector and map data.

Query params:

| Param | Type | Default | Notes |
|---|---|---|---|
| `region` | string | none | filter by region slug |
| `has_branches` | boolean | none | only cities with active branches |
| `search` | string | none | server-side sanitized search |

Response `200`:

```json
{
  "success": true,
  "data": [
    {
      "id": 123,
      "slug": "kyiv",
      "name": "Kyiv",
      "region": "Kyiv region",
      "url": "https://example.com/cities/kyiv/",
      "is_active": true,
      "has_branches": true,
      "coordinates": {
        "lat": 50.4501,
        "lng": 30.5234
      }
    }
  ],
  "meta": {
    "request_id": "req_20260710_cities",
    "count": 1
  }
}
```

Public city endpoints must not expose draft, review-only or internal SEO fields.

### 7.4. List active courses

```http
GET /wp-json/logika/v1/courses
```

Purpose:

- provide course selector and form context.

Query params:

| Param | Type | Default | Notes |
|---|---|---|---|
| `city_id` | integer | none | optional city filter |
| `city_slug` | string | none | optional city filter |
| `direction` | string | none | e.g. `programming`, `english` |
| `age` | integer | none | filter by child age |

Response `200`:

```json
{
  "success": true,
  "data": [
    {
      "id": 456,
      "slug": "python-start",
      "title": "Python Start",
      "age_range": "12-14",
      "direction": "programming",
      "format": "online_or_offline",
      "url": "https://example.com/courses/python-start/",
      "is_active": true
    }
  ],
  "meta": {
    "request_id": "req_20260710_courses",
    "count": 1
  }
}
```

## 8. Admin endpoints

Admin endpoints require:

- logged-in WordPress user;
- nonce;
- capability check;
- no page cache;
- audit log for state-changing actions.

Recommended capability:

```text
manage_logika_leads
```

### 8.1. List leads

```http
GET /wp-json/logika/v1/admin/leads
```

Query params:

| Param | Type | Default |
|---|---|---|
| `status` | string | none |
| `crm_status` | string | none |
| `date_from` | ISO date | none |
| `date_to` | ISO date | none |
| `page` | integer | 1 |
| `per_page` | integer | 20, max 100 |

Response `200`:

```json
{
  "success": true,
  "data": [
    {
      "lead_id": "ld_20260710_abc123",
      "wp_post_id": 789,
      "created_at": "2026-07-10T10:15:00Z",
      "status": "failed",
      "crm_status": "timeout",
      "crm_attempts": 2,
      "person": {
        "name": "Name",
        "phone_masked": "+38050***4567",
        "child_age": 10
      }
    }
  ],
  "meta": {
    "request_id": "req_20260710_admin",
    "page": 1,
    "per_page": 20,
    "total": 1
  }
}
```

### 8.2. Get lead

```http
GET /wp-json/logika/v1/admin/leads/{lead_id}
```

Response `200`:

```json
{
  "success": true,
  "data": {
    "lead_id": "ld_20260710_abc123",
    "wp_post_id": 789,
    "created_at": "2026-07-10T10:15:00Z",
    "updated_at": "2026-07-10T10:16:02Z",
    "status": "failed",
    "crm_status": "timeout",
    "crm_attempts": 2,
    "crm_last_error": {
      "code": "timeout",
      "http_status": null,
      "message": "CRM request timed out"
    }
  },
  "meta": {
    "request_id": "req_20260710_getlead"
  }
}
```

Full phone access should be more tightly restricted than masked listing. If full phone is needed, use separate capability and explicit audit log entry.

### 8.3. Retry CRM send

```http
POST /wp-json/logika/v1/admin/leads/{lead_id}/retry
```

Purpose:

- resend failed lead to CRM.

Request:

```json
{
  "reason": "manual_retry_after_crm_recovery"
}
```

Response `200`:

```json
{
  "success": true,
  "data": {
    "lead_id": "ld_20260710_abc123",
    "status": "sent",
    "crm_status": "accepted",
    "crm_attempts": 3
  },
  "meta": {
    "request_id": "req_20260710_retry"
  }
}
```

Rules:

- retry uses original lead payload;
- retry must keep idempotency key;
- retry should not create duplicate CRM records;
- every retry writes audit log.

### 8.4. Test CRM connection

```http
POST /wp-json/logika/v1/admin/crm/test
```

Purpose:

- verify CRM connectivity without sending real user lead.

Request:

```json
{
  "mode": "dry_run"
}
```

Response `200`:

```json
{
  "success": true,
  "data": {
    "crm_status": "reachable",
    "mode": "dry_run",
    "checked_at": "2026-07-10T10:20:00Z"
  },
  "meta": {
    "request_id": "req_20260710_crmtest"
  }
}
```

Response must not include CRM tokens, shared secrets or raw auth headers.

## 9. CRM integration contract

CRM API is external and vendor-specific. Inside the project, `logika-leads` should normalize outgoing payload into internal DTO before sending.

### 9.1. CrmLeadPayload

```json
{
  "external_id": "ld_20260710_abc123",
  "idempotency_key": "lead_kyiv_phone_hash_source_hash",
  "created_at": "2026-07-10T10:15:00Z",
  "person": {
    "name": "Name",
    "phone": "+380501234567",
    "child_age": 10
  },
  "interest": {
    "city": "Kyiv",
    "city_slug": "kyiv",
    "course": "Python Start",
    "course_slug": "python-start",
    "form_id": "hero_trial_lesson"
  },
  "source": {
    "url": "https://example.com/cities/kyiv/",
    "referrer": "https://google.com/",
    "utm_source": "google",
    "utm_medium": "cpc",
    "utm_campaign": "summer",
    "utm_term": "programming for kids",
    "utm_content": "hero"
  },
  "consent": {
    "accepted": true,
    "text_version": "privacy_2026_07_10"
  }
}
```

Rules:

- send only fields needed by CRM;
- keep CRM mapping in `CrmPayloadMapper`;
- never construct CRM payload in templates or JS directly;
- use timeout;
- use idempotency key when CRM supports it;
- if CRM supports HMAC/signature, sign server-side.

### 9.2. CRM response normalization

Normalize vendor response to internal format:

```json
{
  "accepted": true,
  "crm_lead_id": "crm_12345",
  "http_status": 200,
  "message": "Accepted"
}
```

Failure:

```json
{
  "accepted": false,
  "error_code": "timeout",
  "http_status": null,
  "message": "CRM request timed out",
  "retryable": true
}
```

Do not store raw CRM response if it can include secrets or unnecessary personal data.

## 10. CRM callbacks

Use only if CRM must call back.

```http
POST /wp-json/logika/v1/crm/callback
```

Auth:

- shared secret, HMAC signature, or token;
- optional timestamp check against replay attacks.

Headers:

```http
X-Logika-Signature: sha256=<signature>
X-Logika-Timestamp: 2026-07-10T10:30:00Z
```

Request:

```json
{
  "event": "lead.accepted",
  "lead_id": "ld_20260710_abc123",
  "crm_lead_id": "crm_12345",
  "occurred_at": "2026-07-10T10:30:00Z"
}
```

Response:

```json
{
  "success": true,
  "data": {
    "received": true
  },
  "meta": {
    "request_id": "req_20260710_callback"
  }
}
```

Allowed events:

- `lead.accepted`;
- `lead.rejected`;
- `lead.updated`;
- `lead.closed`.

Unknown events should be logged and safely ignored unless business rules define otherwise.

## 11. `admin-ajax.php` fallback

If form plugin or MVP constraints require `admin-ajax.php`, use equivalent actions:

```text
wp_ajax_nopriv_logika_submit_lead
wp_ajax_logika_submit_lead
wp_ajax_logika_admin_retry_lead
wp_ajax_logika_admin_test_crm
```

The same rules apply:

- nonce/form token required;
- rate limit for public actions;
- capability check for admin actions;
- same DTOs and envelope;
- no CRM secrets in browser.

`admin-ajax.php` is transport fallback, not a different business contract.

## 12. Caching rules

Do not cache:

- `POST /leads`;
- `GET /forms/token`;
- admin endpoints;
- CRM callbacks.

Can cache cautiously:

- `GET /cities`;
- `GET /courses`;

Cacheable public read endpoints should:

- exclude draft/review/noindex private data;
- return only public fields;
- have clear invalidation after city/course changes;
- not include nonce, secrets or user-specific data.

## 13. Testing requirements

Each endpoint should have tests for:

- valid request;
- validation error;
- missing/invalid token where applicable;
- permission denied on admin endpoints;
- rate limiting on public mutating endpoints;
- no secrets in response;
- idempotency for leads;
- CRM success and CRM failure via fake CRM client.

Minimum browser smoke:

- user submits form successfully;
- user sees field-level error for invalid phone;
- CRM failure after local save shows safe message;
- admin can retry failed lead with proper permission.

## 14. Versioning policy

Current API version:

```text
v1
```

Backward compatibility:

- adding optional fields is allowed;
- removing fields requires new API version;
- changing field meaning requires new API version;
- changing envelope requires new API version;
- old endpoints stay available until all clients and integrations are migrated.

Deprecation should be explicit in docs and logs before removal.
