document.addEventListener('DOMContentLoaded', function () {
    const tabs = document.querySelectorAll('.dashboard-tab');
    const sections = document.querySelectorAll('.dashboard-section');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            // Remove active class from all tabs and sections
            tabs.forEach(t => t.classList.remove('active-tab'));
            sections.forEach(s => s.classList.remove('active-section'));

            // Add active class to the clicked tab and its corresponding section
            tab.classList.add('active-tab');
            document.getElementById(tab.dataset.target).classList.add('active-section');
        });
    });
});
