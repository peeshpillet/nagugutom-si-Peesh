<?php
// api/payments/paymongo-checkout.php

session_start();

header('Content-Type: application/json');

require_once __DIR__ . '/../../config/paymongo.php';

if (!defined('PAYMONGO_SECRET_KEY') || !PAYMONGO_SECRET_KEY) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'PayMongo config missing.']);
    exit;
}

// ---------- 1. Grab order data from POST ----------
$customer_name        = trim($_POST['customer_name']        ?? '');
$customer_phone       = trim($_POST['customer_phone']       ?? '');
$customer_messenger   = trim($_POST['customer_messenger']   ?? '');
$customer_email       = trim($_POST['customer_email']       ?? '');
$fulfillment_mode     = trim($_POST['fulfillment_mode']     ?? '');
$delivery_subdivision = trim($_POST['delivery_subdivision'] ?? '');
$delivery_address     = trim($_POST['delivery_address']     ?? '');
$delivery_landmark    = trim($_POST['delivery_landmark']    ?? '');
$payment_method       = trim($_POST['payment_method']       ?? 'paymongo');
$order_notes          = trim($_POST['order_notes']          ?? '');

$subtotal      = floatval($_POST['subtotal']      ?? 0);
$delivery_fee  = floatval($_POST['delivery_fee']  ?? 0);
$total_amount  = floatval($_POST['total_amount']  ?? 0);

$cart_json     = $_POST['cart_json'] ?? '[]';

// Basic amount sanity check
if ($total_amount <= 0) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid total amount for payment.']);
    exit;
}

// ---------- 2. Store everything in session for later (order-confirmed.php will use this) ----------
$_SESSION['pending_order'] = [
    'customer_name'        => $customer_name,
    'customer_phone'       => $customer_phone,
    'customer_messenger'   => $customer_messenger,
    'customer_email'       => $customer_email,
    'fulfillment_mode'     => $fulfillment_mode,
    'delivery_subdivision' => $delivery_subdivision,
    'delivery_address'     => $delivery_address,
    'delivery_landmark'    => $delivery_landmark,
    'payment_method'       => $payment_method,
    'order_notes'          => $order_notes,
    'subtotal'             => $subtotal,
    'delivery_fee'         => $delivery_fee,
    'total_amount'         => $total_amount,
    'cart_json'            => $cart_json,
];

// ---------- 3. Build Checkout Session request to PayMongo ----------

// Convert pesos to centavos (integer)
$amount_centavos = (int) round($total_amount * 100);

$success_url = 'http://localhost/nagugutom-si-Peesh/order-confirmed.php?via=paymongo';
$cancel_url  = 'http://localhost/nagugutom-si-Peesh/order-confirmed.php?paymongo_cancel=1';

// Basic "billing" info just to be nice to PayMongo
$billing = [
    'name'  => $customer_name ?: 'Ramen Naijiro Customer',
    'email' => $customer_email ?: null,
    'phone' => $customer_phone ?: null,
    'address' => [
        'line1'       => $delivery_address ?: 'San Marino',
        'line2'       => $delivery_landmark ?: '',
        'city'        => 'Dasmariñas',
        'state'       => 'Cavite',
        'postal_code' => '4114',
        'country'     => 'PH',
    ],
];

// Minimal Checkout Session body (no split_payment)
$payload = [
    'data' => [
        'attributes' => [
            'billing' => $billing,
            'cancel_url' => $cancel_url,
            'success_url' => $success_url,
            'description' => 'Ramen Naijiro Online Order',
            'payment_method_types' => [
                'card',
                'gcash',
            ],
            'line_items' => [
                [
                    'amount'      => $amount_centavos,
                    'quantity'    => 1,
                    'name'        => 'Ramen Naijiro Order',
                    'description' => 'Online food order',
                    'currency'    => 'PHP',
                ],
            ],
            'merchant'           => 'Ramen Naijiro',
            'reference_number'   => 'SD-' . time(),
            'send_email_receipt' => false,
            'show_description'   => true,
            'show_line_items'    => false,
        ],
    ],
];

// ---------- 4. cURL to PayMongo /v1/checkout_sessions ----------
$ch = curl_init(PAYMONGO_API_BASE . '/checkout_sessions');

curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'Accept: application/json',
        'Authorization: Basic ' . base64_encode(PAYMONGO_SECRET_KEY . ':'), // Secret key auth
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

// If success, PayMongo gives us a CheckoutSession with attributes.checkout_url :contentReference[oaicite:2]{index=2}
if ($httpCode >= 200 && $httpCode < 300 && isset($data['data']['attributes']['checkout_url'])) {
    $checkoutUrl = $data['data']['attributes']['checkout_url'];

    // Optional: store the Checkout Session ID too
    $_SESSION['pending_checkout_session_id'] = $data['data']['id'] ?? null;

    echo json_encode([
        'status'       => 'ok',
        'checkout_url' => $checkoutUrl,
        'raw'          => $data, // for debugging; you can remove this later
    ]);
    exit;
}

// Otherwise, error
http_response_code(500);
echo json_encode([
    'status'   => 'error',
    'message'  => 'PayMongo Checkout Session creation failed.',
    'httpCode' => $httpCode,
    'raw'      => $data,
]);
