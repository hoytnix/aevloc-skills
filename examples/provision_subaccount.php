<?php
// File: examples/provision_subaccount.php
// Description: PHP 8+ script using cURL to inspect and provision sub-accounts.

declare(strict_types=1);

$baseUrl = 'https://aevloc.com/api/v1';
$apiKey = getenv('AEVLOC_API_KEY');

if (!$apiKey) {
    fwrite(STDERR, "Error: AEVLOC_API_KEY environment variable is missing.\n");
    exit(1);
}

/**
 * Execute an authenticated HTTP request to the Aevloc API.
 *
 * @param string $endpoint
 * @param string $method
 * @param array<string, mixed>|null $data
 * @return array<string, mixed>
 */
function apiRequest(string $endpoint, string $method = 'GET', ?array $data = null): array
{
    global $baseUrl, $apiKey;

    $ch = curl_init("{$baseUrl}{$endpoint}");
    if ($ch === false) {
        throw new RuntimeException("Failed to initialize cURL.");
    }

    $headers = [
        "Authorization: Bearer {$apiKey}",
        "Content-Type: application/json",
        "User-Agent: AevlocAgentSkill/1.0",
    ];

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    if ($data !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_THROW_ON_ERROR));
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if (curl_errno($ch)) {
        $errorMsg = curl_error($ch);
        curl_close($ch);
        throw new RuntimeException("cURL Request Error: {$errorMsg}");
    }

    curl_close($ch);

    $decoded = json_decode((string)$response, true);

    if ($httpCode >= 400) {
        $msg = $decoded['message'] ?? $decoded['error'] ?? 'Unknown error';
        throw new RuntimeException("API Error [{$httpCode}]: {$msg}");
    }

    return is_array($decoded) ? $decoded : [];
}

try {
    // 1. Fetch active sub-accounts to guarantee idempotency
    echo "Fetching active sub-accounts...\n";
    $accountsData = apiRequest('/agency/subaccounts', 'GET');
    $existingSubaccounts = $accountsData['subaccounts'] ?? [];
    
    $targetSlug = 'lakeside-chiropractic';
    $alreadyExists = false;

    foreach ($existingSubaccounts as $account) {
        if (($account['slug'] ?? '') === $targetSlug) {
            $alreadyExists = true;
            break;
        }
    }

    if ($alreadyExists) {
        echo "Sub-account '{$targetSlug}' is already registered. Skipping creation.\n";
        exit(0);
    }

    // 2. Provision new client sub-account
    $payload = [
        'name'                => 'Lakeside Chiropractic Clinic',
        'slug'                => $targetSlug,
        'minRatingThreshold'  => 4,
        'googleReviewUrl'     => 'https://g.page/r/sample-lakeside/review',
        'yelpReviewUrl'       => 'https://www.yelp.com/biz/sample-lakeside',
        'notificationEmail'   => 'office@lakesidechiro.com',
    ];

    echo "Provisioning sub-account '{$payload['name']}'...\n";
    $created = apiRequest('/agency/subaccounts', 'POST', $payload);

    echo "Sub-account provisioned successfully:\n";
    echo json_encode($created, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
} catch (Throwable $e) {
    fwrite(STDERR, "Execution failed: " . $e->getMessage() . "\n");
    exit(1);
}
