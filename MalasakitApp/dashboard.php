<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

include "db.php";

$user_id = $_SESSION['user_id'];

// Get user data
$sql = "SELECT * FROM users WHERE id = $user_id";
$result = $conn->query($sql);
$user = $result->fetch_assoc();

$user_avatar = $user['avatar'] ?: "https://ui-avatars.com/api/?name=" . urlencode($user['fullname']) . "&background=10b981&color=fff";

// Get user's appointments
$appointments = [];
$sql = "SELECT * FROM appointments WHERE user_id = $user_id ORDER BY appointment_date DESC LIMIT 5";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $appointments[] = $row;
    }
}

// Get health records
$records = [];
$sql = "SELECT * FROM health_records WHERE user_id = $user_id ORDER BY record_date DESC LIMIT 5";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $records[] = $row;
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
        .dashboard-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
            padding: 40px 0;
        }

        .card {
            background: white;
            padding: 25px;
            border-radius: 24px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        /* Health Metrics */
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
        }
        .metric-box {
            background: #f8fafc;
            padding: 20px;
            border-radius: 18px;
            text-align: center;
        }
        .metric-value { font-size: 1.5rem; font-weight: 700; color: var(--emerald); }
        .metric-label { font-size: 0.8rem; color: var(--text-light); }

        /* Appointments List */
        .appt-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            border-bottom: 1px solid #f1f5f9;
        }
        .appt-date {
            background: var(--sage);
            color: var(--emerald);
            padding: 10px;
            border-radius: 12px;
            text-align: center;
            min-width: 60px;
        }
        .appt-date span { display: block; font-weight: 700; }

        /* Status colors */
        .badge { padding: 4px 12px; border-radius: 20px; font-size: 0.7rem; font-weight: 700; }
        .badge-confirmed { background: #dcfce7; color: #15803d; }
        .badge-pending { background: #fef3c7; color: #d97706; }

        @media (max-width: 900px) {
            .dashboard-grid { grid-template-columns: 1fr; }
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
                    <li><a href="messaging.php"><span>💬</span> Messages</a></li>
                    <li class="divider"></li>
                    <li><a href="logout.php" class="logout-btn"><span>🚪</span> Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <header class="hero" style="height: 25vh;">
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <h1>Patient <span class="highlight">Dashboard</span></h1>
            <p>Welcome back, <?php echo $user['fullname']; ?>! Here is your health overview.</p>
        </div>
    </header>

    <main class="container">
        <div class="dashboard-grid">
            <div class="main-content">
                <div class="card fade-in">
                    <div class="card-header">
                        <h3>Latest Vital Signs</h3>
                        <small>Last updated: Today, 8:30 AM</small>
                    </div>
                    <div class="metrics-grid">
                        <div class="metric-box">
                            <div class="metric-value">120/80</div>
                            <div class="metric-label">Blood Pressure</div>
                        </div>
                        <div class="metric-box">
                            <div class="metric-value">72</div>
                            <div class="metric-label">Heart Rate (bpm)</div>
                        </div>
                        <div class="metric-box">
                            <div class="metric-value">98%</div>
                            <div class="metric-label">Oxygen Sat.</div>
                        </div>
                        <div class="metric-box">
                            <div class="metric-value">36.5°C</div>
                            <div class="metric-label">Temperature</div>
                        </div>
                    </div>
                </div>

                <div class="card slide-up">
                    <h3>Recent Health Records</h3>
                    <?php if (count($records) > 0): ?>
                        <?php foreach ($records as $record): ?>
                        <div class="appt-item">
                            <div class="appt-date">
                                <?php echo date('M', strtotime($record['record_date'])); ?><span><?php echo date('d', strtotime($record['record_date'])); ?></span>
                            </div>
                            <div style="flex:1">
                                <h4><?php echo $record['record_type']; ?></h4>
                                <p style="font-size: 0.85rem; color: #64748b;"><?php echo $record['description']; ?></p>
                            </div>
                            <a href="#" style="color: var(--emerald); font-weight: 600;">View Result</a>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="appt-item">
                            <div class="appt-date">Mar<span>02</span></div>
                            <div style="flex:1">
                                <h4>Vaccination Completed</h4>
                                <p style="font-size: 0.85rem; color: #64748b;">Dose 2: Flu Vaccine • RHU Poblacion</p>
                            </div>
                            <a href="#" style="color: var(--emerald); font-weight: 600;">View Result</a>
                        </div>
                        <div class="appt-item">
                            <div class="appt-date">Feb<span>15</span></div>
                            <div style="flex:1">
                                <h4>General Consultation</h4>
                                <p style="font-size: 0.85rem; color: #64748b;">Dr. Jose Rizal • Normal findings</p>
                            </div>
                            <a href="#" style="color: var(--emerald); font-weight: 600;">View Result</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="sidebar">
                <div class="card slide-up">
                    <div class="card-header">
                        <h3>Upcoming Visit</h3>
                    </div>
                    <?php if (count($appointments) > 0): ?>
                        <?php $next_appt = $appointments[0]; ?>
                        <div style="text-align: center; padding: 10px 0;">
                            <div class="appt-date" style="display: inline-block; margin-bottom: 15px; width: 80px;">
                                <?php echo date('M', strtotime($next_appt['appointment_date'])); ?> <span style="font-size: 1.5rem;"><?php echo date('d', strtotime($next_appt['appointment_date'])); ?></span>
                            </div>
                            <h4><?php echo $next_appt['reason']; ?></h4>
                            <p style="font-size: 0.9rem; margin-bottom: 15px;">With <?php echo $next_appt['health_worker']; ?></p>
                            <span class="badge badge-<?php echo strtolower($next_appt['status']); ?>"><?php echo $next_appt['status']; ?></span>
                        </div>
                    <?php else: ?>
                        <div style="text-align: center; padding: 10px 0;">
                            <div class="appt-date" style="display: inline-block; margin-bottom: 15px; width: 80px;">
                                Mar <span style="font-size: 1.5rem;">12</span>
                            </div>
                            <h4>Prenatal Checkup</h4>
                            <p style="font-size: 0.9rem; margin-bottom: 15px;">With Maria Clara, RM</p>
                            <span class="badge badge-confirmed">Confirmed</span>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="card slide-up" style="background: var(--sage); border: none;">
                    <h3>Quick Support</h3>
                    <p style="font-size: 0.85rem; margin-bottom: 15px;">Need immediate help? Contact your assigned Barangay Health Worker.</p>
                    <button class="btn-primary" style="width: 100%;" onclick="location.href='doctors.php'">Contact Now</button>
                </div>
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
        <div class="footer-bottom">&copy; 2026 Malasakit Healthcare. All rights reserved.</div>
    </footer>

    <script>
        window.onload = function() {
            const savedAvatar = localStorage.getItem('userAvatar');
            if (savedAvatar) document.getElementById('nav-avatar').src = savedAvatar;
        };
    </script>
</body>
</html>

