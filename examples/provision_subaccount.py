# File: examples/provision_subaccount.py
# Description: Python script using urllib to inspect and provision sub-accounts.

import os
import json
import urllib.request
import urllib.error

BASE_URL = "https://aevloc.com/api/v1"
API_KEY = os.getenv("AEVLOC_API_KEY")

if not API_KEY:
    raise ValueError("AEVLOC_API_KEY environment variable is required.")

def request(endpoint: str, method: str = "GET", data: dict = None) -> dict:
    url = f"{BASE_URL}{endpoint}"
    headers = {
        "Authorization": f"Bearer {API_KEY}",
        "Content-Type": "application/json",
        "User-Agent": "AevlocAgentSkill/1.0"
    }
    
    payload = json.dumps(data).encode("utf-8") if data else None
    req = urllib.request.Request(url, data=payload, headers=headers, method=method)
    
    try:
        with urllib.request.urlopen(req) as response:
            return json.loads(response.read().decode("utf-8"))
    except urllib.error.HTTPError as e:
        error_body = e.read().decode("utf-8")
        raise RuntimeError(f"API Error [{e.code}]: {error_body}")

def main():
    # 1. Fetch current sub-accounts
    print("Fetching active sub-accounts...")
    accounts = request("/agency/subaccounts", method="GET")
    existing_slugs = {acc.get("slug") for acc in accounts.get("subaccounts", [])}
    print(f"Found {len(existing_slugs)} registered accounts.")

    # 2. Idempotent provision check
    target_slug = "summit-auto-detailing"
    if target_slug in existing_slugs:
        print(f"Sub-account '{target_slug}' already exists. Skipping.")
        return

    # 3. Create sub-account
    payload = {
        "name": "Summit Auto Detailing",
        "slug": target_slug,
        "minRatingThreshold": 4,
        "googleReviewUrl": "https://g.page/r/sample-summit/review",
        "notificationEmail": "service@summitautodetail.com"
    }

    print(f"Creating sub-account '{payload['name']}'...")
    created = request("/agency/subaccounts", method="POST", data=payload)
    print("Successfully provisioned:")
    print(json.dumps(created, indent=2))

if __name__ == "__main__":
    main()
