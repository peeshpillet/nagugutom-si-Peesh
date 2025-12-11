<?php
// api/payments/paymongo-intent.php
// Creates a PayMongo Payment Intent in TEST mode.

header('Content-Type: application/json');

require_once __DIR__ . '/../../config/paymongo.php';

if (!defined('PAYMONGO_SECRET_KEY') || !PAYMONGO_SECRET_KEY) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'PayMongo config missing.']);
    exit;
}

// Read data from POST (coming from checkout form)
$customerName  = $_POST['customer_name']  ?? 'Ramen Naijiro Customer';
$customerEmail = $_POST['customer_email'] ?? null;
$totalAmount   = $_POST['total_amount']   ?? null;

// Basic validation
if ($totalAmount === null) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing total_amount.']);
    exit;
}

// Convert amount (string like "345.00") to centavos
$amountPeso    = floatval($totalAmount);
$amountCentavo = (int) round($amountPeso * 100);

if ($amountCentavo <= 0) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid amount.']);
    exit;
}

// Build Payment Intent payload
// Docs: Create a Payment Intent :contentReference[oaicite:0]{index=0}
$payload = [
    'data' => [
        'attributes' => [
            'amount'                 => $amountCentavo,
            'currency'               => 'PHP',
            'description'            => "Ramen Naijiro order",
            'payment_method_allowed' => ['card', 'gcash', 'paymaya'],
            'payment_method_options' => new stdClass(), // empty object
            'statement_descriptor'   => 'Ramen Naijiro',
            'metadata'               => [
                'customer_name'  => $customerName,
                'customer_email' => $customerEmail,
            ],
        ],
    ],
];

$ch = curl_init(PAYMONGO_API_BASE . '/payment_intents');

curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        // Basic auth with secret key (key + ":")
        'Authorization: Basic ' . base64_encode(PAYMONGO_SECRET_KEY . ':'),
    ],
    CURLOPT_POSTFIELDS     => json_encode($payload),
]);

$responseBody = curl_exec($ch);
$httpCode     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($responseBody === false) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'No response from PayMongo.']);
    exit;
}

$data = json_decode($responseBody, true);

// If request succeeded (2xx)
if ($httpCode >= 200 && $httpCode < 300 && isset($data['data']['id'])) {
    $piId       = $data['data']['id'];
    $clientKey  = $data['data']['attributes']['client_key'] ?? null;
    $piStatus   = $data['data']['attributes']['status'] ?? null;

    echo json_encode([
        'status'      => 'ok',
        'payment_intent_id' => $piId,
        'client_key'  => $clientKey,
        'pi_status'   => $piStatus,
        // send raw for debugging if needed
        'raw'         => $data,
    ]);
    exit;
}

// Otherwise, error
http_response_code(500);
echo json_encode([
    'status'   => 'error',
    'message'  => 'PayMongo Payment Intent creation failed.',
    'httpCode' => $httpCode,
    'raw'      => $data,
]);
