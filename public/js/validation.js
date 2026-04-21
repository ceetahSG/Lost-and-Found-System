/**
 * Validation Functions
 */

/**
 * Validate email
 */
function validateEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}

/**
 * Validate password strength
 */
function validatePassword(password) {
    if (password.length < 8) return false;
    if (!/[A-Z]/.test(password)) return false;
    if (!/[0-9]/.test(password)) return false;
    return true;
}

/**
 * Validate username
 */
function validateUsername(username) {
    return username.length >= 3 && /^[a-zA-Z0-9_-]+$/.test(username);
}

/**
 * Validate required field
 */
function validateRequired(value) {
    return value.trim().length > 0;
}

/**
 * Real-time form validation
 */
document.addEventListener('DOMContentLoaded', function() {
    // Email validation
    const emailInputs = document.querySelectorAll('input[type="email"]');
    emailInputs.forEach(input => {
        input.addEventListener('blur', function() {
            if (this.value && !validateEmail(this.value)) {
                this.classList.add('border-red-500');
                showFieldError(this, 'Invalid email address');
            } else {
                this.classList.remove('border-red-500');
                removeFieldError(this);
            }
        });
    });

    // Password validation
    const passwordInputs = document.querySelectorAll('input[name="password"]');
    passwordInputs.forEach(input => {
        input.addEventListener('blur', function() {
            if (this.value && !validatePassword(this.value)) {
                this.classList.add('border-red-500');
                showFieldError(this, 'Password must be 8+ chars with uppercase and number');
            } else {
                this.classList.remove('border-red-500');
                removeFieldError(this);
            }
        });
    });

    // Username validation
    const usernameInputs = document.querySelectorAll('input[name="username"]');
    usernameInputs.forEach(input => {
        input.addEventListener('blur', function() {
            if (this.value && !validateUsername(this.value)) {
                this.classList.add('border-red-500');
                showFieldError(this, 'Username 3+ chars (letters, numbers, dash, underscore)');
            } else {
                this.classList.remove('border-red-500');
                removeFieldError(this);
            }
        });
    });

    // File size validation
    const fileInputs = document.querySelectorAll('input[type="file"]');
    fileInputs.forEach(input => {
        input.addEventListener('change', function() {
            const maxSize = 5 * 1024 * 1024; // 5MB
            if (this.files[0] && this.files[0].size > maxSize) {
                this.classList.add('border-red-500');
                showFieldError(this, 'File too large (max 5MB)');
                this.value = '';
            } else {
                this.classList.remove('border-red-500');
                removeFieldError(this);
            }
        });
    });
});

/**
 * Show field error
 */
function showFieldError(field, message) {
    removeFieldError(field);
    const errorDiv = document.createElement('p');
    errorDiv.className = 'text-red-500 text-xs mt-1 field-error';
    errorDiv.textContent = message;
    field.parentNode.appendChild(errorDiv);
}

/**
 * Remove field error
 */
function removeFieldError(field) {
    const error = field.parentNode.querySelector('.field-error');
    if (error) error.remove();
}

/**
 * Validate form before submission
 */
function validateForm(formElement) {
    const requiredFields = formElement.querySelectorAll('[required]');
    let isValid = true;

    requiredFields.forEach(field => {
        if (!validateRequired(field.value)) {
            showFieldError(field, 'This field is required');
            isValid = false;
        }
    });

    return isValid;
}

// Export functions
window.validateEmail = validateEmail;
window.validatePassword = validatePassword;
window.validateUsername = validateUsername;
window.validateRequired = validateRequired;
window.validateForm = validateForm;