// DarTU LIMS - Login Page JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Add loading animation on form submit
    const loginForm = document.getElementById('loginForm');
    const loginButton = document.getElementById('loginButton');
    
    if (loginForm && loginButton) {
        loginForm.addEventListener('submit', function() {
            loginButton.classList.add('loading');
            loginButton.innerHTML = '<span>Signing In...</span>';
        });
    }

    // Add focus animations
    document.querySelectorAll('.form-input, .form-select').forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.style.transform = 'scale(1.02)';
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.style.transform = 'scale(1)';
        });
    });

    // Form validation
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const roleSelect = document.getElementById('role');

    function validateForm() {
        let isValid = true;
        
        // Email validation
        if (emailInput && !emailInput.value.includes('@')) {
            showFieldError(emailInput, 'Please enter a valid email address');
            isValid = false;
        } else {
            clearFieldError(emailInput);
        }

        // Password validation
        if (passwordInput && passwordInput.value.length < 6) {
            showFieldError(passwordInput, 'Password must be at least 6 characters');
            isValid = false;
        } else {
            clearFieldError(passwordInput);
        }

        // Role validation
        if (roleSelect && !roleSelect.value) {
            showFieldError(roleSelect, 'Please select your role');
            isValid = false;
        } else {
            clearFieldError(roleSelect);
        }

        return isValid;
    }

    function showFieldError(field, message) {
        clearFieldError(field);
        const errorDiv = document.createElement('div');
        errorDiv.className = 'field-error';
        errorDiv.textContent = message;
        errorDiv.style.color = '#c53030';
        errorDiv.style.fontSize = '0.85rem';
        errorDiv.style.marginTop = '5px';
        field.parentElement.appendChild(errorDiv);
        field.style.borderColor = '#e53e3e';
    }

    function clearFieldError(field) {
        const existingError = field.parentElement.querySelector('.field-error');
        if (existingError) {
            existingError.remove();
        }
        field.style.borderColor = '#e9ecef';
    }

    // Real-time validation
    if (emailInput) {
        emailInput.addEventListener('blur', function() {
            if (this.value && !this.value.includes('@')) {
                showFieldError(this, 'Please enter a valid email address');
            } else {
                clearFieldError(this);
            }
        });
    }

    if (passwordInput) {
        passwordInput.addEventListener('blur', function() {
            if (this.value && this.value.length < 6) {
                showFieldError(this, 'Password must be at least 6 characters');
            } else {
                clearFieldError(this);
            }
        });
    }

    // Form submission validation
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            if (!validateForm()) {
                e.preventDefault();
                loginButton.classList.remove('loading');
                loginButton.innerHTML = '<i class="fas fa-sign-in-alt"></i><span>Sign In</span>';
            }
        });
    }
});
