<?php
session_start();
include "db.php";

// Get user data if logged in
$user_name = "";
$user_avatar = "https://ui-avatars.com/api/?name=User&background=10b981&color=fff";

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $sql = "SELECT fullname, avatar FROM users WHERE id = $user_id";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $user_name = $row['fullname'];
        if ($row['avatar']) {
            $user_avatar = $row['avatar'];
        } else {
            $user_avatar = "https://ui-avatars.com/api/?name=" . urlencode($user_name) . "&background=10b981&color=fff";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Malasakit Healthcare | Home</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
</head>
<body>

    <nav class="navbar">
        <div class="nav-container">
            <div class="logo">🌿 Malasakit</div>
            <ul class="nav-menu">
                <li><a href="homepage.php" class="active">Home</a></li>
                <li><a href="services.php">Services</a></li>
                <li><a href="doctors.php">Doctors</a></li>
                <li><a href="appointments.php">Appointments</a></li>
                <li><a href="dashboard.php">Dashboard</a></li>
            </ul>

            <div class="profile-dropdown">
                <?php if(isset($_SESSION['user'])): ?>
                <div class="profile-icon">
                    <img id="nav-avatar" src="<?php echo $user_avatar; ?>" alt="Profile">
                </div>
                <ul class="dropdown-menu">
                    <li><a href="profile.php"><span>✏️</span> Edit Profile</a></li>
                    <li><a href="appointments.php"><span>📅</span> My Bookings</a></li>
                    <li><a href="messaging.php"><span>💬</span> Messages</a></li>
                    <li class="divider"></li>
                    <li><a href="logout.php" class="logout-btn"><span>🚪</span> Logout</a></li>
                </ul>
                <?php else: ?>
                <a href="login.php" class="btn-primary" style="padding: 8px 20px; border-radius: 20px;">Sign In</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <header class="hero">
        <div class="hero-overlay"></div>
        <div class="hero-content fade-in">
            <h1>Caring for Communities,<br><span class="highlight">One Heart at a Time.</span></h1>
            <p>Bridging the gap in rural healthcare through trained local experts and modern technology.</p>
            <div class="hero-btns">
                <button class="btn-primary" onclick="location.href='services.php'">View Services</button>
                <button class="btn-secondary" onclick="location.href='appointments.php'">Book Appointment</button>
                <button class="btn-outline-white" onclick="location.href='dashboard.php'">View Dashboard</button>
            </div>
        </div>
    </header>

    <main class="container" style="padding: 60px 20px;">
        <div class="stats-grid">
            <div class="stat-item slide-up" onclick="location.href='appointments.php'" style="cursor:pointer">
                <div class="stat-number">📅</div>
                <div class="stat-label">Schedule Visit</div>
            </div>
            <div class="stat-item slide-up" style="animation-delay: 0.1s" onclick="location.href='doctors.php'" style="cursor:pointer">
                <div class="stat-number">👩‍⚕️</div>
                <div class="stat-label">Find a Worker</div>
            </div>
            <div class="stat-item slide-up" style="animation-delay: 0.15s; border-bottom: 3px solid var(--mint);" onclick="location.href='messaging.php'" style="cursor:pointer">
                <div class="stat-number">💬</div>
                <div class="stat-label">Chat with Worker</div>
            </div>
            <div class="stat-item slide-up" style="animation-delay: 0.2s" onclick="location.href='services.php'" style="cursor:pointer">
                <div class="stat-number">💊</div>
                <div class="stat-label">Free Medicine</div>
            </div>
        </div>
    </main>

    <footer class="main-footer">
        <div class="footer-grid container">
            <div class="footer-col">
                <div class="logo-footer">🌿 Malasakit</div>
                <p>Ensuring wellness and compassion for every Filipino, regardless of distance.</p>
            </div>

            <div class="footer-col">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="dashboard.php">Health Portal</a></li>
                    <li><a href="doctors.php">Worker Directory</a></li>
                    <li><a href="messaging.php">Messaging Center</a></li>
                    <li><a href="#">Support Center</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h3>Services</h3>
                <ul>
                    <li><a href="services.php">Tele-consultation</a></li>
                    <li><a href="services.php">Rural Checkups</a></li>
                    <li><a href="services.php">Vaccination Drives</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h3>Contact</h3>
                <p>Email: <strong>care@malasakit.ph</strong></p>
                <p>Phone: <strong>+63 912 345 6789</strong></p>
            </div>
        </div>

        <div class="footer-bottom">
            &copy; 2026 Malasakit Healthcare. All rights reserved.
        </div>
    </footer>

    <script>
        window.addEventListener('load', () => {
            const savedAvatar = localStorage.getItem('userAvatar');
            if (savedAvatar) {
                document.getElementById('nav-avatar').src = savedAvatar;
            }
        });
    </script>
</body>
</html>

