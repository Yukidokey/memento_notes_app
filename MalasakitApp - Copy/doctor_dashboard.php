<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if user is a health worker
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] != 'health_worker') {
    header("Location: dashboard.php");
    exit();
}

$user_id = $_SESSION['user_id'];

include "db.php";

$sql = "SELECT * FROM users WHERE id = $user_id";
$result = $conn->query($sql);
$user = $result ? $result->fetch_assoc() : [];

$user_avatar = isset($user['avatar']) && $user['avatar'] ? $user['avatar'] : "https://ui-avatars.com/api/?name=" . urlencode($user['fullname'] ?? 'Doctor') . "&background=3b82f6&color=fff";

// Get health worker name
$worker_name = $user['fullname'] ?? 'Health Worker';

// Get appointments assigned to this health worker
$appointments = [];
$sql = "SELECT * FROM appointments WHERE health_worker = '$worker_name' ORDER BY appointment_date ASC";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $appointments[] = $row;
    }
}

// Get counts
$total_appointments = count($appointments);
$pending_count = count(array_filter($appointments, function($a) { return $a['status'] == 'Pending'; }));
$confirmed_count = count(array_filter($appointments, function($a) { return $a['status'] == 'Confirmed'; }));

// Handle appointment status update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $appointment_id = $_POST['appointment_id'];
    $action = $_POST['action'];
    
    if ($action == 'confirm') {
        $new_status = 'Confirmed';
    } elseif ($action == 'cancel') {
        $new_status = 'Cancelled';
    } else {
        $new_status = 'Pending';
    }
    
    $sql = "UPDATE appointments SET status = '$new_status' WHERE id = $appointment_id";
    $conn->query($sql);
    
    // Refresh appointments
    $sql = "SELECT * FROM appointments WHERE health_worker = '$worker_name' ORDER BY appointment_date ASC";
    $result = $conn->query($sql);
    $appointments = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $appointments[] = $row;
        }
    }
    $total_appointments = count($appointments);
    $pending_count = count(array_filter($appointments, function($a) { return $a['status'] == 'Pending'; }));
    $confirmed_count = count(array_filter($appointments, function($a) { return $a['status'] == 'Confirmed'; }));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Malasakit | Doctor Dashboard</title>
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
        
        .dash-metrics {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
        }
        
        .dash-metric {
            background: #f8fafc;
            padding: 18px;
            border-radius: 16px;
            text-align: center;
        }
        
        .dash-metric-value {
            font-size: 2rem;
            font-weight: 700;
            color: #10b981;
        }
        
        .dash-metric-label {
            font-size: 0.8rem;
            color: #6b7280;
            margin-top: 5px;
        }
        
        .appt-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .appt-table th {
            text-align: left;
            padding: 12px;
            background: #f8fafc;
            color: #6b7280;
            font-size: 0.8rem;
            text-transform: uppercase;
        }
        
        .appt-table td {
            padding: 15px 12px;
            border-bottom: 1px solid #f1f5f9;
        }
        
        .appt-table tr:hover {
            background: #f8fafc;
        }
        
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
        }
        
        .status-pending {
            background: #fef3c7;
            color: #d97706;
        }
        
        .status-confirmed {
            background: #dcfce7;
            color: #15803d;
        }
        
        .status-cancelled {
            background: #fee2e2;
            color: #dc2626;
        }
        
        .action-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.8rem;
            font-weight: 600;
            margin-right: 5px;
            transition: 0.3s;
        }
        
        .action-confirm {
            background: #10b981;
            color: white;
        }
        
        .action-confirm:hover {
            background: #059669;
        }
        
        .action-cancel {
            background: #ef4444;
            color: white;
        }
        
        .action-cancel:hover {
            background: #dc2626;
        }
        
        .patient-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .patient-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: #ecfdf5;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: #059669;
        }
        
        @media (max-width: 900px) {
            .dash-grid { grid-template-columns: 1fr; }
            .dash-metrics { grid-template-columns: repeat(2, 1fr); }
            .appt-table { display: block; overflow-x: auto; }
        }
    </style>
</head>
<body>

    <nav class="navbar">
        <div class="nav-container">
            <div class="logo">🌿 Malasakit <span style="font-size: 0.7rem; background: #10b981; color: white; padding: 2px 8px; border-radius: 10px;">Doctor</span></div>
            <ul class="nav-menu">
                <li><a href="doctor_dashboard.php">Dashboard</a></li>
                <li><a href="doctor_messaging.php">Messages</a></li>
            </ul>
            <div class="profile-dropdown">
                <div class="profile-icon">
                    <img id="nav-avatar" src="<?php echo $user_avatar; ?>" alt="Profile">
                </div>
                <ul class="dropdown-menu">
                    <li><a href="profile.php"><span>✏️</span> Edit Profile</a></li>
                    <li class="divider"></li>
                    <li><a href="logout.php" class="logout-btn"><span>🚪</span> Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <header class="hero" style="height: 20vh; background: linear-gradient(135deg, #059669 0%, #10b981 100%);">
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <h1>Welcome, Dr. <span class="highlight"><?php echo explode(' ', $worker_name)[0]; ?></span></h1>
            <p>Manage your patient appointments</p>
        </div>
    </header>

    <main class="container">
        <div class="dash-grid">
            <div class="dash-main">
                <!-- Metrics -->
                <div class="dash-card fade-in">
                    <div class="dash-card-header">
                        <h3>📊 Overview</h3>
                    </div>
                    <div class="dash-metrics">
                        <div class="dash-metric">
                            <div class="dash-metric-value"><?php echo $total_appointments; ?></div>
                            <div class="dash-metric-label">Total Appointments</div>
                        </div>
                        <div class="dash-metric">
                            <div class="dash-metric-value"><?php echo $pending_count; ?></div>
                            <div class="dash-metric-label">Pending</div>
                        </div>
                        <div class="dash-metric">
                            <div class="dash-metric-value"><?php echo $confirmed_count; ?></div>
                            <div class="dash-metric-label">Confirmed</div>
                        </div>
                    </div>
                </div>

                <!-- Appointments Table -->
                <div class="dash-card slide-up">
                    <div class="dash-card-header">
                        <h3>📅 Patient Appointments</h3>
                    </div>
                    
                    <?php if (count($appointments) > 0): ?>
                    <table class="appt-table">
                        <thead>
                            <tr>
                                <th>Patient</th>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Reason</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($appointments as $appt): ?>
                            <tr>
                                <td>
                                    <div class="patient-info">
                                        <div class="patient-avatar"><?php echo strtoupper(substr($appt['patient_name'], 0, 1)); ?></div>
                                        <div>
                                            <strong><?php echo $appt['patient_name']; ?></strong>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($appt['appointment_date'])); ?></td>
                                <td><?php echo $appt['visit_type']; ?></td>
                                <td><?php echo $appt['reason']; ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower($appt['status']); ?>">
                                        <?php echo $appt['status']; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($appt['status'] == 'Pending'): ?>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="appointment_id" value="<?php echo $appt['id']; ?>">
                                        <button type="submit" name="action" value="confirm" class="action-btn action-confirm">✓</button>
                                        <button type="submit" name="action" value="cancel" class="action-btn action-cancel">✗</button>
                                    </form>
                                    <?php elseif ($appt['status'] == 'Confirmed'): ?>
                                    <span style="color: #10b981; font-size: 0.8rem;">✓ Accepted</span>
                                    <?php else: ?>
                                    <span style="color: #ef4444; font-size: 0.8rem;">✗ Cancelled</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div style="text-align: center; padding: 40px; color: #9ca3af;">
                        <p style="font-size: 1.2rem;">No appointments yet</p>
                        <p>Patient appointments will appear here</p>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Recent Activity -->
                <div class="dash-card slide-up">
                    <div class="dash-card-header">
                        <h3>📝 Recent Patient Notes</h3>
                    </div>
                    <?php 
                    $appointments_with_notes = array_filter($appointments, function($a) { return !empty($a['notes']); });
                    if (count($appointments_with_notes) > 0): 
                    ?>
                        <?php foreach (array_slice($appointments_with_notes, 0, 3) as $appt): ?>
                        <div style="padding: 12px; border-bottom: 1px solid #f1f5f9;">
                            <strong><?php echo $appt['patient_name']; ?></strong>
                            <p style="margin: 5px 0 0; color: #6b7280; font-size: 0.9rem;"><?php echo $appt['notes']; ?></p>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                    <p style="color: #9ca3af; text-align: center; padding: 20px;">No patient notes yet</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="dash-sidebar">
                <!-- Profile Card -->
                <div class="dash-card slide-up">
                    <div style="text-align: center;">
                        <img src="<?php echo $user_avatar; ?>" style="width: 80px; height: 80px; border-radius: 50%; margin-bottom: 15px;">
                        <h3 style="margin: 0; color: #064e3b;"><?php echo $worker_name; ?></h3>
                        <p style="margin: 5px 0; color: #6b7280;"><?php echo $user['specialty'] ?? 'Health Worker'; ?></p>
                        <span style="background: #10b981; color: white; padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 600;">✓ Verified</span>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="dash-card slide-up">
                    <div class="dash-card-header">
                        <h3>⚡ Quick Actions</h3>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        <a href="doctor_messaging.php" class="dash-action-btn" style="flex-direction: row; justify-content: flex-start; padding: 15px;">
                            <span style="font-size: 1.3rem;">💬</span>
                            <small>View Messages</small>
                        </a>
                        <a href="profile.php" class="dash-action-btn" style="flex-direction: row; justify-content: flex-start; padding: 15px;">
                            <span style="font-size: 1.3rem;">✏️</span>
                            <small>Edit Profile</small>
                        </a>
                    </div>
                </div>

                <!-- Today's Schedule -->
                <div class="dash-card slide-up">
                    <div class="dash-card-header">
                        <h3>📅 Today's Schedule</h3>
                    </div>
                    <?php 
                    $today = date('Y-m-d');
                    $today_appts = array_filter($appointments, function($a) use ($today) { 
                        return $a['appointment_date'] == $today && $a['status'] == 'Confirmed'; 
                    });
                    if (count($today_appts) > 0): 
                    ?>
                        <?php foreach ($today_appts as $appt): ?>
                        <div style="padding: 12px; background: #ecfdf5; border-radius: 10px; margin-bottom: 10px;">
                            <strong><?php echo $appt['patient_name']; ?></strong>
                            <p style="margin: 3px 0 0; font-size: 0.85rem; color: #059669;"><?php echo $appt['visit_type']; ?> - <?php echo $appt['reason']; ?></p>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                    <p style="color: #9ca3af; text-align: center;">No appointments today</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <footer class="main-footer">
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

