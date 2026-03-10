document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("loginForm");
  const errorBox = document.getElementById("loginError");

  if (!form || !errorBox) return;

  form.addEventListener("submit", async function (e) {
    e.preventDefault();

    const input = document.getElementById("username").value.trim();
    const password = document.getElementById("password").value;

    errorBox.textContent = "";

    if (!input || !password) {
      errorBox.textContent = "Please enter your username or email and password.";
      return;
    }

    try {
      const response = await fetch('login.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ username: input, password: password })
      });

      const result = await response.json();

      if (result.status === "success") {
        // Optional: store username for UI display
        localStorage.setItem("username", input);
        window.location.href = "dashboard.php"; // redirect to dashboard
      } else {
        errorBox.textContent = result.message;
      }
    } catch (error) {
      errorBox.textContent = "Database connection error. Check if XAMPP is running.";
    }
  });
});