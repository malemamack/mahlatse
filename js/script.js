let menuToggle = document.querySelector('.menuToggle');
let header = document.querySelector('header');
let sectio

let menuToggle = document.querySelector('.menuToggle');
let header = document.querySelector('header');
let section = document.querySelector('section');

// Add event listeners to filter buttons
window.addEventListener('load', () => {
    const filterButtons = document.querySelectorAll('.filter-btn');
    const gridItems = document.querySelectorAll('.grid-list li');

    filterButtons.forEach(button => {
        button.addEventListener('click', () => {
            const filter = button.getAttribute('data-filter-btn');
            gridItems.forEach(item => {
                if (filter === 'all' || item.getAttribute('data-filter') === filter) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });

    // Simulate button clicks and verify results
    const testResults = [];

    function simulateClick(button) {
        button.click();
        const filter = button.getAttribute('data-filter-btn');
        gridItems.forEach(item => {
            const shouldBeVisible = filter === 'all' || item.getAttribute('data-filter') === filter;
            const isVisible = item.style.display !== 'none';
            testResults.push({
                filter,
                item: item.querySelector('.card-title').innerText.trim(),
                shouldBeVisible,
                isVisible,
                passed: shouldBeVisible === isVisible
            });
        });
    }

    filterButtons.forEach(button => simulateClick(button));

    console.log('Test Results:', testResults);
});