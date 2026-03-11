<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

// Get user data
$sql = "SELECT * FROM users WHERE id = $user_id";
$result = $conn->query($sql);
$user = $result ? $result->fetch_assoc() : [];

$user_name = $user['fullname'] ?? 'User';
$user_avatar = isset($user['avatar']) && $user['avatar'] ? $user['avatar'] : "https://ui-avatars.com/api/?name=" . urlencode($user_name) . "&background=10b981&color=fff";

// Get health workers dynamically from database (users who registered as health_worker)
$workers = [];
$sql = "SELECT fullname as name, specialty FROM users WHERE user_type = 'health_worker' ORDER BY fullname ASC";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $workers[] = $row;
    }
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $patient_name = $_POST['patient_name'] ?? $user_name;
    $health_worker = $_POST['health_worker'];
    $visit_type = $_POST['visit_type'];
    $appointment_date = $_POST['appointment_date'];
    $reason = $_POST['reason'];
    $notes = $_POST['notes'];

    // Try database first
    $sql = "INSERT INTO appointments (user_id, patient_name, health_worker, visit_type, appointment_date, reason, notes, status)
            VALUES ($user_id, '$patient_name', '$health_worker', '$visit_type', '$appointment_date', '$reason', '$notes', 'Pending')";

    if ($conn && $conn->query($sql) === TRUE) {
        $message = "success";
    } else {
        // Store in session
        if (!isset($_SESSION['appointments'])) {
            $_SESSION['appointments'] = [];
        }
        $_SESSION['appointments'][] = [
            'id' => time(),
            'user_id' => $user_id,
            'patient_name' => $patient_name,
            'health_worker' => $health_worker,
            'visit_type' => $visit_type,
            'appointment_date' => $appointment_date,
            'reason' => $reason,
            'notes' => $notes,
            'status' => 'Pending'
        ];
        $message = "success";
    }
}

// Get appointments - combine database and session
$user_appointments = [];

// First get from database
$sql = "SELECT * FROM appointments WHERE user_id = $user_id ORDER BY appointment_date DESC";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $user_appointments[] = $row;
    }
}

// Then add from session
if (isset($_SESSION['appointments']) && is_array($_SESSION['appointments'])) {
    foreach ($_SESSION['appointments'] as $appt) {
        if (isset($appt['user_id']) && $appt['user_id'] == $user_id) {
            $user_appointments[] = $appt;
        }
    }
    // Sort by date descending
    if (count($user_appointments) > 0) {
        usort($user_appointments, function($a, $b) {
            return strtotime($b['appointment_date']) - strtotime($a['appointment_date']);
        });
    }
}

// Pre-select worker if passed in URL
$pre_selected_worker = isset($_GET['worker']) ? $_GET['worker'] : "";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Malasakit | Appointments</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .appt-container {
            max-width: 800px;
            margin: -40px auto 80px;
            background: white;
            padding: 35px;
            border-radius: 24px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.08);
        }
        
        .appt-section { margin-bottom: 28px; }
        .appt-section h3 { color: #064e3b; margin-bottom: 14px; font-size: 1.05rem; display: flex; align-items: center; gap: 8px; }
        
        .appt-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
        
        .appt-group { margin-bottom: 18px; }
        .appt-group label { display: block; margin-bottom: 7px; font-weight: 600; color: #1f2937; font-size: 0.88rem; }
        .appt-group input, .appt-group select, .appt-group textarea {
            width: 100%;
            padding: 11px 14px;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            background: #f8fafc;
            outline: none;
            transition: 0.3s;
            font-size: 0.95rem;
            box-sizing: border-box;
        }
        .appt-group input:focus, .appt-group select:focus { border-color: #10b981; background: white; }

        .type-selector { display: flex; gap: 10px; margin-bottom: 20px; }
        .type-option {
            flex: 1;
            padding: 14px;
            border: 2px solid #f1f5f9;
            border-radius: 14px;
            text-align: center;
            cursor: pointer;
            transition: 0.3s;
        }
        .type-option.active { border-color: #10b981; background: #f0fdf4; }
        .type-option span { display: block; font-size: 1.4rem; margin-bottom: 4px; }

        .my-appt { margin-top: 35px; padding-top: 25px; border-top: 1px solid #e2e8f0; }
        .appt-card {
            background: #f8fafc;
            border-radius: 14px;
            padding: 18px;
            margin-bottom: 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-left: 4px solid #10b981;
        }
        .appt-card-info h4 { margin: 0 0 4px; color: #064e3b; font-size: 0.95rem; }
        .appt-card-info p { margin: 0; font-size: 0.82rem; color: #6b7280; }
        .appt-date-box {
            background: #ecfdf5;
            padding: 10px 14px;
            border-radius: 10px;
            text-align: center;
        }
        .appt-date-box span { display: block; font-weight: 700; color: #059669; }
        .appt-status { padding: 5px 12px; border-radius: 20px; font-size: 0.72rem; font-weight: 700; }
        .appt-status-pending { background: #fef3c7; color: #d97706; }
        .appt-status-confirmed { background: #dcfce7; color: #15803d; }

        .modal-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(6, 78, 59, 0.8); display: none;
            align-items: center; justify-content: center; z-index: 1000;
        }
        .modal-content {
            background: white; padding: 35px; border-radius: 22px; text-align: center;
            max-width: 380px; transform: scale(0.8); transition: 0.3s;
        }
        .modal-content.show { transform: scale(1); }
        .success-icon { font-size: 3.5rem; color: #10b981; margin-bottom: 18px; }

        @media (max-width: 600px) { .appt-grid { grid-template-columns: 1fr; } }
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
                <li><a href="appointments.php" class="active">Appointments</a></li>
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

    <header class="hero" style="height: 25vh;">
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <h1>Book <span class="highlight">Appointment</span></h1>
            <p>Schedule a visit with your health worker.</p>
        </div>
    </header>

    <main class="container">
        <div class="appt-container fade-in">
            <form method="POST" action="appointments.php">
                <div class="appt-section">
                    <h3>📍 Visit Type</h3>
                    <div class="type-selector">
                        <div class="type-option active" onclick="selectType(this, 'Health Center')">
                            <span>🏥</span> Health Center
                        </div>
                        <div class="type-option" onclick="selectType(this, 'Home Visit')">
                            <span>🏠</span> Home Visit
                        </div>
                    </div>
                    <input type="hidden" name="visit_type" id="visitType" value="Health Center">
                </div>

                <div class="appt-section">
                    <h3>👤 Patient Info</h3>
                    <div class="appt-grid">
                        <div class="appt-group">
                            <label>Full Name</label>
                            <input type="text" name="patient_name" value="<?php echo htmlspecialchars($user_name); ?>" required>
                        </div>
                        <div class="appt-group">
                            <label>Health Worker</label>
                            <select name="health_worker" id="workerSelect" required>
                                <option value="">Select Worker</option>
                                <?php foreach ($workers as $worker): ?>
                                <option value="<?php echo $worker['name']; ?>" <?php echo $pre_selected_worker == $worker['name'] ? 'selected' : ''; ?>>
                                    <?php echo $worker['name']; ?> - <?php echo $worker['specialty']; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="appt-section">
                    <h3>📅 Schedule</h3>
                    <div class="appt-grid">
                        <div class="appt-group">
                            <label>Preferred Date</label>
                            <input type="date" name="appointment_date" required min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="appt-group">
                            <label>Reason</label>
                            <select name="reason" required>
                                <option value="">Select Reason</option>
                                <option>General Checkup</option>
                                <option>Vaccination</option>
                                <option>Prenatal Checkup</option>
                                <option>Medicine Refill</option>
                                <option>Nutrition Monitoring</option>
                                <option>Follow-up Visit</option>
                            </select>
                        </div>
                    </div>
                    <div class="appt-group">
                        <label>Notes (Optional)</label>
                        <textarea name="notes" rows="2" placeholder="Describe your symptoms or concerns..."></textarea>
                    </div>
                </div>

                <button type="submit" class="btn-primary" style="width: 100%; padding: 16px; font-size: 1rem; border-radius: 14px;">
                    Submit Request
                </button>
            </form>

            <!-- My Appointments -->
            <?php if (count($user_appointments) > 0): ?>
            <div class="my-appt">
                <h3>📋 My Appointments (<?php echo count($user_appointments); ?>)</h3>
                <?php foreach ($user_appointments as $appt): ?>
                <div class="appt-card">
                    <div class="appt-card-info">
                        <h4><?php echo $appt['reason']; ?></h4>
                        <p><?php echo $appt['health_worker']; ?> • <?php echo $appt['visit_type']; ?></p>
                        <?php if(!empty($appt['notes'])): ?>
                        <p style="margin-top:4px;font-style:italic;"><?php echo $appt['notes']; ?></p>
                        <?php endif; ?>
                    </div>
                    <div style="display:flex;align-items:center;gap:12px;">
                        <div class="appt-date-box">
                            <span style="font-size:0.75rem;"><?php echo date('M', strtotime($appt['appointment_date'])); ?></span>
                            <span><?php echo date('d', strtotime($appt['appointment_date'])); ?></span>
                        </div>
                        <span class="appt-status appt-status-<?php echo strtolower($appt['status']); ?>">
                            <?php echo $appt['status']; ?>
                        </span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </main>

    <div class="modal-overlay" id="modalOverlay">
        <div class="modal-content" id="modalContent">
            <div class="success-icon">✅</div>
            <h2>Request Sent!</h2>
            <p style="margin:12px 0 22px;color:#64748b;">Your appointment has been booked. You'll be notified once confirmed.</p>
            <button class="btn-primary" onclick="window.location.href='appointments.php'" style="width:100%;padding:14px;border-radius:12px;">OK</button>
        </div>
    </div>

    <script>
        function selectType(element, type) {
            document.querySelectorAll('.type-option').forEach(opt => opt.classList.remove('active'));
            element.classList.add('active');
            document.getElementById('visitType').value = type;
        }
        
        window.onload = function() {
            const savedAvatar = localStorage.getItem('userAvatar');
            if (savedAvatar) document.getElementById('nav-avatar').src = savedAvatar;
            
            <?php if ($message == "success"): ?>
            document.getElementById('modalOverlay').style.display = 'flex';
            setTimeout(() => document.getElementById('modalContent').classList.add('show'), 10);
            <?php endif; ?>
        };
    </script>
</body>
</html>

