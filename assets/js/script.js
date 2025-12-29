// Sidebar Management
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    
    sidebar.classList.toggle('collapsed');
    mainContent.classList.toggle('expanded');
}

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
    
    // Initialize sidebar state
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    
    // Check screen size and adjust sidebar
    if (window.innerWidth <= 768) {
        sidebar.classList.add('collapsed');
        mainContent.classList.add('expanded');
    }
});

// Price Formatting
function formatRupiah(angka, prefix = '') {
    if (!angka) return prefix;
    
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
    let cursorPosition = input.selectionStart;
    let value = input.value.replace(/[^0-9]/g, '');
    let formattedValue = formatRupiah(value);
    
    input.value = formattedValue;
    
    setTimeout(() => {
        input.setSelectionRange(cursorPosition, cursorPosition);
    }, 10);
}

// Initialize event listeners
document.addEventListener('DOMContentLoaded', function() {
    // Format price input
    const hargaInput = document.getElementById('harga');
    if (hargaInput) {
        hargaInput.addEventListener('input', function() {
            formatHargaInput(this);
        });
        
        hargaInput.addEventListener('paste', function() {
            setTimeout(() => {
                formatHargaInput(this);
            }, 10);
        });
    }
    
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
                        
                        const existingError = formGroup.querySelector('.error-message');
                        if (existingError) {
                            existingError.remove();
                        }
                        
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
    
    // Mobile sidebar toggle
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            if (window.innerWidth <= 768) {
                sidebar.classList.toggle('show');
            } else {
                toggleSidebar();
            }
        });
    }
    
    // Close mobile sidebar when clicking outside
    document.addEventListener('click', function(e) {
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.querySelector('.sidebar-toggle');
        
        if (window.innerWidth <= 768 && 
            sidebar.classList.contains('show') && 
            !sidebar.contains(e.target) && 
            !sidebarToggle.contains(e.target)) {
            sidebar.classList.remove('show');
        }
    });
});

// Search functionality for tables
function searchTable() {
    const input = document.getElementById('searchInput');
    const filter = input.value.toLowerCase();
    const table = document.getElementById('stockTable');
    const rows = table.getElementsByTagName('tr');
    
    for (let i = 1; i < rows.length; i++) {
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

// Filter by stock status
function filterStock(type) {
    const rows = document.querySelectorAll('.stock-row');
    const buttons = document.querySelectorAll('.filter-btn');
    
    // Update active button
    buttons.forEach(btn => btn.classList.remove('active'));
    event.target.classList.add('active');
    
    rows.forEach(row => {
        const status = row.getAttribute('data-status');
        if (type === 'all' || status === type) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// View item history
function viewHistory(itemName) {
    const modal = document.getElementById('historyModal');
    if (modal) {
        modal.style.display = 'block';
        document.getElementById('historyContent').innerHTML = 
            '<div class="loading"><i class="fas fa-spinner fa-spin"></i> Memuat...</div>';
        
        setTimeout(() => {
            document.getElementById('historyContent').innerHTML = 
                '<p>Riwayat untuk: <strong>' + itemName + '</strong></p>' +
                '<p>Fitur ini akan menampilkan riwayat lengkap transaksi barang ini.</p>';
        }, 1000);
    }
}

// Close modal
function closeModal() {
    const modal = document.getElementById('historyModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

// Modal management
window.onclick = function(event) {
    const modal = document.getElementById('historyModal');
    if (modal && event.target == modal) {
        modal.style.display = 'none';
    }
}

// Export and Print functions - deprecated, use page-specific functions
// function exportData() {
//     alert('Fitur export akan segera tersedia');
// }

function printData() {
    window.print();
}

// Confirm delete
function confirmDelete(message = 'Yakin ingin menghapus data ini?') {
    return confirm(message);
}

// Loading state management
function showLoading(button) {
    const originalText = button.innerHTML;
    button.innerHTML = '<span class="loading"><i class="fas fa-spinner fa-spin"></i></span> Memproses...';
    button.disabled = true;
    
    return function hideLoading() {
        button.innerHTML = originalText;
        button.disabled = false;
    };
}

// Copy to clipboard
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
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

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl + Enter to submit form
    if (e.ctrlKey && e.key === 'Enter') {
        const activeForm = document.querySelector('form:focus-within');
        if (activeForm) {
            activeForm.submit();
        }
    }
    
    // Escape to close alerts and modals
    if (e.key === 'Escape') {
        closeAlert();
        closeModal();
    }
    
    // Toggle sidebar with Ctrl + B
    if (e.ctrlKey && e.key === 'b') {
        e.preventDefault();
        toggleSidebar();
    }
});

// Responsive handling
window.addEventListener('resize', function() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    
    if (window.innerWidth <= 768) {
        sidebar.classList.remove('show');
        if (!sidebar.classList.contains('collapsed')) {
            sidebar.classList.add('collapsed');
            mainContent.classList.add('expanded');
        }
    } else {
        sidebar.classList.remove('show');
    }
});

// Auto-save functionality
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