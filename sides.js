// sides.js
document.addEventListener("DOMContentLoaded", () => {
    const addToOrderBtns = document.querySelectorAll('.apps-section .add-to-order-btn');
    const cartItems = document.getElementById('cart-items');
    const cartTotal = document.getElementById('cart-total');
    const sideCart = document.getElementById('side-cart');

    addToOrderBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const name = btn.getAttribute('data-name');
            const sizesData = btn.getAttribute('data-sizes');
            const sizeMap = Object.fromEntries(
                sizesData.split(',').map(s => {
                    const [key, val] = s.split(':');
                    return [key, parseInt(val)];
                })
            );

            const defaultSize = Object.keys(sizeMap)[0];
            const key = `${name}__${defaultSize}`; // sides have no extras

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

            const priceDiv = document.createElement('div');
            priceDiv.classList.add('mb-1');

            const deleteBtn = document.createElement('button');
            deleteBtn.classList.add('btn', 'btn-sm', 'btn-danger');
            deleteBtn.textContent = 'Remove';

            function calculateItemPrice() {
                return sizeMap[sizeSelect.value] * parseInt(qtySpan.textContent);
            }

            function updateItemPrice(item) {
                priceDiv.textContent = `₱${calculateItemPrice()}`;
                item.setAttribute('data-price', calculateItemPrice());
            }

            function updateCartTotal() {
                let total = 0;
                cartItems.querySelectorAll('.list-group-item').forEach(item => {
                    total += parseInt(item.getAttribute('data-price'));
                });
                cartTotal.textContent = total;
            }

            // Event listeners
            sizeSelect.addEventListener('change', () => {
                li.setAttribute('data-key', `${name}__${sizeSelect.value}`);
                updateItemPrice(li);
                updateCartTotal();
            });

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

            li.append(title, sizeSelect, qtyDiv, priceDiv, deleteBtn);
            updateItemPrice(li);
            cartItems.appendChild(li);
            updateCartTotal();

            sideCart.classList.add('open');
        });
    });
});
