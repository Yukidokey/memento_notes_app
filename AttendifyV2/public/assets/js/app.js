document.addEventListener('DOMContentLoaded', () => {
    // Password strength meter on the registration page
    const passwordInput = document.getElementById('password');
    const strengthBar = document.getElementById('password-strength-bar');
    const strengthLabel = document.getElementById('password-strength-label');

    if (passwordInput && strengthBar && strengthLabel) {
        passwordInput.addEventListener('input', () => {
            const value = passwordInput.value || '';
            const { score, label, color, width } = evaluatePasswordStrength(value);
            strengthBar.style.width = width;
            strengthBar.style.backgroundColor = color;
            strengthLabel.textContent = label;
        });
    }

    // Password show/hide toggles
    document.querySelectorAll('.password-toggle').forEach((btn) => {
        btn.addEventListener('click', () => {
            const targetId = btn.getAttribute('data-target');
            if (!targetId) return;
            const input = document.getElementById(targetId);
            if (!input) return;
            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';
            btn.classList.toggle('is-visible', !isPassword);
        });
    });

    // Mobile sidebar toggle
    const menuBtn = document.querySelector('.mobile-menu-toggle');
    const sidebar = document.getElementById('sidebar');
    if (menuBtn && sidebar) {
        menuBtn.addEventListener('click', () => {
            sidebar.classList.toggle('is-open');
            document.body.classList.toggle('drawer-open', sidebar.classList.contains('is-open'));
        });
    }
});

function evaluatePasswordStrength(password) {
    if (!password) {
        return { score: 0, label: 'Enter a password', color: 'rgba(255,255,255,0.15)', width: '0%' };
    }

    let score = 0;
    if (password.length >= 8) score++;
    if (password.length >= 12) score++;
    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) score++;
    if (/\d/.test(password)) score++;
    if (/[^A-Za-z0-9]/.test(password)) score++;

    if (score <= 2) {
        return { score, label: 'Weak', color: '#ff6b6b', width: '33%' };
    }
    if (score === 3 || score === 4) {
        return { score, label: 'Medium', color: '#ffb648', width: '66%' };
    }
    return { score, label: 'Strong', color: '#00ffa3', width: '100%' };
}

