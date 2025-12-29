// Tab Management
function showTab(tabName) {
    // Hide all tab panels
    const tabPanels = document.querySelectorAll('.tab-panel');
    tabPanels.forEach(panel => {
        panel.classList.remove('active');
    });
    
    // Remove active class from all tab buttons
    const tabButtons = document.querySelectorAll('.tab-btn');
    tabButtons.forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Show selected tab panel
    const selectedPanel = document.getElementById(tabName);
    if (selectedPanel) {
        selectedPanel.classList.add('active');
    }
    
    // Add active class to clicked tab button
    const clickedButton = event.target.closest('.tab-btn');
    if (clickedButton) {
        clickedButton.classList.add('active');
    }
}

// Alert Management
function closeAlert() {
    const alert = document.getElementById('alert');
    if (alert) {
        alert.style.animation = 'slideUp 0.3s ease-out';
        setTimeout(() => {
            alert.remove();
        }, 300);
    }
}

// Auto close alert after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alert = document.getElementById('alert');
    if (alert) {
        setTimeout(() => {
            closeAlert();
        }, 5000);
    }
});

// Price Formatting
function formatRupiah(angka, prefix = '') {
    if (!angka) return prefix;
    
    // Remove non-numeric characters except dots
    let number_string = angka.replace(/[^,\d]/g, '').toString();
    let split = number_string.split(',');
    let sisa = split[0].length % 3;
    let rupiah = split[0].substr(0, sisa);
    let ribuan = split[0].substr(sisa).match(/\d{3}/gi);
    
    if (ribuan) {
        let separator = sisa ? '.' : '';
        rupiah += separator + ribuan.join('.');
    }
    
    rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
    return prefix + rupiah;
}

// Format price input
function formatHargaInput(input) {
    // Get cursor position
    let cursorPosition = input.selectionStart;
    
    // Get the value and format it
    let value = input.value.replace(/[^0-9]/g, '');
    let formattedValue = formatRupiah(value);
    
    // Set the formatted value
    input.value = formattedValue;
    
    // Restore cursor position (approximately)
    setTimeout(() => {
        input.setSelectionRange(cursorPosition, cursorPosition);
    }, 10);
}

// Add event listeners when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Format price input
    const hargaInput = document.getElementById('harga');
    if (hargaInput) {
        hargaInput.addEventListener('input', function() {
            formatHargaInput(this);
        });
        
        // Format on paste
        hargaInput.addEventListener('paste', function() {
            setTimeout(() => {
                formatHargaInput(this);
            }, 10);
        });
    }
    
    // Initialize tab functionality
    const tabButtons = document.querySelectorAll('.tab-btn');
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const tabName = this.getAttribute('onclick').match(/showTab\('(.+)'\)/)[1];
            showTab(tabName);
        });
    });
    
    // Form validation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                const formGroup = field.closest('.form-group');
                if (!field.value.trim()) {
                    isValid = false;
                    if (formGroup) {
                        formGroup.classList.add('error');
                        
                        // Remove existing error message
                        const existingError = formGroup.querySelector('.error-message');
                        if (existingError) {
                            existingError.remove();
                        }
                        
                        // Add error message
                        const errorMsg = document.createElement('div');
                        errorMsg.className = 'error-message';
                        errorMsg.textContent = 'Field ini harus diisi';
                        formGroup.appendChild(errorMsg);
                    }
                } else {
                    if (formGroup) {
                        formGroup.classList.remove('error');
                        const errorMsg = formGroup.querySelector('.error-message');
                        if (errorMsg) {
                            errorMsg.remove();
                        }
                    }
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                return false;
            }
        });
    });
    
    // Remove error styling when user starts typing
    const inputs = document.querySelectorAll('input, select');
    inputs.forEach(input => {
        input.addEventListener('input', function() {
            const formGroup = this.closest('.form-group');
            if (formGroup && formGroup.classList.contains('error')) {
                formGroup.classList.remove('error');
                const errorMsg = formGroup.querySelector('.error-message');
                if (errorMsg) {
                    errorMsg.remove();
                }
            }
        });
    });
});

// Confirm delete with enhanced styling
function confirmDelete(message = 'Yakin ingin menghapus data ini?') {
    return confirm(message);
}

// Loading state management
function showLoading(button) {
    const originalText = button.innerHTML;
    button.innerHTML = '<span class="loading"></span> Memproses...';
    button.disabled = true;
    
    return function hideLoading() {
        button.innerHTML = originalText;
        button.disabled = false;
    };
}

// Smooth scroll to element
function scrollToElement(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        element.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });
    }
}

// Copy to clipboard functionality
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        // Show temporary success message
        const tempAlert = document.createElement('div');
        tempAlert.className = 'alert alert-success';
        tempAlert.innerHTML = '<i class="fas fa-check-circle"></i> Berhasil disalin ke clipboard!';
        tempAlert.style.position = 'fixed';
        tempAlert.style.top = '20px';
        tempAlert.style.right = '20px';
        tempAlert.style.zIndex = '9999';
        tempAlert.style.maxWidth = '300px';
        
        document.body.appendChild(tempAlert);
        
        setTimeout(() => {
            tempAlert.remove();
        }, 3000);
    }).catch(function() {
        alert('Gagal menyalin ke clipboard');
    });
}

// Search functionality for tables
function searchTable(searchInput, tableId) {
    const filter = searchInput.value.toLowerCase();
    const table = document.getElementById(tableId);
    const rows = table.getElementsByTagName('tr');
    
    for (let i = 1; i < rows.length; i++) { // Start from 1 to skip header
        const cells = rows[i].getElementsByTagName('td');
        let found = false;
        
        for (let j = 0; j < cells.length; j++) {
            if (cells[j].textContent.toLowerCase().includes(filter)) {
                found = true;
                break;
            }
        }
        
        rows[i].style.display = found ? '' : 'none';
    }
}

// Export table to CSV
function exportTableToCSV(tableId, filename = 'data.csv') {
    const table = document.getElementById(tableId);
    const rows = table.querySelectorAll('tr');
    const csvContent = [];
    
    rows.forEach(row => {
        const cols = row.querySelectorAll('td, th');
        const rowData = [];
        cols.forEach(col => {
            // Clean the text content
            let text = col.textContent.trim();
            // Escape quotes and wrap in quotes if contains comma
            if (text.includes(',') || text.includes('"')) {
                text = '"' + text.replace(/"/g, '""') + '"';
            }
            rowData.push(text);
        });
        csvContent.push(rowData.join(','));
    });
    
    // Create and download the file
    const blob = new Blob([csvContent.join('\n')], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    a.click();
    window.URL.revokeObjectURL(url);
}

// Print functionality
function printTable(tableId) {
    const table = document.getElementById(tableId);
    const printWindow = window.open('', '', 'height=600,width=800');
    
    printWindow.document.write('<html><head><title>Cetak Data</title>');
    printWindow.document.write('<style>');
    printWindow.document.write('table { width: 100%; border-collapse: collapse; }');
    printWindow.document.write('th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }');
    printWindow.document.write('th { background-color: #f2f2f2; font-weight: bold; }');
    printWindow.document.write('</style></head><body>');
    printWindow.document.write('<h2>Laporan Data Inventaris</h2>');
    printWindow.document.write(table.outerHTML);
    printWindow.document.write('</body></html>');
    
    printWindow.document.close();
    printWindow.focus();
    printWindow.print();
    printWindow.close();
}

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl + Enter to submit form
    if (e.ctrlKey && e.key === 'Enter') {
        const activeForm = document.querySelector('form:focus-within');
        if (activeForm) {
            activeForm.submit();
        }
    }
    
    // Escape to close alerts
    if (e.key === 'Escape') {
        closeAlert();
    }
});

// Auto-save functionality (for future enhancement)
function autoSave(formId) {
    const form = document.getElementById(formId);
    if (!form) return;
    
    const inputs = form.querySelectorAll('input, select, textarea');
    inputs.forEach(input => {
        input.addEventListener('input', function() {
            const formData = new FormData(form);
            localStorage.setItem('auto_save_' + formId, JSON.stringify(Object.fromEntries(formData)));
        });
    });
}

// Restore auto-saved data
function restoreAutoSave(formId) {
    const savedData = localStorage.getItem('auto_save_' + formId);
    if (!savedData) return;
    
    try {
        const data = JSON.parse(savedData);
        const form = document.getElementById(formId);
        
        Object.keys(data).forEach(key => {
            const input = form.querySelector(`[name="${key}"]`);
            if (input) {
                input.value = data[key];
            }
        });
    } catch (e) {
        console.error('Error restoring auto-save data:', e);
    }
}