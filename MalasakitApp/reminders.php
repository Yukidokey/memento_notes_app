<?php
session_start();
include "db.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user data
$sql = "SELECT * FROM users WHERE id = $user_id";
$result = $conn->query($sql);
$user = $result->fetch_assoc();

$user_avatar = $user['avatar'] ?: "https://ui-avatars.com/api/?name=" . urlencode($user['fullname']) . "&background=10b981&color=fff";

// Get reminders from database
$reminders = [];
$sql = "SELECT * FROM reminders WHERE user_id = $user_id AND status = 'Active' ORDER BY due_date ASC";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $reminders[] = $row;
    }
}

// If no reminders in database, show default reminders
$show_defaults = count($reminders) == 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Malasakit Healthcare | Reminders</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        .reminder-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 15px;
            display: flex;
            gap: 20px;
            align-items: center;
            transition: 0.3s;
        }
        .reminder-card:hover { border-color: var(--mint); transform: translateX(5px); }
        .icon-box {
            width: 50px; height: 50px;
            background: var(--sage);
            border-radius: 12px;
            display: flex; justify-content: center; align-items: center;
            font-size: 1.5rem;
        }
        .reminder-content h4 { margin: 0; color: var(--emerald); }
        .reminder-content p { margin: 5px 0 0; font-size: 0.9rem; color: var(--text-light); }
        .status-tag {
            margin-left: auto;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
        }
        .urgent { background: #fef2f2; color: #ef4444; }
        .upcoming { background: #ecfdf5; color: var(--mint); }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo">🌿 Malasakit</div>
            <ul class="nav-menu">
                <li><a href="homepage.php">Home</a></li>
                <li><a href="services.php">Services</a></li>
                <li><a href="doctors.php">Health Workers</a></li>
                <li><a href="history.php">My Records</a></li>
                <li><a href="dashboard.php">Dashboard</a></li>
            </ul>
            <div class="profile-dropdown">
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
            </div>
        </div>
    </nav>

    <main class="profile-container">
        <aside class="profile-sidebar">
            <div class="sidebar-user">
                <img id="sidebar-avatar" src="<?php echo $user_avatar; ?>" alt="User">
                <h3 id="display-name"><?php echo $user['fullname']; ?></h3>
                <p>Resident Member</p>
            </div>
            <ul class="sidebar-nav">
                <li><a href="profile.php"><span>👤</span> Personal Info</a></li>
                <li><a href="history.php"><span>📋</span> Health History</a></li>
                <li class="active"><a href="reminders.php"><span>🔔</span> Reminders</a></li>
            </ul>
        </aside>

        <section class="profile-content">
            <div class="content-card slide-up">
                <h2>Health Reminders</h2>
                <div class="underline-left"></div>

                <?php if ($show_defaults): ?>
                <!-- Default Reminders -->
                <div class="reminder-card">
                    <div class="icon-box">💊</div>
                    <div class="reminder-content">
                        <h4>Maintenance Medicine</h4>
                        <p>Take Vitamin C and Hypertension meds today at 6:00 PM.</p>
                    </div>
                    <span class="status-tag urgent">Daily</span>
                </div>

                <div class="reminder-card">
                    <div class="icon-box">🏠</div>
                    <div class="reminder-content">
                        <h4>Health Worker Visit</h4>
                        <p>Health worker Maria is scheduled to visit your Purok tomorrow.</p>
                    </div>
                    <span class="status-tag upcoming">Tomorrow</span>
                </div>

                <div class="reminder-card">
                    <div class="icon-box">💉</div>
                    <div class="reminder-content">
                        <h4>Follow-up Vaccine</h4>
                        <p>Second dose of Rabies vaccine due on Nov 15.</p>
                    </div>
                    <span class="status-tag upcoming">Next Week</span>
                </div>
                <?php else: ?>
                    <!-- Database Reminders -->
                    <?php foreach ($reminders as $reminder): ?>
                    <div class="reminder-card">
                        <div class="icon-box">
                            <?php 
                            $icon = '📅';
                            if (strpos($reminder['reminder_type'], 'Medicine') !== false) $icon = '💊';
                            elseif (strpos($reminder['reminder_type'], 'Visit') !== false) $icon = '🏠';
                            elseif (strpos($reminder['reminder_type'], 'Vaccine') !== false) $icon = '💉';
                            echo $icon;
                            ?>
                        </div>
                        <div class="reminder-content">
                            <h4><?php echo $reminder['title']; ?></h4>
                            <p><?php echo $reminder['description']; ?></p>
                        </div>
                        <span class="status-tag <?php echo $reminder['frequency'] == 'Daily' ? 'urgent' : 'upcoming'; ?>">
                            <?php echo $reminder['frequency']; ?>
                        </span>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <script>
        window.onload = function() {
            const savedAvatar = localStorage.getItem('userAvatar');
            if (savedAvatar) {
                document.getElementById('nav-avatar').src = savedAvatar;
                document.getElementById('sidebar-avatar').src = savedAvatar;
            }
        }
    </script>
</body>
</html>

