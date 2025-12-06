// admin-tab.js
document.addEventListener('DOMContentLoaded', function () {
    const tabs = document.querySelectorAll('.dashboard-tab');
    const sections = document.querySelectorAll('.dashboard-section');

    // Set the first tab as active by default
    if (tabs.length > 0 && sections.length > 0) {
        tabs[0].classList.add('active-tab');
        sections[0].classList.add('active-section');
    }

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            // Remove active class from all tabs and sections
            tabs.forEach(t => t.classList.remove('active-tab'));
            sections.forEach(s => s.classList.remove('active-section'));

            // Add active class to the clicked tab and its corresponding section
            tab.classList.add('active-tab');
            const targetId = tab.dataset.target;
            const targetSection = document.getElementById(targetId);
            if (targetSection) {
                targetSection.classList.add('active-section');
            }
        });
    });
});
