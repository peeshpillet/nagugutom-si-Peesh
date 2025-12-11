// Elements
const cartToggle = document.getElementById('cart-toggle');
const sideCart   = document.getElementById('side-cart');
const closeCart  = document.getElementById('close-cart');
const cartItems  = document.getElementById('cart-items');
const cartTotal  = document.getElementById('cart-total');

// Open/close side cart
cartToggle?.addEventListener('click', () => sideCart.classList.toggle('open'));
closeCart?.addEventListener('click', () => sideCart.classList.remove('open'));

// Unified function to add item to cart
function setupAddToOrderButtons(sectionSelector, hasExtras = false) {
    const buttons = document.querySelectorAll(`${sectionSelector} .add-to-order-btn`);

    buttons.forEach(btn => {
        btn.addEventListener('click', () => {
            const name      = btn.getAttribute('data-name');
            const sizesData = btn.getAttribute('data-sizes') || '';
            const sizeMap   = Object.fromEntries(
                sizesData.split(',').map(s => {
                    const [key, val] = s.split(':');
                    return [key, parseInt(val)];
                })
            );

            const defaultSize = Object.keys(sizeMap)[0];

            // ---- EXTRAS (only for ramen) ----
            let extrasSelect;
            let extrasMap   = {};
            let extrasValue = '';

            if (hasExtras) {
                extrasSelect = document.createElement('select');
                extrasSelect.classList.add('form-select', 'mb-1');

                // Always have "None"
                const noneOpt = document.createElement('option');
                noneOpt.value = '';
                noneOpt.textContent = 'None (₱0)';
                extrasSelect.appendChild(noneOpt);

                const extrasData = btn.getAttribute('data-extras') || '';
                if (extrasData.trim() !== '') {
                    // extrasData looks like: "Egg:20,Noodles:15,Pork:25"
                    extrasMap = Object.fromEntries(
                        extrasData.split(',').map(pair => {
                            const [label, val] = pair.split(':');
                            return [label, parseInt(val)];
                        })
                    );

                    Object.keys(extrasMap).forEach(label => {
                        const option = document.createElement('option');
                        option.value = label; // e.g. "Egg"
                        option.textContent = `${label} (+₱${extrasMap[label]})`;
                        extrasSelect.appendChild(option);
                    });
                }

                extrasValue = extrasSelect.value; // '' by default (None)
            }

            // Unique key for duplicate check
            const baseKey = `${name}__${defaultSize}`;
            const key     = hasExtras ? `${baseKey}__${extrasValue}` : baseKey;

            // Check if item already exists
            let existingItem = Array.from(cartItems.children).find(
                li => li.getAttribute('data-key') === key
            );

            if (existingItem) {
                const qtySpan = existingItem.querySelector('.item-qty');
                qtySpan.textContent = parseInt(qtySpan.textContent) + 1;
                updateItemPrice(existingItem);
                updateCartTotal();
                sideCart.classList.add('open');
                return;
            }

            // Create new cart item
            const li = document.createElement('li');
            li.classList.add('list-group-item', 'd-flex', 'flex-column');
            li.setAttribute('data-name', name);
            li.setAttribute('data-key', key);

            const title = document.createElement('div');
            title.textContent = name;
            title.classList.add('fw-bold', 'mb-1');

            // Size dropdown
            const sizeSelect = document.createElement('select');
            sizeSelect.classList.add('form-select', 'mb-1');
            Object.keys(sizeMap).forEach(size => {
                const option = document.createElement('option');
                option.value = size;
                option.textContent = `${size} (₱${sizeMap[size]})`;
                sizeSelect.appendChild(option);
            });
            sizeSelect.value = defaultSize;

            // Quantity controls
            const qtyDiv = document.createElement('div');
            qtyDiv.classList.add('mb-1', 'd-flex', 'align-items-center');

            const minusBtn = document.createElement('button');
            minusBtn.classList.add('btn', 'btn-sm', 'btn-secondary', 'me-2');
            minusBtn.textContent = '-';

            const qtySpan = document.createElement('span');
            qtySpan.classList.add('item-qty', 'mx-2');
            qtySpan.textContent = '1';

            const plusBtn = document.createElement('button');
            plusBtn.classList.add('btn', 'btn-sm', 'btn-secondary', 'ms-2');
            plusBtn.textContent = '+';

            qtyDiv.append(minusBtn, qtySpan, plusBtn);

            // Price & delete
            const priceDiv = document.createElement('div');
            priceDiv.classList.add('mb-1');

            const deleteBtn = document.createElement('button');
            deleteBtn.classList.add('btn', 'btn-sm', 'btn-danger');
            deleteBtn.textContent = 'Remove';

            // ------- Functions -------

            function calculateItemPrice() {
                let finalPrice = sizeMap[sizeSelect.value];

                if (hasExtras && extrasSelect && extrasSelect.value && extrasMap[extrasSelect.value]) {
                    finalPrice += extrasMap[extrasSelect.value];
                }

                return finalPrice * parseInt(qtySpan.textContent);
            }

            function updateItemPrice(item) {
                const lineTotal = calculateItemPrice();
                priceDiv.textContent = `₱${lineTotal}`;
                item.setAttribute('data-price', lineTotal);
            }

            function updateCartTotal() {
                let total = 0;
                cartItems.querySelectorAll('.list-group-item').forEach(item => {
                    total += parseInt(item.getAttribute('data-price')) || 0;
                });
                cartTotal.textContent = total;
            }

            // Event listeners
            sizeSelect.addEventListener('change', () => {
                const newSize = sizeSelect.value;
                const extraKeyPart = hasExtras && extrasSelect ? extrasSelect.value : '';
                li.setAttribute(
                    'data-key',
                    hasExtras ? `${name}__${newSize}__${extraKeyPart}` : `${name}__${newSize}`
                );
                updateItemPrice(li);
                updateCartTotal();
            });

            if (hasExtras) {
                extrasSelect.addEventListener('change', () => {
                    const extraKeyPart = extrasSelect.value;
                    li.setAttribute(
                        'data-key',
                        `${name}__${sizeSelect.value}__${extraKeyPart}`
                    );
                    updateItemPrice(li);
                    updateCartTotal();
                });
            }

            plusBtn.addEventListener('click', () => {
                qtySpan.textContent = parseInt(qtySpan.textContent) + 1;
                updateItemPrice(li);
                updateCartTotal();
            });

            minusBtn.addEventListener('click', () => {
                if (parseInt(qtySpan.textContent) > 1) {
                    qtySpan.textContent = parseInt(qtySpan.textContent) - 1;
                    updateItemPrice(li);
                    updateCartTotal();
                }
            });

            deleteBtn.addEventListener('click', () => {
                li.remove();
                updateCartTotal();
            });

            // Append elements
            li.append(title, sizeSelect);
            if (hasExtras) li.append(extrasSelect);
            li.append(qtyDiv, priceDiv, deleteBtn);

            updateItemPrice(li);
            cartItems.appendChild(li);
            updateCartTotal();

            sideCart.classList.add('open');
        });
    });
}

// Initialize
setupAddToOrderButtons('.ramen-section', true);  // ramen has extras
setupAddToOrderButtons('.apps-section', false);  // sides have no extras

// ---------------------- Checkout → checkout.php ----------------------

const checkoutBtn = document.getElementById('checkout-btn');

function getSelectedRadioValue(name) {
    const el = document.querySelector(`input[name="${name}"]:checked`);
    return el ? el.value : null;
}

function buildOrderSnapshot() {
    const items = [];

    cartItems.querySelectorAll('.list-group-item').forEach(li => {
        const name    = li.getAttribute('data-name') || '';
        const selects = li.querySelectorAll('select');
        const size    = selects[0] ? selects[0].value : null;
        const extras  = selects[1] ? selects[1].value : null;
        const qtyEl   = li.querySelector('.item-qty');
        const qty     = qtyEl ? parseInt(qtyEl.textContent) || 1 : 1;
        const lineTotal = parseInt(li.getAttribute('data-price')) || 0;

        items.push({
            name,
            size,
            extras,
            qty,
            line_total: lineTotal
        });
    });

    const total         = parseInt(cartTotal.textContent) || 0;
    const orderType     = getSelectedRadioValue('order-type');
    const paymentMethod = getSelectedRadioValue('payment-method');

    const branchId        = document.getElementById('selected-branch-id')?.value || '';
    const branchName      = document.getElementById('selected-branch-name')?.value || '';
    const province        = document.getElementById('selected-province')?.value || '';
    const city            = document.getElementById('selected-city')?.value || '';
    const barangay        = document.getElementById('selected-barangay')?.value || '';
    const deliveryAllowed = document.getElementById('delivery-allowed')?.value || '0';

    return {
        items,
        total,
        orderType,
        paymentMethod,
        location: {
            province,
            city,
            barangay,
            branchId,
            branchName,
            deliveryAllowed
        },
        createdAt: new Date().toISOString()
    };
}

checkoutBtn?.addEventListener('click', () => {
    const itemsInCart = cartItems.querySelectorAll('.list-group-item').length;
    if (itemsInCart === 0) {
        alert('Your cart is empty. Please add items before checking out.');
        return;
    }

    const orderType       = getSelectedRadioValue('order-type');
    const branchId        = document.getElementById('selected-branch-id')?.value || '';
    const deliveryAllowed = document.getElementById('delivery-allowed')?.value || '0';

    if (orderType === 'Delivery') {
        if (!branchId) {
            alert('Please check if we deliver to your area first.');
            return;
        }
        if (deliveryAllowed === '0') {
            alert('We cannot deliver to this area. Please try Pick-up instead.');
            return;
        }
    }

    const snapshot = buildOrderSnapshot();

    try {
        localStorage.setItem('rn_current_order', JSON.stringify(snapshot));
    } catch (e) {
        console.error('Failed to store order in localStorage:', e);
        alert('There was a problem preparing your checkout. Please try again.');
        return;
    }

    window.location.href = 'checkout.php';
});
