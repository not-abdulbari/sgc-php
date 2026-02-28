// Admin Dashboard JavaScript
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const openSidebarBtn = document.getElementById('openSidebar');
    const closeSidebarBtn = document.getElementById('closeSidebar');

    // Mobile menu toggle functionality
    function toggleSidebar() {
        sidebar.classList.toggle('hidden');
    }

    // Open sidebar
    openSidebarBtn.addEventListener('click', function() {
        sidebar.classList.remove('hidden');
    });

    // Close sidebar
    closeSidebarBtn.addEventListener('click', function() {
        sidebar.classList.add('hidden');
    });

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(event) {
        if (window.innerWidth <= 768) {
            const isClickInsideSidebar = sidebar.contains(event.target);
            const isClickOnToggle = openSidebarBtn.contains(event.target);
            
            if (!isClickInsideSidebar && !isClickOnToggle && !sidebar.classList.contains('hidden')) {
                sidebar.classList.add('hidden');
            }
        }
    });

    // Responsive adjustments
    function adjustLayout() {
        if (window.innerWidth > 768) {
            sidebar.classList.remove('hidden'); // Always show sidebar on desktop
        } else {
            // Show toggle button and hide close button when sidebar is open
            if (!sidebar.classList.contains('hidden')) {
                closeSidebarBtn.style.display = 'block';
            }
        }
    }

    // Initial layout adjustment
    adjustLayout();

    // Listen for window resize
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