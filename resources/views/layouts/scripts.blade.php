<script>
    // Notifications toggle
    function toggleNotifications() {
        const notificationDropdown = document.getElementById('notificationDropdown');
        notificationDropdown.classList.toggle('hidden');
    }

    // Sidebar toggle for mobile
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');
        
        sidebar.classList.toggle('-translate-x-full');
        overlay.classList.toggle('hidden');
    }

    // User dropdown toggle
    function toggleDropdown() {
        const dropdown = document.getElementById('userDropdown');
        dropdown.classList.toggle('hidden');
    }

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(event) {
        const userDropdown = document.getElementById('userDropdown');
        const notificationDropdown = document.getElementById('notificationDropdown');
        
        // Close user dropdown
        if (!event.target.closest('#userDropdown') && !event.target.closest('button[onclick="toggleDropdown()"]')) {
            userDropdown.classList.add('hidden');
        }
        
        // Close notification dropdown
        if (!event.target.closest('#notificationDropdown') && !event.target.closest('button[onclick="toggleNotifications()"]')) {
            notificationDropdown.classList.add('hidden');
        }
    });

    // Auto refresh dashboard every 30 seconds
    setInterval(function() {
        console.log('Auto-refreshing dashboard data...');
        // Update sidebar parameter values
        updateSidebarValues();
        // You can implement AJAX calls here to update data without full page reload
    }, 30000);

    // Function to show parameter details (for sidebar sensor menu)
    function showParameterDetails(parameter) {
        let message = '';
        let title = '';
        
        switch(parameter) {
            case 'temperature':
                title = 'Suhu Air';
                message = 'Suhu air saat ini: ' + document.getElementById('tempSidebarValue').textContent + '\n\nRentang normal: 24-30°C\nStatus: Normal';
                break;
            case 'oxygen':
                title = 'Oksigen';
                message = 'Kadar oksigen saat ini: ' + document.getElementById('oxygenSidebarValue').textContent + '\n\nRentang normal: 6-10 ppm\nStatus: Baik';
                break;
            case 'ph':
                title = 'pH Air';
                message = 'pH air saat ini: ' + document.getElementById('phSidebarValue').textContent + '\n\nRentang normal: 6.5-8.5 pH\nStatus: Normal';
                break;
        }
        
        alert(title + '\n\n' + message);
    }

    // Function to update sidebar parameter values
    function updateSidebarValues() {
        // Simulate real-time updates for sidebar values
        const tempValue = (24 + Math.random() * 6).toFixed(1);
        const oxygenValue = (6 + Math.random() * 4).toFixed(1);
        const phValue = (6.0 + Math.random() * 2.5).toFixed(1);
        
        // Update sidebar display values
        const tempSidebar = document.getElementById('tempSidebarValue');
        const oxygenSidebar = document.getElementById('oxygenSidebarValue');
        const phSidebar = document.getElementById('phSidebarValue');
        
        if (tempSidebar) tempSidebar.textContent = tempValue + '°C';
        if (oxygenSidebar) oxygenSidebar.textContent = oxygenValue + ' ppm';
        if (phSidebar) phSidebar.textContent = phValue + ' pH';
    }

    // Function to confirm logout
    function confirmLogout(formId) {
        if (confirm('Apakah Anda yakin ingin keluar dari sistem?')) {
            document.getElementById(formId).submit();
        }
    }

    console.log('Admin Dashboard Layout loaded successfully');
</script>
