# Aevloc Agency API — AI Agent & Automation Skill (SKILLS.md)

This document provides machine-executable instructions and technical specifications for LLMs, autonomous agents, and workflow runtimes (n8n, Make, Zapier, custom scripts) to manage sub-accounts and automation on the Aevloc platform.

---

## 1. Authentication & Base URL

All requests interact with the REST API using bearer token authentication:

- **Base URL:** `https://aevloc.com/api/v1`
- **Headers:**
  - `Authorization: Bearer <AGENCY_API_KEY>`
  - `Content-Type: application/json`

API keys are provisioned in the Agency Admin portal under `/admin/api`. Keys must be kept confidential and injected via secure environment variables (`AEVLOC_API_KEY`).

---

## 2. API Endpoints

### 2.1 List Sub-Accounts

Retrieve all active and managed client sub-accounts belonging to the authenticated agency.

- **Method:** `GET`
- **Path:** `/agency/subaccounts`
- **Query Parameters:** None

#### Response (`200 OK`)
```json
{
  "subaccounts": [
    {
      "id": "org_clx0912ab0001",
      "name": "Downtown Dental Care",
      "slug": "downtown-dental",
      "plan": "agency_client",
      "createdAt": "2026-03-15T12:00:00.000Z",
      "reviewCount": 142,
      "averageRating": 4.8
    }
  ]
}
```

---

### 2.2 Create Sub-Account

Provisions a new isolated client sub-account with review firewall rules, redirect URLs, and branding.

- **Method:** `POST`
- **Path:** `/agency/subaccounts`
- **Request Body (JSON):**

| Field | Type | Required | Description |
| :--- | :--- | :--- | :--- |
| `name` | string | Yes | Display name of the business or client location. |
| `slug` | string | No | Unique URL identifier for the review funnel (`/review?org=<slug>`). If omitted, auto-generated from `name`. |
| `minRatingThreshold` | integer | No | Rating threshold (1–5) required to pass the firewall. Default: `4`. |
| `googleReviewUrl` | string | No | Direct Google Place review URL destination for positive reviews. |
| `yelpReviewUrl` | string | No | Yelp review URL destination. |
| `facebookReviewUrl`| string | No | Facebook review URL destination. |
| `notificationEmail`| string | No | Recipient for private complaint capture alerts. |
| `customDomain` | string | No | Custom white-label domain mapped to this tenant. |

#### Example Request:
```bash
curl -X POST [https://aevloc.com/api/v1/agency/subaccounts](https://aevloc.com/api/v1/agency/subaccounts) \
  -H "Authorization: Bearer $AEVLOC_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Apex Auto Detail",
    "slug": "apex-auto",
    "minRatingThreshold": 4,
    "googleReviewUrl": "[https://g.page/r/example/review](https://g.page/r/example/review)",
    "notificationEmail": "owner@apexdetail.com"
  }'
```

#### Response (`201 Created`):
```json
{
  "success": true,
  "subaccount": {
    "id": "org_sub_98124fa10",
    "name": "Apex Auto Detail",
    "slug": "apex-auto",
    "funnelUrl": "[https://aevloc.com/review?org=apex-auto](https://aevloc.com/review?org=apex-auto)",
    "createdAt": "2026-10-08T22:15:00.000Z"
  }
}
```

---

## 3. Error Handling & Status Codes

All errors return standard JSON formatted payloads:

```json
{
  "error": "Unauthorized",
  "message": "Invalid or missing agency API key."
}
```

| HTTP Code | Cause | Resolution |
| :--- | :--- | :--- |
| `401 Unauthorized` | Missing or invalid `Bearer` token. | Verify that `AEVLOC_API_KEY` is loaded and valid. |
| `403 Forbidden` | The account is suspended or lacks agency permissions. | Check agency subscription status in the Super Admin / Billing portal. |
| `409 Conflict` | The requested `slug` or name is already registered. | Pass an alternate unique `slug` or allow the server to generate one. |
| `422 Unprocessable`| Missing required parameters (e.g., empty `name`). | Provide required schema attributes. |
| `429 Too Many Req` | Rate limits exceeded (>120 requests/minute). | Back off exponentially before retrying. |

---

## 4. Agent Tool Use Definitions (Function Calling)

When implementing agentic runtimes (OpenAI Function Calling, Anthropic Tools, LangChain, or MCP), declare the following tool schemas:

```json
[
  {
    "name": "aevloc_list_subaccounts",
    "description": "Retrieve all client sub-accounts managed under the agency's Aevloc organization.",
    "parameters": {
      "type": "object",
      "properties": {},
      "required": []
    }
  },
  {
    "name": "aevloc_create_subaccount",
    "description": "Provision a new white-labeled review firewall sub-account for a client.",
    "parameters": {
      "type": "object",
      "properties": {
        "name": {
          "type": "string",
          "description": "Business or client name."
        },
        "slug": {
          "type": "string",
          "description": "Unique URL slug for the client review funnel."
        },
        "minRatingThreshold": {
          "type": "integer",
          "description": "Star rating threshold (1-5) below which feedback is captured internally.",
          "default": 4
        },
        "googleReviewUrl": {
          "type": "string",
          "description": "Direct link to the client Google Maps review dialog."
        },
        "notificationEmail": {
          "type": "string",
          "description": "Client email to receive negative feedback notifications."
        }
      },
      "required": ["name"]
    }
  }
]
```

---

## 5. Execution Rules for Autonomous Agents

1. **Idempotency Check:** Always run `aevloc_list_subaccounts` before creating a new client to verify whether a matching business name or slug already exists.
2. **Review Firewall Logic:** Ensure positive review destinations (`googleReviewUrl`) are collected before setting `minRatingThreshold` to 4 or 5 stars.
3. **Funnel Verification:** After creating an account, verify the returned `funnelUrl` is accessible and deliver it directly to the end user or client CRM workflow.
