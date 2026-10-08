// File: examples/provision-subaccount.ts
// Description: TypeScript implementation using native fetch to manage sub-accounts.

const BASE_URL = "https://aevloc.com/api/v1";
const API_KEY = process.env.AEVLOC_API_KEY;

if (!API_KEY) {
  throw new Error("AEVLOC_API_KEY environment variable is missing.");
}

interface SubAccountInput {
  name: string;
  slug?: string;
  minRatingThreshold?: number;
  googleReviewUrl?: string;
  yelpReviewUrl?: string;
  facebookReviewUrl?: string;
  notificationEmail?: string;
}

interface ApiResponse<T> {
  success?: boolean;
  data?: T;
  error?: string;
  message?: string;
}

async function apiCall<T>(endpoint: string, options: RequestInit = {}): Promise<T> {
  const response = await fetch(`${BASE_URL}${endpoint}`, {
    ...options,
    headers: {
      "Authorization": `Bearer ${API_KEY}`,
      "Content-Type": "application/json",
      ...options.headers,
    },
  });

  const body = await response.json();
  if (!response.ok) {
    throw new Error(`API Error [${response.status}]:${body.message || body.error || "Unknown error"}`);
  }

  return body as T;
}

async function runProvisioningFlow() {
  // 1. List accounts to ensure uniqueness
  const { subaccounts } = await apiCall<{ subaccounts: Array<{ id: string; slug: string }> }>(
    "/agency/subaccounts"
  );

  const clientSlug = "grand-traverse-hvac";
  const exists = subaccounts.some((account) => account.slug === clientSlug);

  if (exists) {
    console.log(`Sub-account with slug '${clientSlug}' is already registered.`);
    return;
  }

  // 2. Provision new client
  const payload: SubAccountInput = {
    name: "Grand Traverse HVAC",
    slug: clientSlug,
    minRatingThreshold: 4,
    googleReviewUrl: "https://g.page/r/gt-hvac-example/review",
    notificationEmail: "dispatch@gthvac.com",
  };

  const result = await apiCall<{ subaccount: { id: string; funnelUrl: string } }>(
    "/agency/subaccounts",
    {
      method: "POST",
      body: JSON.stringify(payload),
    }
  );

  console.log("Client provisioned successfully:");
  console.log(`ID: ${result.subaccount.id}`);
  console.log(`Review Funnel: ${result.subaccount.funnelUrl}`);
}

runProvisioningFlow().catch(console.error);
