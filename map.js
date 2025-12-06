document.addEventListener('DOMContentLoaded', () => {
    // Elements
    const branchSelect = document.getElementById('branch-select');
    const branchForm = document.getElementById('branch-form');
    const mapDiv = document.getElementById('map');
    const etaDiv = document.getElementById('eta');
    const addressDiv = document.getElementById('address');

    // Example branch data
    const branches = {
        'Branch A': {
            address: '123 Main St, Manila',
            eta: '30-40 mins',
            mapPlaceholder: 'Map for Branch A'
        },
        'Branch B': {
            address: '456 Rizal Ave, Quezon City',
            eta: '25-35 mins',
            mapPlaceholder: 'Map for Branch B'
        },
        'Branch C': {
            address: '789 Bonifacio Blvd, Taguig',
            eta: '40-50 mins',
            mapPlaceholder: 'Map for Branch C'
        }
    };

    // Hide map/ETA initially
    mapDiv.textContent = 'Select a branch to see the map';
    etaDiv.textContent = '';
    addressDiv.textContent = '';

    // When user selects a branch
    branchSelect.addEventListener('change', () => {
        const selectedBranch = branchSelect.value;
        if (branches[selectedBranch]) {
            const branchInfo = branches[selectedBranch];

            // Update map placeholder
            mapDiv.textContent = branchInfo.mapPlaceholder;

            // Show ETA and address
            etaDiv.textContent = `ETA: ${branchInfo.eta}`;
            addressDiv.textContent = `Address: ${branchInfo.address}`;
        } else {
            mapDiv.textContent = 'Select a branch to see the map';
            etaDiv.textContent = '';
            addressDiv.textContent = '';
        }
    });

    // Optional: handle form submit
    branchForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const selectedBranch = branchSelect.value;
        if (!branches[selectedBranch]) {
            alert('Please select a branch');
            