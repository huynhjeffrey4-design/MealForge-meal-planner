document.getElementById('registrationForm').addEventListener('submit', async (event) => {
    event.preventDefault();
    clearErrors();

    const formData = new FormData(event.target);
    const rawData = Object.fromEntries(formData.entries());
    
    // Process data
    const userData = {
        ...rawData,
        email: rawData.email.toLowerCase().trim()
    };

    // Validation checks
    let isValid = true;

    // Required fields
    const required = ['firstName', 'lastName', 'birthdate', 'country', 'address', 'email', 'password'];
    required.forEach(field => {
        if (!userData[field]?.trim()) {
            showError(field, `${fieldName(field)} is required`);
            isValid = false;
        }
    });

    // Email validation
    if (userData.email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(userData.email)) {
        showError('email', 'Please enter a valid email address');
        isValid = false;
    }

    // Password validation
    if (userData.password && userData.password.length < 8) {
        showError('password', 'Password must be at least 8 characters');
        isValid = false;
    }

    // Birthdate validation
    if (userData.birthdate && !/^\d{4}-\d{2}-\d{2}$/.test(userData.birthdate)) {
        showError('birthdate', 'Invalid date format (YYYY-MM-DD)');
        isValid = false;
    }

    if (!isValid) return;

    try {
        const response = await fetch('/CSE442/2025-Spring/cse-442v/register.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(userData)
        });

        const data = await response.json();

        if (!response.ok || !data.success) {
            throw new Error(data.message || 'Registration failed');
        }

        alert('Registration successful! Redirecting to login...');
        window.location.href = 'login.html';

    } catch (error) {
        showError('general', error.message);
    }
});

function fieldName(field) {
    return {
        firstName: 'First name',
        lastName: 'Last name',
        birthdate: 'Birthdate',
        country: 'Country',
        address: 'Address',
        email: 'Email',
        password: 'Password'
    }[field] || field;
}

function clearErrors() {
    document.querySelectorAll('.error-message').forEach(el => {
        el.textContent = '';
    });
}

function showError(fieldId, message) {
    const errorContainer = document.getElementById(`${fieldId}Error`) || createErrorContainer(fieldId);
    errorContainer.textContent = message;
}

function createErrorContainer(fieldId) {
    const container = document.createElement('div');
    container.className = 'error-message';
    container.id = `${fieldId}Error`;
    
    const field = document.getElementById(fieldId);
    if (field) {
        field.parentNode.appendChild(container);
    } else {
        document.getElementById('generalError').after(container);
    }
    return container;
}