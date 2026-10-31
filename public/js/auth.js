document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('form-login');
    const inputIdentity = document.getElementById('login-identity');
    const inputPassword = document.getElementById('login-password');
    const btnSubmit = document.getElementById('btn-login-submit');

    if (!loginForm || !btnSubmit) return;

    loginForm.addEventListener('submit', (e) => {
        const identityVal = inputIdentity ? inputIdentity.value.trim() : '';
        const passwordVal = inputPassword ? inputPassword.value : '';

        if (identityVal === '') {
            e.preventDefault();
            highlightError(inputIdentity, 'Email atau username wajib diisi.');
            return;
        }

        if (passwordVal === '') {
            e.preventDefault();
            highlightError(inputPassword, 'Password wajib diisi.');
            return;
        }

        btnSubmit.disabled = true;
        btnSubmit.innerHTML = `
            <span style="display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem;">
                <svg style="animation: spin 0.8s linear infinite; width: 1.1rem; height: 1.1rem;" viewBox="0 0 24 24" fill="none">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-dasharray="31.4" stroke-dashoffset="10" stroke-linecap="round"></circle>
                </svg>
                Memproses...
            </span>
        `;
    });

    [inputIdentity, inputPassword].forEach(input => {
        if (!input) return;
        input.addEventListener('input', () => {
            input.style.borderColor = '';
            removeInlineError();
        });
    });

    function highlightError(inputElement, message) {
        inputElement.style.borderColor = '#ef4444';
        inputElement.focus();

        removeInlineError();
        const alertBox = document.createElement('div');
        alertBox.className = 'auth-alert auth-alert-error';
        alertBox.id = 'client-validation-alert';
        alertBox.textContent = message;

        loginForm.insertBefore(alertBox, loginForm.firstChild);
    }

    function removeInlineError() {
        const existingAlert = document.getElementById('client-validation-alert');
        if (existingAlert) {
            existingAlert.remove();
        }
    }
});

const styleSheet = document.createElement('style');
styleSheet.textContent = `
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
`;
document.head.appendChild(styleSheet);
