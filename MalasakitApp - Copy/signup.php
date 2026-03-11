<?php
session_start();
include "db.php";

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $user_type = $_POST['user_type'] ?? 'patient';
    $specialty = $_POST['specialty'] ?? '';

    // Validate password length
    if (strlen($password) < 8) {
        $error = "Password must be at least 8 characters";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Check if email already exists
        $check_sql = "SELECT id FROM users WHERE email = '$email'";
        $check_result = $conn->query($check_sql);
        
        if ($check_result && $check_result->num_rows > 0) {
            $error = "Email already registered";
        } else {
            // Insert new user
            $sql = "INSERT INTO users (fullname, email, password, user_type, specialty)
                    VALUES ('$fullname', '$email', '$hashed_password', '$user_type', '$specialty')";

            if ($conn->query($sql) === TRUE) {

                // Get the newly created user ID
                $new_user_id = $conn->insert_id;
                $_SESSION['user_id'] = $new_user_id;
                $_SESSION['user'] = $fullname;
                $_SESSION['user_type'] = $user_type;

                // Redirect based on user type
                if ($user_type == 'health_worker') {
                    echo "<script>
                            alert('Account Created Successfully! Welcome, Health Worker!');
                            window.location='doctor_dashboard.php';
                          </script>";
                } else {
                    echo "<script>
                            alert('Account Created Successfully!');
                            window.location='dashboard.php';
                          </script>";
                }
                exit();

            } else {
                $error = "Error: " . $conn->error;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Malasakit | Join Us</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .password-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }
        .toggle-icon {
            position: absolute;
            right: 15px;
            cursor: pointer;
            color: var(--text-light);
            transition: 0.3s;
            z-index: 10;
        }
        .toggle-icon:hover {
            color: var(--mint);
        }
        .password-wrapper input {
            padding-right: 45px !important;
        }
        
        .user-type-selector {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .user-type-option {
            flex: 1;
            padding: 15px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            text-align: center;
            cursor: pointer;
            transition: 0.3s;
            background: #f8fafc;
        }
        
        .user-type-option:hover {
            border-color: #10b981;
        }
        
        .user-type-option.selected {
            border-color: #10b981;
            background: #f0fdf4;
        }
        
        .user-type-option input {
            display: none;
        }
        
        .user-type-option .icon {
            font-size: 2rem;
            margin-bottom: 5px;
        }
        
        .user-type-option .label {
            font-weight: 600;
            color: #1f2937;
        }
        
        .user-type-option .desc {
            font-size: 0.75rem;
            color: #6b7280;
        }
        
        .specialty-field {
            display: none;
            margin-top: 15px;
        }
        
        .specialty-field.show {
            display: block;
        }
        
        .alert {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 15px;
            font-size: 0.9rem;
        }
        
        .alert-error {
            background: #fee2e2;
            color: #dc2626;
            border: 1px solid #fca5a5;
        }
    </style>
</head>
<body class="full-screen-bg">
    <div class="overlay"></div>

    <div class="glass-card fade-in">
        <div class="logo" style="font-size: 2.5rem;">🌱</div>
        <h2 style="color: var(--emerald); margin-bottom: 5px;">Join Us</h2>
        <p style="color: var(--text-light); margin-bottom: 20px; font-size: 0.95rem;">Start your health journey with care.</p>

        <?php if ($error): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
        </div>
        <?php endif; ?>

        <form id="signup-form" action="signup.php" method="POST">

            <!-- User Type Selection -->
            <label style="display: block; text-align: left; margin-bottom: 8px; font-weight: 600; color: #1f2937;">I am a:</label>
            <div class="user-type-selector">
                <label class="user-type-option selected" id="patient-option">
                    <input type="radio" name="user_type" value="patient" checked onchange="toggleUserType()">
                    <div class="icon">🏥</div>
                    <div class="label">Patient</div>
                    <div class="desc">Seeking healthcare</div>
                </label>
                <label class="user-type-option" id="worker-option">
                    <input type="radio" name="user_type" value="health_worker" onchange="toggleUserType()">
                    <div class="icon">👨‍⚕️</div>
                    <div class="label">Health Worker</div>
                    <div class="desc">Doctor/Nurse/Midwife</div>
                </label>
            </div>

            <!-- Specialty field (shown for health workers) -->
            <div class="specialty-field" id="specialty-field">
                <div class="form-group" style="text-align: left;">
                    <label>Specialty</label>
                    <select name="specialty" id="specialty-select">
                        <option value="">Select Specialty</option>
                        <option value="General Physician">General Physician</option>
                        <option value="Midwife">Midwife</option>
                        <option value="Nurse">Nurse</option>
                        <option value="Pediatrician">Pediatrician</option>
                        <option value="Nutritionist">Nutritionist</option>
                        <option value="Barangay Health Worker">Barangay Health Worker</option>
                    </select>
                </div>
            </div>

            <div class="form-group" style="text-align: left;">
                <label>Full Name</label>
                <input type="text" name="fullname" placeholder="Juan Dela Cruz" required>
            </div>

            <div class="form-group" style="text-align: left;">
                <label>Email Address</label>
                <input type="email" name="email" placeholder="juan@email.com" required>
            </div>
            
            <div class="form-group" style="text-align: left;">
                <label>Create Password</label>
                <div class="password-wrapper">
                    <input type="password" name="password" id="signup-password" placeholder="Min. 8 characters" required>
                    <i class="fa-solid fa-eye toggle-icon" id="toggleSignupPassword"></i>
                </div>
                
                <div id="strength-meter-container" style="margin-top: 10px;">
                    <div style="height: 6px; width: 100%; background: #e2e8f0; border-radius: 3px; overflow: hidden;">
                        <div id="strength-bar" style="height: 100%; width: 0%; transition: 0.4s ease; background: #ef4444;"></div>
                    </div>

                    <div id="strength-text" style="font-size: 0.75rem; margin-top: 6px; font-weight: 700; color: #6b7280; text-align: right;">
                        STRENGTH: <span id="strength-label">WEAK</span>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-glow">Register</button>

        </form>

        <p style="margin-top: 25px; font-size: 0.9rem; color: var(--text-main);">
            Already a member? <a href="login.php" style="color: var(--mint); font-weight: 700; text-decoration: none;">Sign In</a>
        </p>
    </div>

    <script>
        function toggleUserType() {
            const isWorker = document.querySelector('input[name="user_type"]:checked').value === 'health_worker';
            document.getElementById('specialty-field').classList.toggle('show', isWorker);
            document.getElementById('patient-option').classList.toggle('selected', !isWorker);
            document.getElementById('worker-option').classList.toggle('selected', isWorker);
        }
    </script>
    <script src="script.js"></script>
</body>
</html>

