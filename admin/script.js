// Admin Dashboard JavaScript
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const openSidebarBtn = document.getElementById('openSidebar');
    const closeSidebarBtn = document.getElementById('closeSidebar');

    // Make sure elements exist before adding listeners
    if (!sidebar || !openSidebarBtn || !closeSidebarBtn) return;

    // Open sidebar
    openSidebarBtn.addEventListener('click', function(e) {
        e.stopPropagation(); // Prevent the document click listener from firing immediately
        sidebar.classList.remove('hidden');
    });

    // Close sidebar via the 'X' button
    closeSidebarBtn.addEventListener('click', function() {
        sidebar.classList.add('hidden');
    });

    // Close sidebar when clicking anywhere outside of it on mobile
    document.addEventListener('click', function(event) {
        if (window.innerWidth <= 768) {
            const isClickInsideSidebar = sidebar.contains(event.target);
            const isClickOnToggle = openSidebarBtn.contains(event.target);
            
            // If the click is outside the sidebar and not on the menu button, close it
            if (!isClickInsideSidebar && !isClickOnToggle && !sidebar.classList.contains('hidden')) {
                sidebar.classList.add('hidden');
            }
        }
    });

    // Responsive adjustments on resize and load
    function adjustLayout() {
        if (window.innerWidth > 768) {
            // Desktop: Always show sidebar
            sidebar.classList.remove('hidden'); 
        } else {
            // Mobile: Ensure sidebar starts hidden to prevent overlapping on load
            sidebar.classList.add('hidden'); 
        }
    }

    // Initial layout adjustment on page load
    adjustLayout();

    // Listen for window resize to fix layout if user rotates device or resizes browser
    window.addEventListener('resize', adjustLayout);

    // Add confirmation for deletion actions (if any)
    const deleteButtons = document.querySelectorAll('.delete-btn');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            const confirmed = confirm('Are you sure you want to delete this item?');
            if (!confirmed) {
                e.preventDefault();
            }
        });
    });
});