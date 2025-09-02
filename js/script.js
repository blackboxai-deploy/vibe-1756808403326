// Main JavaScript file for MediCare Store

// DOM ready function
document.addEventListener('DOMContentLoaded', function() {
    initializeComponents();
});

// Initialize all components
function initializeComponents() {
    initializeSearch();
    initializeCart();
    initializeForms();
    initializeAlerts();
}

// Search functionality
function initializeSearch() {
    const searchForm = document.querySelector('.search-form');
    const searchInput = document.querySelector('input[name="search"]');
    
    if (searchForm && searchInput) {
        // Auto-submit search on Enter
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchForm.submit();
            }
        });
        
        // Clear search button
        if (searchInput.value) {
            addClearButton(searchInput);
        }
    }
}

// Add clear button to search input
function addClearButton(input) {
    if (input.parentNode.querySelector('.clear-search')) return;
    
    const clearBtn = document.createElement('button');
    clearBtn.type = 'button';
    clearBtn.className = 'clear-search';
    clearBtn.innerHTML = '×';
    clearBtn.style.cssText = `
        position: absolute;
        right: 5px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        font-size: 18px;
        cursor: pointer;
        color: #999;
        padding: 0;
        width: 20px;
        height: 20px;
    `;
    
    input.parentNode.style.position = 'relative';
    input.parentNode.appendChild(clearBtn);
    
    clearBtn.addEventListener('click', function() {
        input.value = '';
        input.focus();
        clearBtn.remove();
    });
}

// Cart functionality
function initializeCart() {
    // Add to cart forms
    const addToCartForms = document.querySelectorAll('form[action="add-to-cart.php"]');
    addToCartForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = 'Adding...';
                
                // Re-enable after 3 seconds to prevent double submission
                setTimeout(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = 'Add to Cart';
                }, 3000);
            }
        });
    });
    
    // Quantity controls
    const quantityInputs = document.querySelectorAll('.quantity-input');
    quantityInputs.forEach(input => {
        input.addEventListener('change', function() {
            if (parseInt(this.value) < 1) {
                this.value = 1;
            }
            if (parseInt(this.value) > parseInt(this.max)) {
                this.value = this.max;
            }
        });
    });
}

// Form enhancements
function initializeForms() {
    // Password strength indicator
    const passwordInputs = document.querySelectorAll('input[type="password"]');
    passwordInputs.forEach(input => {
        if (input.name === 'password') {
            addPasswordStrengthIndicator(input);
        }
    });
    
    // Form validation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(form)) {
                e.preventDefault();
            }
        });
    });
    
    // Phone number formatting
    const phoneInputs = document.querySelectorAll('input[type="tel"]');
    phoneInputs.forEach(input => {
        input.addEventListener('input', formatPhoneNumber);
    });
}

// Add password strength indicator
function addPasswordStrengthIndicator(input) {
    const indicator = document.createElement('div');
    indicator.className = 'password-strength';
    indicator.style.cssText = `
        height: 4px;
        margin-top: 5px;
        border-radius: 2px;
        transition: all 0.3s ease;
        background: #e9ecef;
    `;
    
    input.parentNode.insertBefore(indicator, input.nextSibling);
    
    input.addEventListener('input', function() {
        const strength = calculatePasswordStrength(this.value);
        updatePasswordIndicator(indicator, strength);
    });
}

// Calculate password strength
function calculatePasswordStrength(password) {
    let strength = 0;
    
    if (password.length >= 8) strength += 25;
    if (/[a-z]/.test(password)) strength += 25;
    if (/[A-Z]/.test(password)) strength += 25;
    if (/[0-9]/.test(password)) strength += 15;
    if (/[^A-Za-z0-9]/.test(password)) strength += 10;
    
    return Math.min(strength, 100);
}

// Update password strength indicator
function updatePasswordIndicator(indicator, strength) {
    let color, text;
    
    if (strength < 25) {
        color = '#dc3545';
        text = 'Weak';
    } else if (strength < 50) {
        color = '#ffc107';
        text = 'Fair';
    } else if (strength < 75) {
        color = '#fd7e14';
        text = 'Good';
    } else {
        color = '#28a745';
        text = 'Strong';
    }
    
    indicator.style.width = strength + '%';
    indicator.style.backgroundColor = color;
    indicator.title = `Password strength: ${text}`;
}

// Form validation
function validateForm(form) {
    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            showFieldError(field, 'This field is required');
            isValid = false;
        } else {
            clearFieldError(field);
            
            // Email validation
            if (field.type === 'email' && !isValidEmail(field.value)) {
                showFieldError(field, 'Please enter a valid email address');
                isValid = false;
            }
            
            // Password confirmation
            if (field.name === 'confirm_password') {
                const passwordField = form.querySelector('input[name="password"]');
                if (passwordField && field.value !== passwordField.value) {
                    showFieldError(field, 'Passwords do not match');
                    isValid = false;
                }
            }
        }
    });
    
    return isValid;
}

// Show field error
function showFieldError(field, message) {
    clearFieldError(field);
    
    const error = document.createElement('div');
    error.className = 'field-error';
    error.textContent = message;
    error.style.cssText = `
        color: #dc3545;
        font-size: 0.875rem;
        margin-top: 0.25rem;
    `;
    
    field.style.borderColor = '#dc3545';
    field.parentNode.appendChild(error);
}

// Clear field error
function clearFieldError(field) {
    const existingError = field.parentNode.querySelector('.field-error');
    if (existingError) {
        existingError.remove();
    }
    field.style.borderColor = '';
}

// Email validation
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Format phone number
function formatPhoneNumber(e) {
    let value = e.target.value.replace(/\D/g, '');
    
    if (value.length >= 10) {
        value = value.substring(0, 10);
        value = value.replace(/(\d{3})(\d{3})(\d{4})/, '$1-$2-$3');
    } else if (value.length >= 6) {
        value = value.replace(/(\d{3})(\d{3})/, '$1-$2-');
    } else if (value.length >= 3) {
        value = value.replace(/(\d{3})/, '$1-');
    }
    
    e.target.value = value;
}

// Alert handling
function initializeAlerts() {
    const alerts = document.querySelectorAll('.alert');
    
    alerts.forEach(alert => {
        // Auto-hide alerts after 5 seconds
        setTimeout(() => {
            fadeOut(alert);
        }, 5000);
        
        // Add close button
        const closeBtn = document.createElement('button');
        closeBtn.innerHTML = '×';
        closeBtn.style.cssText = `
            position: absolute;
            top: 0.5rem;
            right: 0.75rem;
            background: none;
            border: none;
            font-size: 1.25rem;
            cursor: pointer;
            color: inherit;
            opacity: 0.7;
        `;
        
        alert.style.position = 'relative';
        alert.appendChild(closeBtn);
        
        closeBtn.addEventListener('click', () => fadeOut(alert));
    });
}

// Fade out element
function fadeOut(element) {
    element.style.transition = 'opacity 0.5s ease';
    element.style.opacity = '0';
    
    setTimeout(() => {
        if (element.parentNode) {
            element.parentNode.removeChild(element);
        }
    }, 500);
}

// Utility functions
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type}`;
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        max-width: 300px;
        padding: 1rem;
        border-radius: 5px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => fadeOut(notification), 3000);
}

// Confirm dialog
function confirmAction(message, callback) {
    if (confirm(message)) {
        if (typeof callback === 'function') {
            callback();
        }
        return true;
    }
    return false;
}

// Loading indicator
function showLoading(element) {
    const originalContent = element.innerHTML;
    element.innerHTML = '<span class="loading">Loading...</span>';
    element.disabled = true;
    
    return function hideLoading() {
        element.innerHTML = originalContent;
        element.disabled = false;
    };
}

// Format currency (client-side)
function formatCurrency(amount) {
    return '$' + parseFloat(amount).toFixed(2);
}

// Debounce function for search
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}