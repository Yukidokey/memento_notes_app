<?php
session_start();
include "db.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

// Get user data
$sql = "SELECT * FROM users WHERE id = $user_id";
$result = $conn->query($sql);
$user = $result->fetch_assoc();

// Get health workers for dropdown
$workers = [];
$sql = "SELECT * FROM health_workers ORDER BY name ASC";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $workers[] = $row;
    }
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $patient_name = $_POST['patient_name'];
    $health_worker = $_POST['health_worker'];
    $visit_type = $_POST['visit_type'];
    $appointment_date = $_POST['appointment_date'];
    $reason = $_POST['reason'];
    $notes = $_POST['notes'];

    $sql = "INSERT INTO appointments (user_id, patient_name, health_worker, visit_type, appointment_date, reason, notes, status)
            VALUES ($user_id, '$patient_name', '$health_worker', '$visit_type', '$appointment_date', '$reason', '$notes', 'Pending')";

    if ($conn->query($sql) === TRUE) {
        $message = "success";
    } else {
        $message = "Error: " . $conn->error;
    }
}

// Get user's appointments
$user_appointments = [];
$sql = "SELECT * FROM appointments WHERE user_id = $user_id ORDER BY appointment_date DESC";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $user_appointments[] = $row;
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
    <title>Malasakit Healthcare | Book Appointment</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        .appointment-container {
            max-width: 800px;
            margin: -50px auto 100px;
            background: white;
            padding: 40px;
            border-radius: 30px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.1);
            position: relative;
            z-index: 10;
        }

        .form-section { margin-bottom: 30px; }
        .form-section h3 { color: var(--emerald); margin-bottom: 15px; font-size: 1.1rem; display: flex; align-items: center; gap: 10px; }
        
        .input-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: var(--text-dark); font-size: 0.9rem; }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            background: #f8fafc;
            outline: none;
            transition: 0.3s;
            font-family: inherit;
        }
        .form-group input:focus, .form-group select:focus { border-color: var(--mint); background: white; }

        .type-selector { display: flex; gap: 10px; margin-bottom: 20px; }
        .type-option {
            flex: 1;
            padding: 15px;
            border: 2px solid #f1f5f9;
            border-radius: 15px;
            text-align: center;
            cursor: pointer;
            transition: 0.3s;
        }
        .type-option.active { border-color: var(--mint); background: #f0fdf4; }
        .type-option span { display: block; font-size: 1.5rem; margin-bottom: 5px; }

        /* My Appointments Section */
        .my-appointments { margin-top: 40px; padding-top: 30px; border-top: 1px solid #e2e8f0; }
        .appt-card {
            background: #f8fafc;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-left: 4px solid var(--mint);
        }
        .appt-info h4 { margin: 0 0 5px; color: var(--emerald); }
        .appt-info p { margin: 0; font-size: 0.85rem; color: var(--text-light); }
        .appt-date-badge {
            background: var(--sage);
            padding: 10px 15px;
            border-radius: 10px;
            text-align: center;
        }
        .appt-date-badge span { display: block; font-weight: 700; color: var(--emerald); }
        .status-badge { padding: 5px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; }
        .status-pending { background: #fef3c7; color: #d97706; }
        .status-confirmed { background: #dcfce7; color: #15803d; }
        .status-cancelled { background: #fee2e2; color: #dc2626; }

        /* Success Modal */
        .modal-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(6, 78, 59, 0.8); display: none;
            align-items: center; justify-content: center; z-index: 1000;
        }
        .modal-content {
            background: white; padding: 40px; border-radius: 25px; text-align: center;
            max-width: 400px; transform: scale(0.8); transition: 0.3s;
        }
        .modal-content.show { transform: scale(1); }
        .success-icon { font-size: 4rem; color: #10b981; margin-bottom: 20px; }

        @media (max-width: 600px) { .input-grid { grid-template-columns: 1fr; } }
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
                    <img id="nav-avatar" src="<?php echo $user['avatar'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($user['fullname']); ?>&background=10b981&color=fff" alt="Profile">
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

    <header class="hero" style="height: 30vh;">
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <h1>Request a <span class="highlight">Checkup</span></h1>
            <p>Fill out the form below to coordinate with your health worker.</p>
        </div>
    </header>

    <main class="container">
        <div class="appointment-container slide-up">
            <form id="appointmentForm" method="POST" action="">
                <div class="form-section">
                    <h3><span>📍</span> Where would you like to meet?</h3>
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

                <div class="form-section">
                    <h3><span>👤</span> Patient Information</h3>
                    <div class="input-grid">
                        <div class="form-group">
                            <label>Full Name</label>
                            <input type="text" name="patient_name" value="<?php echo $user['fullname']; ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Health Worker</label>
                            <select name="health_worker" id="workerSelect" required>
                                <option value="">Select a Health Worker</option>
                                <?php foreach ($workers as $worker): ?>
                                <option value="<?php echo $worker['name']; ?>" <?php echo $pre_selected_worker == $worker['name'] ? 'selected' : ''; ?>>
                                    <?php echo $worker['name']; ?> - <?php echo $worker['specialty']; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3><span>📅</span> Schedule & Reason</h3>
                    <div class="input-grid">
                        <div class="form-group">
                            <label>Preferred Date</label>
                            <input type="date" name="appointment_date" required min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="form-group">
                            <label>Reason for Visit</label>
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
                    <div class="form-group">
                        <label>Additional Notes (Optional)</label>
                        <textarea name="notes" rows="3" placeholder="e.g. I am experiencing a slight fever since yesterday..."></textarea>
                    </div>
                </div>

                <button type="submit" class="btn-primary" style="width: 100%; padding: 18px; font-size: 1.1rem; border-radius: 15px;">
                    Submit Appointment Request
                </button>
            </form>

            <!-- My Appointments Section -->
            <?php if (count($user_appointments) > 0): ?>
            <div class="my-appointments">
                <h3>My Appointments</h3>
                <?php foreach ($user_appointments as $appt): ?>
                <div class="appt-card">
                    <div class="appt-info">
                        <h4><?php echo $appt['reason']; ?></h4>
                        <p>With <?php echo $appt['health_worker']; ?> - <?php echo $appt['visit_type']; ?></p>
                        <?php if($appt['notes']): ?>
                        <p style="margin-top: 5px;"><em><?php echo $appt['notes']; ?></em></p>
                        <?php endif; ?>
                    </div>
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <div class="appt-date-badge">
                            <span style="font-size: 0.8rem;"><?php echo date('M', strtotime($appt['appointment_date'])); ?></span>
                            <span><?php echo date('d', strtotime($appt['appointment_date'])); ?></span>
                        </div>
                        <span class="status-badge status-<?php echo strtolower($appt['status']); ?>">
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
            <p style="margin: 15px 0 25px; color: #64748b;">Your health worker has been notified. Please wait for a confirmation via SMS.</p>
            <button class="btn-primary" onclick="window.location.href='appointments.php'" style="width: 100%;">Back to Appointments</button>
        </div>
    </div>

    <script>
        // Sync Navbar Avatar
        window.onload = function() {
            const savedAvatar = localStorage.getItem('userAvatar');
            if (savedAvatar) document.getElementById('nav-avatar').src = savedAvatar;
            
            // Show modal if form was submitted successfully
            <?php if ($message == "success"): ?>
            const overlay = document.getElementById('modalOverlay');
            const content = document.getElementById('modalContent');
            overlay.style.display = 'flex';
            setTimeout(() => content.classList.add('show'), 10);
            <?php endif; ?>
        };

        // Handle Visit Type Toggle
        function selectType(element, type) {
            document.querySelectorAll('.type-option').forEach(opt => opt.classList.remove('active'));
            element.classList.add('active');
            document.getElementById('visitType').value = type;
        }
    </script>
</body>
</html>

