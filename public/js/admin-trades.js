// Admin Trades Page JavaScript

$(document).ready(function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Copy to clipboard functionality
    $('.copy-btn').click(function(e) {
        e.preventDefault();
        const text = $(this).data('copy');
        const btn = $(this);
        
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(function() {
                showCopyFeedback(btn, 'Copied!');
            }).catch(function() {
                fallbackCopyTextToClipboard(text, btn);
            });
        } else {
            fallbackCopyTextToClipboard(text, btn);
        }
    });

    // Fallback copy function
    function fallbackCopyTextToClipboard(text, btn) {
        const textArea = document.createElement("textarea");
        textArea.value = text;
        textArea.style.top = "0";
        textArea.style.left = "0";
        textArea.style.position = "fixed";
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        
        try {
            document.execCommand('copy');
            showCopyFeedback(btn, 'Copied!');
        } catch (err) {
            showCopyFeedback(btn, 'Failed');
        }
        
        document.body.removeChild(textArea);
    }

    // Show copy feedback
    function showCopyFeedback(btn, message) {
        const originalText = btn.html();
        btn.html('<i class="fas fa-check"></i> ' + message);
        btn.addClass('text-success');
        
        setTimeout(function() {
            btn.html(originalText);
            btn.removeClass('text-success');
        }, 2000);
    }

    // Auto-refresh functionality
    let autoRefreshInterval;
    $('#autoRefresh').change(function() {
        if ($(this).is(':checked')) {
            autoRefreshInterval = setInterval(function() {
                location.reload();
            }, 30000); // Refresh every 30 seconds
            showNotification('Auto-refresh enabled (30s)', 'info');
        } else {
            clearInterval(autoRefreshInterval);
            showNotification('Auto-refresh disabled', 'info');
        }
    });

    // Filter functionality
    $('#searchInput, #statusFilter, #typeFilter, #dateFromFilter, #dateToFilter').on('input change', function() {
        filterTransactions();
    });

    function filterTransactions() {
        const searchTerm = $('#searchInput').val().toLowerCase();
        const statusFilter = $('#statusFilter').val();
        const typeFilter = $('#typeFilter').val();
        const dateFrom = $('#dateFromFilter').val();
        const dateTo = $('#dateToFilter').val();

        $('.table tbody tr').each(function() {
            const row = $(this);
            const text = row.text().toLowerCase();
            const status = row.find('.badge').text().toLowerCase();
            const date = row.find('td:last').text(); // Assuming date is in last column
            
            let show = true;

            // Text search
            if (searchTerm && !text.includes(searchTerm)) {
                show = false;
            }

            // Status filter
            if (statusFilter && !status.includes(statusFilter.toLowerCase())) {
                show = false;
            }

            // Date range filter
            if (dateFrom || dateTo) {
                const rowDate = new Date(date);
                if (dateFrom && rowDate < new Date(dateFrom)) {
                    show = false;
                }
                if (dateTo && rowDate > new Date(dateTo)) {
                    show = false;
                }
            }

            row.toggle(show);
        });

        updateResultsCount();
    }

    function updateResultsCount() {
        const visibleRows = $('.table tbody tr:visible').length;
        const totalRows = $('.table tbody tr').length;
        $('#resultsCount').text(`Showing ${visibleRows} of ${totalRows} transactions`);
    }

    // Clear filters
    $('#clearFilters').click(function() {
        $('#searchInput, #statusFilter, #typeFilter, #dateFromFilter, #dateToFilter').val('');
        $('.table tbody tr').show();
        updateResultsCount();
        showNotification('Filters cleared', 'info');
    });

    // Status update functionality
    $('.status-form').submit(function(e) {
        e.preventDefault();
        const form = $(this);
        const formData = form.serialize();
        const actionUrl = form.attr('action');
        const submitBtn = form.find('button[type="submit"]');
        const originalBtnText = submitBtn.html();

        // Show loading state
        submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Updating...');
        submitBtn.prop('disabled', true);

        $.ajax({
            url: actionUrl,
            method: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                showNotification('Status updated successfully', 'success');
                // Update the status badge in the table
                const newStatus = form.find('select').val();
                const row = form.closest('tr');
                const statusBadge = row.find('.badge');
                updateStatusBadge(statusBadge, newStatus);
                
                // Close modal if open
                const modal = form.closest('.modal');
                if (modal.length) {
                    modal.modal('hide');
                }
            },
            error: function(xhr) {
                let errorMessage = 'Failed to update status';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                showNotification(errorMessage, 'error');
            },
            complete: function() {
                submitBtn.html(originalBtnText);
                submitBtn.prop('disabled', false);
            }
        });
    });

    // Update status badge appearance
    function updateStatusBadge(badge, status) {
        badge.removeClass('bg-warning bg-success bg-danger bg-info bg-secondary');
        badge.text(status.charAt(0).toUpperCase() + status.slice(1));
        
        switch(status.toLowerCase()) {
            case 'pending':
                badge.addClass('bg-warning');
                break;
            case 'successful':
            case 'approved':
                badge.addClass('bg-success');
                break;
            case 'canceled':
            case 'rejected':
                badge.addClass('bg-danger');
                break;
            default:
                badge.addClass('bg-secondary');
        }
    }

    // Export functionality
    $('#exportBtn').click(function() {
        const activeTab = $('.nav-tabs .nav-link.active').attr('data-bs-target');
        const tabName = activeTab.replace('#', '');
        const currentDate = new Date().toISOString().split('T')[0];
        
        // Get visible table data
        const data = [];
        $(`${activeTab} .table tbody tr:visible`).each(function() {
            const rowData = [];
            $(this).find('td').each(function() {
                rowData.push($(this).text().trim());
            });
            data.push(rowData);
        });

        // Get table headers
        const headers = [];
        $(`${activeTab} .table thead th`).each(function() {
            headers.push($(this).text().trim());
        });

        // Create CSV content
        let csvContent = headers.join(',') + '\n';
        data.forEach(row => {
            csvContent += row.map(cell => `"${cell.replace(/"/g, '""')}"`).join(',') + '\n';
        });

        // Download CSV
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', `${tabName}_transactions_${currentDate}.csv`);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        showNotification('Export completed', 'success');
    });

    // Print functionality
    $('#printBtn').click(function() {
        const activeTab = $('.nav-tabs .nav-link.active').attr('data-bs-target');
        const tabContent = $(activeTab).html();
        const printWindow = window.open('', '_blank');
        
        printWindow.document.write(`
            <html>
                <head>
                    <title>Transaction Report</title>
                    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
                    <style>
                        @media print {
                            .btn, .modal, .tab-content > .tab-pane:not(.active) { display: none !important; }
                            .table { font-size: 12px; }
                        }
                    </style>
                </head>
                <body>
                    <div class="container-fluid">
                        <h2>Transaction Report - ${new Date().toLocaleDateString()}</h2>
                        ${tabContent}
                    </div>
                </body>
            </html>
        `);
        
        printWindow.document.close();
        printWindow.print();
    });

    // Notification system
    function showNotification(message, type = 'info') {
        const alertClass = type === 'success' ? 'alert-success' : type === 'error' ? 'alert-danger' : 'alert-info';
        const iconClass = type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle';
        
        const notification = $(`
            <div class="alert ${alertClass} alert-dismissible fade show position-fixed" 
                 style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
                <i class="fas ${iconClass} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `);

        $('body').append(notification);

        // Auto-remove after 5 seconds
        setTimeout(function() {
            notification.alert('close');
        }, 5000);
    }

    // Tab change handler
    $('.nav-tabs a').on('shown.bs.tab', function() {
        updateResultsCount();
        // Clear filters when switching tabs
        $('#searchInput, #statusFilter, #typeFilter').val('');
    });

    // Initialize results count
    updateResultsCount();

    // Keyboard shortcuts
    $(document).keydown(function(e) {
        // Ctrl+F for search
        if (e.ctrlKey && e.keyCode === 70) {
            e.preventDefault();
            $('#searchInput').focus();
        }
        
        // Ctrl+R for refresh (prevent default browser refresh)
        if (e.ctrlKey && e.keyCode === 82) {
            e.preventDefault();
            location.reload();
        }
        
        // Escape to clear search
        if (e.keyCode === 27) {
            $('#searchInput').val('').trigger('input');
        }
    });

    // Real-time status updates via polling (optional)
    if (typeof enableRealTimeUpdates !== 'undefined' && enableRealTimeUpdates) {
        setInterval(function() {
            checkForUpdates();
        }, 60000); // Check every minute
    }

    function checkForUpdates() {
        // This would typically make an AJAX call to check for new transactions
        // Implementation depends on your backend API
        console.log('Checking for updates...');
    }

    // Enhanced modal functionality
    $('.modal').on('show.bs.modal', function() {
        const modal = $(this);
        modal.find('.modal-body').addClass('fade-in');
    });

    // Enhanced table interactions
    $('.table tbody tr').hover(
        function() { $(this).addClass('table-hover-effect'); },
        function() { $(this).removeClass('table-hover-effect'); }
    );
});

// Utility functions
function formatCurrency(amount, currency = 'NGN') {
    return new Intl.NumberFormat('en-NG', {
        style: 'currency',
        currency: currency
    }).format(amount);
}

function formatDate(date) {
    return new Date(date).toLocaleDateString('en-NG', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function truncateText(text, length = 20) {
    return text.length > length ? text.substring(0, length) + '...' : text;
}