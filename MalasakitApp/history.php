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

// Get health records from database
$records = [];
$sql = "SELECT * FROM health_records WHERE user_id = $user_id ORDER BY record_date DESC";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $records[] = $row;
    }
}

// Get user's appointments for history
$appointments = [];
$sql = "SELECT * FROM appointments WHERE user_id = $user_id AND status = 'Confirmed' ORDER BY appointment_date DESC";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $appointments[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Malasakit Healthcare | Health History</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        .history-list { margin-top: 20px; }
        .history-item {
            background: #fff;
            border-left: 5px solid var(--mint);
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .history-info h4 { margin: 0; color: var(--emerald); }
        .history-info p { margin: 5px 0 0; font-size: 0.9rem; color: var(--text-light); }
        .history-date { font-weight: 700; color: var(--mint); font-size: 0.85rem; }
        .health-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: var(--sage);
            padding: 15px;
            border-radius: 15px;
            text-align: center;
            border: 1px solid #d1fae5;
        }
        .stat-card small { display: block; color: var(--emerald); font-weight: 600; }
        .stat-card span { font-size: 1.2rem; font-weight: 700; color: var(--text-main); }
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
                <li><a href="history.php" class="active">My Records</a></li>
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
                <li class="active"><a href="history.php"><span>📋</span> Health History</a></li>
                <li><a href="reminders.php"><span>🔔</span> Reminders</a></li>
            </ul>
        </aside>

        <section class="profile-content">
            <div class="content-card slide-up">
                <h2>My Medical History</h2>
                <div class="underline-left"></div>

                <div class="health-stats-grid">
                    <div class="stat-card"><small>Blood Type</small><span id="stat-blood"><?php echo $user['blood_type'] ?: '--'; ?></span></div>
                    <div class="stat-card"><small>Last BP</small><span>120/80</span></div>
                    <div class="stat-card"><small>Weight</small><span>65 kg</span></div>
                    <div class="stat-card"><small>Height</small><span>160 cm</span></div>
                </div>

                <h3>Recent Activities</h3>
                <div class="history-list">
                    <?php if (count($records) > 0): ?>
                        <?php foreach ($records as $record): ?>
                        <div class="history-item">
                            <div class="history-info">
                                <h4><?php echo $record['record_type']; ?></h4>
                                <p><?php echo $record['description']; ?></p>
                                <?php if($record['health_worker']): ?>
                                <p>Handled by: <?php echo $record['health_worker']; ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="history-date"><?php echo date('M d, Y', strtotime($record['record_date'])); ?></div>
                        </div>
                        <?php endforeach; ?>
                    <?php elseif (count($appointments) > 0): ?>
                        <?php foreach ($appointments as $appt): ?>
                        <div class="history-item">
                            <div class="history-info">
                                <h4><?php echo $appt['reason']; ?></h4>
                                <p>With <?php echo $appt['health_worker']; ?></p>
                            </div>
                            <div class="history-date"><?php echo date('M d, Y', strtotime($appt['appointment_date'])); ?></div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="history-item">
                            <div class="history-info">
                                <h4>General Check-up</h4>
                                <p>Handled by Health Worker: Maria Clara</p>
                            </div>
                            <div class="history-date">Oct 24, 2025</div>
                        </div>
                        <div class="history-item">
                            <div class="history-info">
                                <h4>Flu Vaccination</h4>
                                <p>Location: Barangay Health Center</p>
                            </div>
                            <div class="history-date">Aug 12, 2025</div>
                        </div>
                    <?php endif; ?>
                </div>
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

