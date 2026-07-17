/**
 * Documentation Search
 */
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.querySelector('.docs-search input');
    
    if (!searchInput) return;
    
    // Simple client-side search
    searchInput.addEventListener('input', function(e) {
        const query = e.target.value.toLowerCase();
        const contentSections = document.querySelectorAll('.docs-content h2, .docs-content h3, .docs-content p, .docs-content li');
        
        if (query.length < 2) {
            // Reset highlighting
            contentSections.forEach(el => {
                el.style.display = '';
                el.innerHTML = el.textContent;
            });
            return;
        }
        
        contentSections.forEach(el => {
            const text = el.textContent.toLowerCase();
            if (text.includes(query)) {
                el.style.display = '';
                // Highlight match
                const regex = new RegExp(`(${query})`, 'gi');
                el.innerHTML = el.textContent.replace(regex, '<mark>$1</mark>');
            } else {
                el.style.display = 'none';
            }
        });
    });
});
