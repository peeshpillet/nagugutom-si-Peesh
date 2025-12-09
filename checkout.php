<?php
// checkout.php - Ramen Naijiro Checkout + reCAPTCHA + PayMongo

$paymongo_cancelled = isset($_GET['paymongo_cancel']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Ramen Naijiro | Checkout</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Slab:wght@100..900&display=swap" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="style.css">

    <!-- Google reCAPTCHA (v2) -->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>

<body class="d-flex flex-column min-vh-100">

    <!-- Optional PayMongo cancelled notice -->
    <?php if (!empty($paymongo_cancelled)): ?>
        <div class="alert alert-warning text-center mb-0">
            Online payment was cancelled or failed.
            Please try again.
        </div>
    <?php endif; ?>

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <img src="img/logo.jpg" alt="Ramen Naijiro Logo" class="logo-circle">
                Ramen Naijiro
            </a>
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="about-us.php">About Us</a></li>
                <li class="nav-item"><a class="nav-link" href="menu.php">Menu</a></li>
                <li class="nav-item"><a class="nav-link" href="track-order.php">Track Order</a></li>
                <li class="nav-item ms-3">
                    <a class="btn btn-warning" href="menu.php"><i class="fa-solid fa-bag-shopping me-1"></i> Order Now</a>
                </li>
            </ul>
        </div>
    </nav>
    
    <!-- PAGE HEADER -->
    <section class="text-center py-5 page-header">
        <div class="container">
            <h1 class="display-4">Checkout</h1>
            <p class="lead mb-0">Review your order and enter your details</p>
        </div>
    </section>

    <!-- CHECKOUT SECTION -->
    <section class="checkout py-4 flex-grow-1">
        <div class="container">

            <div class="row g-4">

                <!-- LEFT COLUMN: Customer Details + hidden fields for backend -->
                <div class="col-lg-7">
                    <div class="card shadow-sm p-4 checkout-card">
                        <h4 class="mb-3">Customer Information</h4>

                        <!-- One form (always PayMongo) -->
                        <form id="checkoutForm" method="post" action="order-confirmed.php" novalidate>
                            <!-- Visible fields -->
                            <div class="mb-3">
                                <label class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="custName" name="customer_name"
                                       placeholder="Juan Dela Cruz" required>
                                <div class="invalid-feedback">Please enter your full name.</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="custPhone" name="customer_phone"
                                       placeholder="09XX-XXX-XXXX" required>
                                <div class="invalid-feedback">Please enter your phone number.</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Email (for receipts)</label>
                                <input type="email" class="form-control" id="custEmail" name="customer_email"
                                       placeholder="juan@example.com" required>
                                <div class="invalid-feedback">Please enter a valid email.</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Delivery Address</label>
                                <textarea class="form-control" id="custAddress" name="delivery_address" rows="3"
                                          placeholder="House No., Street, Subdivision, City" required></textarea>
                                <div class="invalid-feedback">Please enter your address.</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Landmark (optional)</label>
                                <input type="text" class="form-control" id="custLandmark" name="delivery_landmark"
                                       placeholder="Near 7-Eleven, red gate, etc.">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Order Notes (optional)</label>
                                <textarea class="form-control" id="orderNotes" name="order_notes" rows="2"
                                          placeholder="Less spicy, no onions, call when outside..."></textarea>
                            </div>

                            <!-- Payment info (fixed: PayMongo) -->
                            <div class="mb-3">
                                <label class="form-label">Payment Method</label>
                                <input type="text" class="form-control" value="Online (PayMongo – GCash / Card)" disabled>
                            </div>

                            <!-- Summary of branch / order type (filled from localStorage) -->
                            <div class="mb-3 small text-muted" id="checkoutMetaSummary">
                                Loading branch and order type...
                            </div>

                            <!-- Hidden fields that PHP / PayMongo will use -->
                            <input type="hidden" name="payment_method" id="hiddenPaymentMethod" value="paymongo">
                            <input type="hidden" name="fulfillment_mode" id="hiddenFulfillmentMode">
                            <input type="hidden" name="branch_id" id="hiddenBranchId">
                            <input type="hidden" name="branch_name" id="hiddenBranchName">
                            <input type="hidden" name="province" id="hiddenProvince">
                            <input type="hidden" name="city" id="hiddenCity">
                            <input type="hidden" name="barangay" id="hiddenBarangay">

                            <!-- Money -->
                            <input type="hidden" name="subtotal" id="hiddenSubtotal">
                            <input type="hidden" name="delivery_fee" id="hiddenDeliveryFee" value="0">
                            <input type="hidden" name="total_amount" id="hiddenTotalAmount">

                            <!-- Cart snapshot JSON -->
                            <input type="hidden" name="cart_json" id="hiddenCartJson">

                            <div id="checkoutErrorBox" class="alert alert-danger small d-none mt-2"></div>

                            <div class="d-flex gap-2 mt-3">
                                <button type="button" class="btn btn-outline-secondary w-50"
                                        onclick="window.location.href='menu.php'">
                                    <i class="fa-solid fa-arrow-left me-2"></i> Back to Menu
                                </button>

                                <!-- This does NOT directly submit; it opens CAPTCHA flow -->
                                <button type="button" class="btn btn-warning w-50 fw-bold" id="btnCheckoutPlace">
                                    Pay & Place Order
                                </button>
                            </div>
                        </form>

                    </div>
                </div>

                <!-- RIGHT COLUMN: Order Summary (from localStorage) -->
                <div class="col-lg-5">
                    <div class="card shadow-sm p-4 checkout-card">
                        <h4 class="mb-3">Order Summary</h4>

                        <div id="checkoutOrderSummary">
                            <div class="text-muted small">Loading your cart...</div>
                        </div>

                        <div class="d-flex justify-content-between pt-3 border-top mt-3">
                            <strong>Total:</strong>
                            <strong id="checkoutTotalLabel">₱0</strong>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="bg-dark text-white py-4 mt-auto">
        <div class="container text-center">
            <p>&copy; 2025 Ramen Naijiro. All rights reserved.</p>
            <a href="https://www.facebook.com/RamenNaijiroGTC" class="text-warning text-decoration-none"><i
                    class="fa-brands fa-facebook"></i></a><br>
            <a href="admin/admin-login.php">Admin Login</a>
            <a href="contact-us.php">Contact Us</a>
        </div>
    </footer>

    <!-- CAPTCHA CONFIRMATION MODAL -->
    <div class="modal fade" id="captchaModal" tabindex="-1" aria-labelledby="captchaModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

          <div class="modal-header">
            <h5 class="modal-title" id="captchaModalLabel">
                Verify You're Human
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>

          <div class="modal-body text-center">
            <p class="small text-muted mb-2">
                Please complete the CAPTCHA to continue to payment.
            </p>

            <div id="modalCaptchaBox" class="w-100 mb-3">
                <div class="g-recaptcha"
                    data-sitekey="6LeG9RcsAAAAAGt5EdHWA29Q-sMkMRuxB1ad-e9O"
                    id="captchaContainer"></div>
            </div>

            <div id="captchaModalError" class="alert alert-danger small mt-2 d-none">
                Please verify the CAPTCHA before continuing.
            </div>
          </div>

          <div class="modal-footer">
            <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
            <button class="btn btn-warning btn-sm fw-semibold" id="captchaConfirmBtn">
                Continue to Payment
            </button>
          </div>

        </div>
      </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    // ---------------------- Load snapshot from localStorage ----------------------
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('checkoutForm');
        const errorBox = document.getElementById('checkoutErrorBox');
        const orderSummaryDiv = document.getElementById('checkoutOrderSummary');
        const totalLabel = document.getElementById('checkoutTotalLabel');
        const metaSummary = document.getElementById('checkoutMetaSummary');

        const hiddenCartJson   = document.getElementById('hiddenCartJson');
        const hiddenSubtotal   = document.getElementById('hiddenSubtotal');
        const hiddenTotal      = document.getElementById('hiddenTotalAmount');
        const hiddenDelivery   = document.getElementById('hiddenDeliveryFee');
        const hiddenFulfillment = document.getElementById('hiddenFulfillmentMode');
        const hiddenBranchId   = document.getElementById('hiddenBranchId');
        const hiddenBranchName = document.getElementById('hiddenBranchName');
        const hiddenProv       = document.getElementById('hiddenProvince');
        const hiddenCity       = document.getElementById('hiddenCity');
        const hiddenBrgy       = document.getElementById('hiddenBarangay');

        const snapshotStr = localStorage.getItem('rn_current_order');
        if (!snapshotStr) {
            alert('No active order found. Please start from the menu.');
            window.location.href = 'menu.php';
            return;
        }

        let snapshot;
        try {
            snapshot = JSON.parse(snapshotStr);
        } catch (e) {
            console.error(e);
            alert('Could not read your cart data. Please start over.');
            window.location.href = 'menu.php';
            return;
        }

        // Cart summary
        const items = snapshot.items || [];
        let subtotal = 0;

        if (!items.length) {
            orderSummaryDiv.innerHTML = '<div class="text-muted small">Your cart is empty.</div>';
        } else {
            const ul = document.createElement('ul');
            ul.className = 'list-group list-group-flush mb-3';

            items.forEach(item => {
                subtotal += item.line_total || 0;
                const li = document.createElement('li');
                li.className = 'list-group-item d-flex justify-content-between align-items-center';

                const left = document.createElement('div');
                left.innerHTML = `
                    <div><strong>${item.name}</strong></div>
                    <div class="small text-muted">
                        ${item.size ? 'Size: ' + item.size : ''}
                        ${item.extras ? (item.size ? ' • ' : '') + 'Extras: ' + item.extras : ''}
                        ${item.qty ? ' • Qty: ' + item.qty : ''}
                    </div>
                `;

                const right = document.createElement('span');
                right.textContent = '₱' + (item.line_total || 0);

                li.appendChild(left);
                li.appendChild(right);
                ul.appendChild(li);
            });

            orderSummaryDiv.innerHTML = '';
            orderSummaryDiv.appendChild(ul);
        }

        // For now: no separate delivery fee yet
        const deliveryFee = 0;
        const total = subtotal + deliveryFee;

        hiddenCartJson.value  = snapshotStr;
        hiddenSubtotal.value  = subtotal.toFixed(2);
        hiddenDelivery.value  = deliveryFee.toFixed(2);
        hiddenTotal.value     = total.toFixed(2);

        totalLabel.textContent = '₱' + total.toFixed(2);

        // Meta summary (branch + mode)
        const loc = snapshot.location || {};
        const branchName = loc.branchName || 'N/A';
        const province   = loc.province || '';
        const city       = loc.city || '';
        const barangay   = loc.barangay || '';
        const orderType  = snapshot.orderType || 'Pickup';

        metaSummary.textContent =
            `Order type: ${orderType} • Branch: ${branchName || 'Not set'} ` +
            (province ? `• Area: ${barangay}, ${city}, ${province}` : '');

        hiddenFulfillment.value = orderType;
        hiddenBranchId.value    = loc.branchId || '';
        hiddenBranchName.value  = branchName;
        hiddenProv.value        = province;
        hiddenCity.value        = city;
        hiddenBrgy.value        = barangay;

        // ---------------------- Validation + CAPTCHA flow ----------------------
        const placeBtn = document.getElementById('btnCheckoutPlace');
        const captchaModal = new bootstrap.Modal(document.getElementById('captchaModal'));
        const captchaError = document.getElementById('captchaModalError');
        const captchaConfirmBtn = document.getElementById('captchaConfirmBtn');

        function validateFormFields() {
            errorBox.classList.add('d-none');
            errorBox.textContent = '';

            if (!form.checkValidity()) {
                // Trigger browser validation UI
                form.classList.add('was-validated');
                return false;
            }

            if (!items.length) {
                errorBox.textContent = 'Your cart is empty.';
                errorBox.classList.remove('d-none');
                return false;
            }

            if (!hiddenBranchId.value && hiddenFulfillment.value === 'Delivery') {
                errorBox.textContent = 'Delivery is selected but no branch / service area was found. Please go back and set your location.';
                errorBox.classList.remove('d-none');
                return false;
            }

            return true;
        }

        placeBtn.addEventListener('click', function () {
            if (!validateFormFields()) {
                return;
            }
            // Open CAPTCHA modal
            captchaError.classList.add('d-none');
            captchaModal.show();
        });

        captchaConfirmBtn.addEventListener('click', function () {
            const captchaResponse = grecaptcha.getResponse();
            if (!captchaResponse) {
                captchaError.classList.remove('d-none');
                return;
            }

            // Always PayMongo path
            const fd = new FormData(form);
            fd.append('g-recaptcha-response', captchaResponse);

            fetch('api/payments/paymongo-checkout.php', {
                method: 'POST',
                body: fd
            })
            .then(async res => {
                let data = null;
                try {
                    data = await res.json();
                } catch (e) {
                    console.error('Invalid JSON from PayMongo endpoint', e);
                }

                if (res.ok && data && data.status === 'ok' && data.checkout_url) {
                    window.location.href = data.checkout_url;
                } else {
                    console.error('PayMongo error:', data);
                    errorBox.textContent = 'Unable to start online payment. Please try again later.';
                    errorBox.classList.remove('d-none');
                    captchaModal.hide();
                    grecaptcha.reset();
                }
            })
            .catch(err => {
                console.error('Network error calling PayMongo endpoint', err);
                errorBox.textContent = 'A network error occurred. Please check your connection and try again.';
                errorBox.classList.remove('d-none');
                captchaModal.hide();
                grecaptcha.reset();
            });
        });
    });
    </script>

</body>
</html>
