#!/usr/bin/env bash
# File: examples/curl_examples.sh
# Description: cURL examples for listing and provisioning Aevloc sub-accounts.

set -euo pipefail

# Check for API key
if [ -z "${AEVLOC_API_KEY:-}" ]; then
  echo "Error: AEVLOC_API_KEY environment variable is not set." >&2
  exit 1
fi

BASE_URL="https://aevloc.com/api/v1"

echo "=== 1. Listing Sub-Accounts ==="
curl -s -X GET "${BASE_URL}/agency/subaccounts" \
  -H "Authorization: Bearer ${AEVLOC_API_KEY}" \
  -H "Content-Type: application/json" | jq .

echo -e "\n=== 2. Provisioning New Sub-Account ==="
curl -s -X POST "${BASE_URL}/agency/subaccounts" \
  -H "Authorization: Bearer ${AEVLOC_API_KEY}" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Bay Harbor Dental",
    "slug": "bay-harbor-dental",
    "minRatingThreshold": 4,
    "googleReviewUrl": "https://g.page/r/example/review",
    "yelpReviewUrl": "https://www.yelp.com/biz/example",
    "notificationEmail": "frontdesk@bayharbordental.com"
  }' | jq .
