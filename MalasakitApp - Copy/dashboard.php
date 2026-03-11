<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check user type and redirect to appropriate dashboard
if (isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'health_worker') {
    header("Location: doctor_dashboard.php");
    exit();
}

$user_id = $_SESSION['user_id'];

include "db.php";

$sql = "SELECT * FROM users WHERE id = $user_id";
$result = $conn->query($sql);
$user = $result ? $result->fetch_assoc() : [];

$user_avatar = isset($user['avatar']) && $user['avatar'] ? $user['avatar'] : "https://ui-avatars.com/api/?name=" . urlencode($user['fullname'] ?? 'User') . "&background=10b981&color=fff";

// Get appointments
$appointments = [];
$sql = "SELECT * FROM appointments WHERE user_id = $user_id ORDER BY appointment_date DESC LIMIT 5";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $appointments[] = $row;
    }
}

// Add session appointments (only for current user)
if (isset($_SESSION['appointments'])) {
    $session_appts = $_SESSION['appointments'];
    foreach ($session_appts as $appt) {
        // Only add appointments belonging to the current user
        if (isset($appt['user_id']) && $appt['user_id'] == $user_id) {
            $appointments[] = $appt;
        }
    }
    usort($appointments, function($a, $b) {
        return strtotime($b['appointment_date'] ?? 0) - strtotime($a['appointment_date'] ?? 0);
    });
    $appointments = array_slice($appointments, 0, 5);
}

// Get upcoming appointment
$upcoming_appt = null;
foreach ($appointments as $appt) {
    if (($appt['appointment_date'] ?? '') >= date('Y-m-d')) {
        $upcoming_appt = $appt;
        break;
    }
}

// Get health records
$records = [];
$sql = "SELECT * FROM health_records WHERE user_id = $user_id ORDER BY record_date DESC LIMIT 5";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $records[] = $row;
    }
}

// Get reminders
$reminders = [];
$sql = "SELECT * FROM reminders WHERE user_id = $user_id AND status = 'Active' ORDER BY due_date ASC LIMIT 3";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $reminders[] = $row;
    }
}

// Get messages count (session-based)
$unread_count = 0;
for ($i = 1; $i <= 6; $i++) {
    $chat_key = "chat_$i";
    if (isset($_SESSION[$chat_key])) {
        $unread_count += count($_SESSION[$chat_key]);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Malasakit Healthcare | Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        .dash-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
            padding: 40px 0;
        }
        
        .dash-card {
            background: white;
            padding: 25px;
            border-radius: 24px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.06);
            margin-bottom: 25px;
        }
        
        .dash-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .dash-card-header h3 {
            margin: 0;
            color: #064e3b;
            font-size: 1.1rem;
        }
        
        .dash-card-header a {
            color: #10b981;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .dash-metrics {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
        }
        
        .dash-metric {
            background: #f8fafc;
            padding: 18px;
            border-radius: 16px;
            text-align: center;
        }
        
        .dash-metric-value {
            font-size: 1.4rem;
            font-weight: 700;
            color: #10b981;
        }
        
        .dash-metric-label {
            font-size: 0.75rem;
            color: #6b7280;
            margin-top: 5px;
        }
        
        .dash-appt-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            border-bottom: 1px solid #f1f5f9;
        }
        
        .dash-appt-item:last-child { border-bottom: none; }
        
        .dash-appt-date {
            background: #ecfdf5;
            color: #059669;
            padding: 10px 14px;
            border-radius: 12px;
            text-align: center;
            min-width: 55px;
        }
        
        .dash-appt-date span {
            display: block;
            font-weight: 700;
            font-size: 1.1rem;
        }
        
        .dash-appt-date small {
            font-size: 0.7rem;
            text-transform: uppercase;
        }
        
        .dash-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 700;
        }
        
        .dash-badge-confirmed { background: #dcfce7; color: #15803d; }
        .dash-badge-pending { background: #fef3c7; color: #d97706; }
        
        .dash-quick-actions {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }
        
        .dash-action-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            padding: 20px 15px;
            background: #f8fafc;
            border-radius: 16px;
            text-decoration: none;
            color: #1f2937;
            transition: 0.3s;
            border: none;
            cursor: pointer;
        }
        
        .dash-action-btn:hover {
            background: #ecfdf5;
            transform: translateY(-3px);
        }
        
        .dash-action-btn span {
            font-size: 1.5rem;
        }
        
        .dash-action-btn small {
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .dash-reminder {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            background: #fef3c7;
            border-radius: 12px;
            margin-bottom: 10px;
        }
        
        .dash-reminder-icon {
            font-size: 1.3rem;
        }
        
        .dash-reminder-text h4 {
            margin: 0;
            font-size: 0.9rem;
            color: #92400e;
        }
        
        .dash-reminder-text p {
            margin: 3px 0 0;
            font-size: 0.75rem;
            color: #b45309;
        }
        
        @media (max-width: 900px) {
            .dash-grid { grid-template-columns: 1fr; }
            .dash-metrics { grid-template-columns: repeat(2, 1fr); }
        }
    </style>
</head>
<body>

    <nav class="navbar">
        <div class="nav-container">
            <div class="logo">🌿 Malasakit</div>
            <ul class="nav-menu">
                <li><a href="homepage.php">Home</a></li>
                <li><a href="services.php">Services</a></li>
                <li><a href="doctors.php">Doctors</a></li>
                <li><a href="appointments.php">Appointments</a></li>
                <li><a href="dashboard.php" class="active">Dashboard</a></li>
            </ul>
            <div class="profile-dropdown">
                <div class="profile-icon">
                    <img id="nav-avatar" src="<?php echo $user_avatar; ?>" alt="Profile">
                </div>
                <ul class="dropdown-menu">
                    <li><a href="profile.php"><span>✏️</span> Edit Profile</a></li>
                    <li><a href="appointments.php"><span>📅</span> My Bookings</a></li>
                    <li><a href="messaging.php"><span>💬</span> Messages <?php echo $unread_count > 0 ? "($unread_count)" : ""; ?></a></li>
                    <li class="divider"></li>
                    <li><a href="logout.php" class="logout-btn"><span>🚪</span> Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <header class="hero" style="height: 22vh;">
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <h1>Welcome, <span class="highlight"><?php echo explode(' ', $user['fullname'] ?? 'User')[0]; ?></span></h1>
            <p>Here's your health overview</p>
        </div>
    </header>

    <main class="container">
        <div class="dash-grid">
            <div class="dash-main">
                <!-- Metrics -->
                <div class="dash-card fade-in">
                    <div class="dash-card-header">
                        <h3>📊 Health Metrics</h3>
                        <a href="history.php">View All →</a>
                    </div>
                    <div class="dash-metrics">
                        <div class="dash-metric">
                            <div class="dash-metric-value"><?php echo $user['blood_type'] ?? '--'; ?></div>
                            <div class="dash-metric-label">Blood Type</div>
                        </div>
                        <div class="dash-metric">
                            <div class="dash-metric-value">120/80</div>
                            <div class="dash-metric-label">Blood Pressure</div>
                        </div>
                        <div class="dash-metric">
                            <div class="dash-metric-value">72</div>
                            <div class="dash-metric-label">Heart Rate</div>
                        </div>
                        <div class="dash-metric">
                            <div class="dash-metric-value">98%</div>
                            <div class="dash-metric-label">Oxygen</div>
                        </div>
                    </div>
                </div>

                <!-- Upcoming Appointments -->
                <div class="dash-card slide-up">
                    <div class="dash-card-header">
                        <h3>📅 Upcoming Appointments</h3>
                        <a href="appointments.php">View All →</a>
                    </div>
                    <?php if (count($appointments) > 0): ?>
                        <?php foreach ($appointments as $appt): ?>
                        <div class="dash-appt-item">
                            <div class="dash-appt-date">
                                <small><?php echo date('M', strtotime($appt['appointment_date'])); ?></small>
                                <span><?php echo date('d', strtotime($appt['appointment_date'])); ?></span>
                            </div>
                            <div style="flex:1">
                                <h4 style="margin:0;font-size:0.95rem;"><?php echo $appt['reason']; ?></h4>
                                <p style="margin:3px 0 0;font-size:0.8rem;color:#6b7280;"><?php echo $appt['health_worker']; ?> • <?php echo $appt['visit_type']; ?></p>
                            </div>
                            <span class="dash-badge dash-badge-<?php echo strtolower($appt['status']); ?>"><?php echo $appt['status']; ?></span>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div style="text-align:center;padding:30px;color:#9ca3af;">
                            <p>No appointments yet</p>
                            <a href="appointments.php" class="btn-primary" style="display:inline-block;padding:12px 25px;border-radius:25px;text-decoration:none;margin-top:10px;">Book Appointment</a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Recent Records -->
                <div class="dash-card slide-up">
                    <div class="dash-card-header">
                        <h3>📋 Health Records</h3>
                        <a href="history.php">View All →</a>
                    </div>
                    <?php if (count($records) > 0): ?>
                        <?php foreach ($records as $record): ?>
                        <div class="dash-appt-item">
                            <div class="dash-appt-date">
                                <small><?php echo date('M', strtotime($record['record_date'])); ?></small>
                                <span><?php echo date('d', strtotime($record['record_date'])); ?></span>
                            </div>
                            <div style="flex:1">
                                <h4 style="margin:0;font-size:0.95rem;"><?php echo $record['record_type']; ?></h4>
                                <p style="margin:3px 0 0;font-size:0.8rem;color:#6b7280;"><?php echo $record['description']; ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="dash-appt-item">
                            <div class="dash-appt-date"><small>Oct</small><span>24</span></div>
                            <div style="flex:1"><h4 style="margin:0;">General Checkup</h4><p style="margin:3px 0 0;font-size:0.8rem;color:#6b7280;">Maria Clara • Normal</p></div>
                        </div>
                        <div class="dash-appt-item">
                            <div class="dash-appt-date"><small>Aug</small><span>12</span></div>
                            <div style="flex:1"><h4 style="margin:0;">Flu Vaccine</h4><p style="margin:3px 0 0;font-size:0.8rem;color:#6b7280;">RHU Poblacion • Completed</p></div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="dash-sidebar">
                <!-- Upcoming Visit -->
                <div class="dash-card slide-up">
                    <div class="dash-card-header">
                        <h3>Next Visit</h3>
                    </div>
                    <?php if ($upcoming_appt): ?>
                    <div style="text-align:center;padding:10px 0;">
                        <div class="dash-appt-date" style="display:inline-block;margin-bottom:15px;">
                            <small><?php echo date('M', strtotime($upcoming_appt['appointment_date'])); ?></small>
                            <span><?php echo date('d', strtotime($upcoming_appt['appointment_date'])); ?></span>
                        </div>
                        <h4 style="margin:0;"><?php echo $upcoming_appt['reason']; ?></h4>
                        <p style="margin:5px 0 15px;font-size:0.9rem;color:#6b7280;">With <?php echo $upcoming_appt['health_worker']; ?></p>
                        <span class="dash-badge dash-badge-<?php echo strtolower($upcoming_appt['status']); ?>"><?php echo $upcoming_appt['status']; ?></span>
                    </div>
                    <?php else: ?>
                    <div style="text-align:center;padding:20px 0;">
                        <p style="color:#9ca3af;margin-bottom:15px;">No upcoming visits</p>
                        <a href="appointments.php" class="btn-primary" style="display:inline-block;padding:10px 20px;border-radius:20px;text-decoration:none;">Schedule Now</a>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Quick Actions -->
                <div class="dash-card slide-up">
                    <div class="dash-card-header">
                        <h3>⚡ Quick Actions</h3>
                    </div>
                    <div class="dash-quick-actions">
                        <a href="doctors.php" class="dash-action-btn">
                            <span>👨‍⚕️</span>
                            <small>Find Doctor</small>
                        </a>
                        <a href="appointments.php" class="dash-action-btn">
                            <span>📅</span>
                            <small>Book Visit</small>
                        </a>
                        <a href="messaging.php" class="dash-action-btn">
                            <span>💬</span>
                            <small>Message</small>
                        </a>
                        <a href="reminders.php" class="dash-action-btn">
                            <span>🔔</span>
                            <small>Reminders</small>
                        </a>
                        <a href="history.php" class="dash-action-btn">
                            <span>📋</span>
                            <small>Records</small>
                        </a>
                        <a href="profile.php" class="dash-action-btn">
                            <span>👤</span>
                            <small>Profile</small>
                        </a>
                    </div>
                </div>

                <!-- Reminders -->
                <div class="dash-card slide-up">
                    <div class="dash-card-header">
                        <h3>🔔 Reminders</h3>
                        <a href="reminders.php">View All →</a>
                    </div>
                    <?php if (count($reminders) > 0): ?>
                        <?php foreach ($reminders as $reminder): ?>
                        <div class="dash-reminder">
                            <div class="dash-reminder-icon">💊</div>
                            <div class="dash-reminder-text">
                                <h4><?php echo $reminder['title']; ?></h4>
                                <p><?php echo $reminder['description']; ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="dash-reminder">
                            <div class="dash-reminder-icon">💊</div>
                            <div class="dash-reminder-text">
                                <h4>Take Vitamins</h4>
                                <p>Vitamin C - 6:00 PM</p>
                            </div>
                        </div>
                        <div class="dash-reminder" style="background:#dcfce7;">
                            <div class="dash-reminder-icon">🏥</div>
                            <div class="dash-reminder-text" style="color:#065f46;">
                                <h4>Checkup Next Week</h4>
                                <p>Maria Clara - Purok 7</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <footer class="main-footer">
        <div class="footer-grid container">
            <div class="footer-col">
                <div class="logo-footer">🌿 Malasakit</div>
                <p>Ensuring wellness for every Filipino.</p>
            </div>
            <div class="footer-col">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="doctors.php">Health Workers</a></li>
                    <li><a href="appointments.php">Appointments</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h3>Services</h3>
                <ul>
                    <li><a href="services.php">Tele-consultation</a></li>
                    <li><a href="services.php">Home Visit</a></li>
                    <li><a href="services.php">Vaccination</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">&copy; 2026 Malasakit Healthcare.</div>
    </footer>

    <script>
        window.onload = function() {
            const savedAvatar = localStorage.getItem('userAvatar');
            if (savedAvatar) document.getElementById('nav-avatar').src = savedAvatar;
        };
    </script>
</body>
</html>

