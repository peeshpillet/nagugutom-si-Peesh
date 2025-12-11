// map.js - Angel's-style area selection (no external API)

document.addEventListener('DOMContentLoaded', () => {
    // ---------------------- BRANCH DEFINITIONS ----------------------
    // 5 branches you mentioned
    const BRANCHES = {
        'gen-trias': {
            id: 'gen-trias',
            name: 'Ramen Naijiro - General Trias'
        },
        'dasma': {
            id: 'dasma',
            name: 'Ramen Naijiro - Dasmariñas'
        },
        'odasiba': {
            id: 'odasiba',
            name: 'Ramen Naijiro - Odasiba'
        },
        'marikina': {
            id: 'marikina',
            name: 'Ramen Naijiro - Marikina'
        },
        'cainta': {
            id: 'cainta',
            name: 'Ramen Naijiro - Cainta'
        }
    };

    // ---------------------- SERVICE AREAS ----------------------
    // Structure:
    // Province -> City -> Barangay/Subdivision -> { branchId: 'gen-trias', delivery: true/false }
    //
    // TODO: Replace the sample barangays with real areas you want to support.
    const SERVICE_AREAS = {
        'Cavite': {
            'General Trias': {
                'Manggahan':       { branchId: 'gen-trias', delivery: true },
                'Governor Hills':  { branchId: 'gen-trias', delivery: true },
                'San Francisco':   { branchId: 'gen-trias', delivery: true }
            },
            'Dasmariñas': {
                'Salitran':        { branchId: 'dasma', delivery: true },
                'Burol':           { branchId: 'dasma', delivery: true },
                'Paliparan':       { branchId: 'dasma', delivery: true }
            }
        },
        'Metro Manila': {
            'Marikina': {
                'Concepcion Uno':  { branchId: 'marikina', delivery: true },
                'Parang':          { branchId: 'marikina', delivery: true },
                'Barangka':        { branchId: 'marikina', delivery: true }
            }
        },
        'Rizal': {
            'Cainta': {
                'Greenpark Village': { branchId: 'cainta', delivery: true },
                'San Andres':        { branchId: 'cainta', delivery: true },
                'San Isidro':        { branchId: 'cainta', delivery: true }
            }
        },
        'Other': {
            'Odasiba City': {
                'South Triangle':      { branchId: 'odasiba', delivery: true },
                'Pinyahan':      { branchId: 'odasiba', delivery: true},
                'Laging Handa':      { branchId: 'odasiba', delivery: true},
                'Krus na Ligas':      { branchId: 'odasiba', delivery: true}
            }
        }
    };

    // ---------------------- ELEMENTS ----------------------
    const provinceSelect = document.getElementById('province-select');
    const citySelect = document.getElementById('city-select');
    const barangaySelect = document.getElementById('barangay-select');

    const deliveryRadio = document.getElementById('cov-delivery');
    const pickupRadio = document.getElementById('cov-pickup');

    const changeLocationBtn = document.getElementById('change-location-btn');
    const coverageResult = document.getElementById('coverage-result');

    const hiddenBranchId = document.getElementById('selected-branch-id');
    const hiddenBranchName = document.getElementById('selected-branch-name');
    const hiddenProvince = document.getElementById('selected-province');
    const hiddenCity = document.getElementById('selected-city');
    const hiddenBarangay = document.getElementById('selected-barangay');
    const hiddenDeliveryAllowed = document.getElementById('delivery-allowed');

    // ---------------------- HELPERS ----------------------

    function clearSelect(selectEl, placeholderText) {
        selectEl.innerHTML = '';
        const opt = document.createElement('option');
        opt.value = '';
        opt.disabled = true;
        opt.selected = true;
        opt.textContent = placeholderText;
        selectEl.appendChild(opt);
    }

    function populateProvinces() {
        clearSelect(provinceSelect, 'Province');

        Object.keys(SERVICE_AREAS).forEach(province => {
            const opt = document.createElement('option');
            opt.value = province;
            opt.textContent = province;
            provinceSelect.appendChild(opt);
        });

        // reset lower levels
        clearSelect(citySelect, 'City / Municipality');
        citySelect.disabled = true;
        clearSelect(barangaySelect, 'Barangay / Subdivision');
        barangaySelect.disabled = true;
    }

    function populateCities(province) {
        clearSelect(citySelect, 'City / Municipality');
        citySelect.disabled = false;

        const cities = SERVICE_AREAS[province] || {};
        Object.keys(cities).forEach(city => {
            const opt = document.createElement('option');
            opt.value = city;
            opt.textContent = city;
            citySelect.appendChild(opt);
        });

        clearSelect(barangaySelect, 'Barangay / Subdivision');
        barangaySelect.disabled = true;
    }

    function populateBarangays(province, city) {
        clearSelect(barangaySelect, 'Barangay / Subdivision');
        barangaySelect.disabled = false;

        const barangays = (SERVICE_AREAS[province] && SERVICE_AREAS[province][city]) || {};
        Object.keys(barangays).forEach(brgy => {
            const opt = document.createElement('option');
            opt.value = brgy;
            opt.textContent = brgy;
            barangaySelect.appendChild(opt);
        });
    }

    function updateCoverageMessage() {
        const province = provinceSelect.value;
        const city = citySelect.value;
        const barangay = barangaySelect.value;

        if (!province || !city || !barangay) {
            coverageResult.innerHTML = '';
            hiddenBranchId.value = '';
            hiddenBranchName.value = '';
            hiddenProvince.value = '';
            hiddenCity.value = '';
            hiddenBarangay.value = '';
            hiddenDeliveryAllowed.value = '0';
            return;
        }

        const areaInfo =
            SERVICE_AREAS[province] &&
            SERVICE_AREAS[province][city] &&
            SERVICE_AREAS[province][city][barangay];

        if (!areaInfo) {
            coverageResult.innerHTML =
                '<span class="text-danger">This area is not in our coverage list yet. Please try Pick-up or contact the branch.</span>';
            hiddenDeliveryAllowed.value = '0';
            return;
        }

        const branch = BRANCHES[areaInfo.branchId];
        if (!branch) {
            coverageResult.innerHTML =
                '<span class="text-danger">Internal configuration error: branch not found.</span>';
            hiddenDeliveryAllowed.value = '0';
            return;
        }

        // save selection
        hiddenBranchId.value = branch.id;
        hiddenBranchName.value = branch.name;
        hiddenProvince.value = province;
        hiddenCity.value = city;
        hiddenBarangay.value = barangay;

        const isDelivery = deliveryRadio && deliveryRadio.checked;
        const canDeliver = !!areaInfo.delivery;

        if (isDelivery) {
            if (canDeliver) {
                hiddenDeliveryAllowed.value = '1';
                coverageResult.innerHTML = `
                    <span class="text-success fw-semibold">Yes, we deliver!</span>
                    <span> We can deliver to <strong>${barangay}, ${city}, ${province}</strong>
                    from <strong>${branch.name}</strong>.</span>
                `;
            } else {
                hiddenDeliveryAllowed.value = '0';
                coverageResult.innerHTML = `
                    <span class="text-danger fw-semibold">Sorry!</span>
                    <span> We do not deliver to <strong>${barangay}, ${city}, ${province}</strong>,
                    but you can still place a <strong>Pick-up</strong> order from
                    <strong>${branch.name}</strong>.</span>
                `;
            }
        } else {
            // Pick-up always allowed
            hiddenDeliveryAllowed.value = '0'; // not relevant for pickup
            coverageResult.innerHTML = `
                <span class="text-success fw-semibold">Pick-up available.</span>
                <span> Your selected area is served by <strong>${branch.name}</strong>.
                You can choose this branch for your pick-up order at checkout.</span>
            `;
        }
    }

    function resetLocation() {
        provinceSelect.value = '';
        citySelect.value = '';
        barangaySelect.value = '';

        citySelect.disabled = true;
        barangaySelect.disabled = true;

        clearSelect(citySelect, 'City / Municipality');
        clearSelect(barangaySelect, 'Barangay / Subdivision');

        coverageResult.innerHTML = '';
        hiddenBranchId.value = '';
        hiddenBranchName.value = '';
        hiddenProvince.value = '';
        hiddenCity.value = '';
        hiddenBarangay.value = '';
        hiddenDeliveryAllowed.value = '0';

        // default back to Delivery checked
        if (deliveryRadio) deliveryRadio.checked = true;
        if (pickupRadio) pickupRadio.checked = false;
    }

    // ---------------------- EVENT BINDINGS ----------------------

    if (provinceSelect) {
        provinceSelect.addEventListener('change', () => {
            const province = provinceSelect.value;
            if (!province) return;
            populateCities(province);
            coverageResult.innerHTML = '';
        });
    }

    if (citySelect) {
        citySelect.addEventListener('change', () => {
            const province = provinceSelect.value;
            const city = citySelect.value;
            if (!province || !city) return;
            populateBarangays(province, city);
            coverageResult.innerHTML = '';
        });
    }

    if (barangaySelect) {
        barangaySelect.addEventListener('change', () => {
            updateCoverageMessage();
        });
    }

    if (deliveryRadio) {
        deliveryRadio.addEventListener('change', () => {
            updateCoverageMessage();
        });
    }

    if (pickupRadio) {
        pickupRadio.addEventListener('change', () => {
            updateCoverageMessage();
        });
    }

    if (changeLocationBtn) {
        changeLocationBtn.addEventListener('click', () => {
            resetLocation();
        });
    }

    // ---------------------- INIT ----------------------
    populateProvinces();
});
