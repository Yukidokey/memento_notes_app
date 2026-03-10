/* --- PASSWORD VISIBILITY TOGGLE --- */
const toggleSignupPassword = document.querySelector('#toggleSignupPassword');
const passwordInput = document.getElementById('signup-password');

if (toggleSignupPassword && passwordInput) {
    toggleSignupPassword.addEventListener('click', function () {
        // Toggle the type attribute
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        
        // Toggle the icon (eye / eye-slash)
        this.classList.toggle('fa-eye');
        this.classList.toggle('fa-eye-slash');
    });
}

/* --- PASSWORD STRENGTH & AUTHENTICITY LOGIC --- */

// 1. List of common/forbidden passwords to block (Authenticity check)
const forbiddenPasswords = [
    "password", "password123", "12345678", "admin123", 
    "qwertyuiop", "malasakit", "healthcare", "p@ssword",
    "123456789", "welcome123"
];

const strengthBar = document.getElementById('strength-bar');
const strengthLabel = document.getElementById('strength-label');

// 2. Real-time Strength Meter Listener
if (passwordInput) {
    passwordInput.addEventListener('input', () => {
        const val = passwordInput.value;
        let strength = 0;

        // Scoring logic
        if (val.length >= 8) strength += 25; // Length
        if (val.match(/[a-z]/) && val.match(/[A-Z]/)) strength += 25; // Mixed case
        if (val.match(/\d/)) strength += 25; // Numbers
        if (val.match(/[^a-zA-Z\d]/)) strength += 25; // Special chars

        // Update Bar Width
        strengthBar.style.width = strength + "%";

        // Update Colors and Labels
        if (strength <= 25) {
            strengthBar.style.background = "#ef4444"; // Red
            strengthLabel.innerText = "WEAK";
            strengthLabel.style.color = "#ef4444";
        } else if (strength <= 75) {
            strengthBar.style.background = "#f59e0b"; // Yellow/Orange
            strengthLabel.innerText = "MEDIUM";
            strengthLabel.style.color = "#f59e0b";
        } else {
            strengthBar.style.background = "#10b981"; // Green
            strengthLabel.innerText = "STRONG";
            strengthLabel.style.color = "#10b981";
        }
    });
}

// 3. Handle Registration Submission
const signupForm = document.getElementById('signup-form');
if (signupForm) {
    signupForm.onsubmit = (e) => {
        e.preventDefault();
        
        const pwdValue = passwordInput.value;
        const pwdLower = pwdValue.toLowerCase();

        // AUTHENTICITY CHECK: Block common passwords
        if (forbiddenPasswords.includes(pwdLower)) {
            alert("Security Error: This password is too common and easily guessed. Please create a more authentic password.");
            passwordInput.style.borderColor = "#ef4444";
            passwordInput.focus();
            return;
        }

        // STRENGTH CHECK: Prevent registration if strength is "Weak"
        // We calculate strength here again to ensure it meets requirements
        let finalStrength = 0;
        if (pwdValue.length >= 8) finalStrength += 25;
        if (pwdValue.match(/[a-z]/) && pwdValue.match(/[A-Z]/)) finalStrength += 25;
        if (pwdValue.match(/\d/)) finalStrength += 25;
        if (pwdValue.match(/[^a-zA-Z\d]/)) finalStrength += 25;

        if (finalStrength < 50) {
            alert("Security Error: Your password is too weak. Please include a mix of uppercase, lowercase, numbers, and symbols.");
            return;
        }

        // Success Action
        alert("Account created successfully!");
        window.location.href = "login.html";
    };
}

/* --- PRE-EXISTING LOGIC --- */

function showSignup() {
    document.getElementById('login-box').classList.add('hidden');
    document.getElementById('signup-box').classList.remove('hidden');
}

function showLogin() {
    document.getElementById('signup-box').classList.add('hidden');
    document.getElementById('login-box').classList.remove('hidden');
}

function logout() {
    location.reload();
}