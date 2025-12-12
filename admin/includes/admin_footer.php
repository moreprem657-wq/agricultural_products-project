</div> <!-- End of main-content -->
        </div> <!-- End of page-content-wrapper -->
    </div> <!-- End of wrapper -->

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Custom Admin JS -->
    <script src="../../assets/js/admin.js"></script>
    
    <script>
    // Toggle sidebar
    document.addEventListener('DOMContentLoaded', function() {
        // Menu toggle
        const menuToggle = document.getElementById('menu-toggle');
        const wrapper = document.getElementById('wrapper');
        
        menuToggle.addEventListener('click', function(e) {
            e.preventDefault();
            wrapper.classList.toggle('toggled');
            
            // Store state in localStorage
            const isToggled = wrapper.classList.contains('toggled');
            localStorage.setItem('sidebarToggled', isToggled);
        });
        
        // Check localStorage for saved state
        const sidebarToggled = localStorage.getItem('sidebarToggled') === 'true';
        if (sidebarToggled) {
            wrapper.classList.add('toggled');
        }
        
        // Auto-dismiss alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
        
        // Initialize DataTables
        $('.datatable').DataTable({
            responsive: true,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search...",
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                infoEmpty: "Showing 0 to 0 of 0 entries",
                infoFiltered: "(filtered from _MAX_ total entries)",
                paginate: {
                    first: "First",
                    last: "Last",
                    next: "Next",
                    previous: "Previous"
                }
            },
            dom: '<"top"<"row"<"col-md-6"l><"col-md-6"f>>>rt<"bottom"<"row"<"col-md-6"i><"col-md-6"p>>>'
        });
        
        // Initialize tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl, {
                trigger: 'hover'
            });
        });
        
        // Initialize popovers
        const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        popoverTriggerList.map(function (popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl);
        });
        
        // Image preview for file inputs
        function previewImage(input, previewId) {
            const preview = document.getElementById(previewId);
            const file = input.files[0];
            const reader = new FileReader();
            
            preview.style.display = 'block';
            
            reader.onloadend = function() {
                preview.src = reader.result;
            }
            
            if (file) {
                reader.readAsDataURL(file);
            } else {
                preview.src = '';
            }
        }
        
        // Initialize all image previews
        document.querySelectorAll('.image-preview-input').forEach(input => {
            const previewId = input.dataset.preview;
            input.addEventListener('change', function() {
                previewImage(this, previewId);
            });
        });
    });
    
    // Confirm before delete
    function confirmDelete(itemName) {
        return confirm(`Are you sure you want to delete this ${itemName}? This action cannot be undone.`);
    }
    
    // Initialize charts
    function initCharts() {
        // Sales chart
        const salesCtx = document.getElementById('salesChart');
        if (salesCtx) {
            new Chart(salesCtx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{
                        label: 'Monthly Sales',
                        data: [12, 19, 3, 5, 2, 3],
                        backgroundColor: 'rgba(40, 167, 69, 0.2)',
                        borderColor: 'rgba(40, 167, 69, 1)',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }
        
        // Product categories chart
        const categoriesCtx = document.getElementById('categoriesChart');
        if (categoriesCtx) {
            new Chart(categoriesCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Fertilizers', 'Pesticides', 'Seeds', 'Equipment'],
                    datasets: [{
                        data: [30, 25, 20, 25],
                        backgroundColor: [
                            '#28a745',
                            '#17a2b8',
                            '#ffc107',
                            '#dc3545'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'right',
                        }
                    }
                }
            });
        }
    }
    
    // Initialize charts when DOM is loaded
    document.addEventListener('DOMContentLoaded', initCharts);
    </script>
</body>
</html>