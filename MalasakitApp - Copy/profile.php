<?php
session_start();
include "db.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Redirect health workers to their profile page
if (isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'health_worker') {
    header("Location: doctor_profile.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

// Get user data
$sql = "SELECT * FROM users WHERE id = $user_id";
$result = $conn->query($sql);
$user = $result->fetch_assoc();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = $_POST['fullname'];
    $phone = $_POST['phone'];
    $birthday = $_POST['birthday'];
    $purok = $_POST['purok'];
    $barangay = $_POST['barangay'];
    $philhealth = $_POST['philhealth'];
    $blood_type = $_POST['blood_type'];
    $allergies = $_POST['allergies'];
    $avatar = $_POST['avatar'];

    $sql = "UPDATE users SET 
            fullname = '$fullname',
            phone = '$phone',
            birthday = '$birthday',
            purok = '$purok',
            barangay = '$barangay',
            philhealth = '$philhealth',
            blood_type = '$blood_type',
            allergies = '$allergies',
            avatar = '$avatar'
            WHERE id = $user_id";

    if ($conn->query($sql) === TRUE) {
        $message = "success";
        // Update session
        $_SESSION['user'] = $fullname;
        // Refresh user data
        $result = $conn->query("SELECT * FROM users WHERE id = $user_id");
        $user = $result->fetch_assoc();
    } else {
        $message = "Error: " . $conn->error;
    }
}

$user_avatar = $user['avatar'] ?: "https://ui-avatars.com/api/?name=" . urlencode($user['fullname']) . "&background=10b981&color=fff";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Malasakit Healthcare | My Profile</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
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
                    <li><a href="#"><span>⚙️</span> Settings</a></li>
                    <li class="divider"></li>
                    <li><a href="logout.php" class="logout-btn"><span>🚪</span> Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="profile-container"> 
        <aside class="profile-sidebar">
            <div class="sidebar-user">
                <img id="sidebar-avatar" src="<?php echo $user_avatar; ?>" alt="User Avatar">
                <h3 id="display-name"><?php echo $user['fullname']; ?></h3>
                <p id="display-location"><?php echo !empty($user['barangay']) ? 'Resident of ' . $user['barangay'] : 'Rural Resident'; ?></p>
            </div>
            <ul class="sidebar-nav">
                <li class="active"><a href="profile.php"><span>👤</span> Personal Info</a></li>
                <li><a href="history.php"><span>📋</span> Health History</a></li>
                <li><a href="reminders.php"><span>🔔</span> Reminders</a></li>
                <li class="divider" style="height: 1px; background: #eee; margin: 10px 0;"></li>
                <li><a href="logout.php" style="color: #ef4444;"><span>🚪</span> Logout</a></li>
            </ul>
        </aside>

        <section class="profile-content">
            <div class="content-card slide-up">
                <h2>Resident Profile Settings</h2>
                <p style="color: var(--text-light); font-size: 0.9rem; margin-top: -10px;">Keep your information updated for better local health support.</p>
                <div class="underline-left"></div>

                <?php if ($message == "success"): ?>
                <div style="background: #dcfce7; color: #15803d; padding: 15px; border-radius: 10px; margin-bottom: 20px;">
                    ✅ Profile updated successfully!
                </div>
                <?php endif; ?>

                <div class="avatar-selection-area">
                    <div class="current-avatar-preview">
                        <img id="main-profile-preview" src="<?php echo $user_avatar; ?>" alt="Preview">
                        <label for="file-upload" class="upload-label"><span>📷</span></label>
                        <input id="file-upload" type="file" accept="image/*" onchange="previewImage(event)" style="display:none;">
                    </div>
                    
                    <div class="avatar-options">
                        <label>Select a profile character</label>
                        <div class="avatar-grid">
                            <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=Felix" onclick="setAvatar(this.src)" alt="Avatar 1">
                            <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=Aneka" onclick="setAvatar(this.src)" alt="Avatar 2">
                            <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=George" onclick="setAvatar(this.src)" alt="Avatar 3">
                            <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=Sophia" onclick="setAvatar(this.src)" alt="Avatar 4">
                        </div>
                    </div>
                </div>
                
                <form id="profile-form" method="POST" action="">
                    <input type="hidden" name="avatar" id="avatarInput" value="<?php echo $user['avatar']; ?>">
                    
                    <h3 style="color: var(--mint); margin-bottom: 15px; font-size: 1rem;">Personal Information</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Full Name</label>
                            <input type="text" name="fullname" id="fullname" value="<?php echo $user['fullname']; ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" value="<?php echo $user['email']; ?>" disabled style="background: #e2e8f0;">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Birthday</label>
                            <input type="date" name="birthday" id="bday" value="<?php echo $user['birthday']; ?>">
                        </div>
                        <div class="form-group">
                            <label>Phone Number</label>
                            <input type="text" name="phone" id="phone" value="<?php echo $user['phone']; ?>" placeholder="09XX XXX XXXX">
                        </div>
                    </div>

                    <h3 style="color: var(--mint); margin-top: 20px; margin-bottom: 15px; font-size: 1rem;">Residence Details</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Purok / Sitio</label>
                            <input type="text" name="purok" id="purok" value="<?php echo $user['purok']; ?>" placeholder="e.g. Purok 4">
                        </div>
                        <div class="form-group">
                            <label>Barangay</label>
                            <input type="text" name="barangay" id="barangay" value="<?php echo $user['barangay']; ?>" placeholder="e.g. Poblacion">
                        </div>
                    </div>

                    <h3 style="color: var(--mint); margin-top: 20px; margin-bottom: 15px; font-size: 1rem;">Health Identification</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label>PhilHealth ID (Optional)</label>
                            <input type="text" name="philhealth" id="philhealth" value="<?php echo $user['philhealth']; ?>" placeholder="00-000000000-0">
                        </div>
                        <div class="form-group">
                            <label>Blood Type</label>
                            <select name="blood_type" id="blood-type" style="width: 100%; padding: 14px; border-radius: 12px; border: 1px solid #e2e8f0; background: #f8fafc;">
                                <option value="">Select Type</option>
                                <option value="A+" <?php echo $user['blood_type'] == 'A+' ? 'selected' : ''; ?>>A+</option>
                                <option value="A-" <?php echo $user['blood_type'] == 'A-' ? 'selected' : ''; ?>>A-</option>
                                <option value="B+" <?php echo $user['blood_type'] == 'B+' ? 'selected' : ''; ?>>B+</option>
                                <option value="B-" <?php echo $user['blood_type'] == 'B-' ? 'selected' : ''; ?>>B-</option>
                                <option value="AB+" <?php echo $user['blood_type'] == 'AB+' ? 'selected' : ''; ?>>AB+</option>
                                <option value="AB-" <?php echo $user['blood_type'] == 'AB-' ? 'selected' : ''; ?>>AB-</option>
                                <option value="O+" <?php echo $user['blood_type'] == 'O+' ? 'selected' : ''; ?>>O+</option>
                                <option value="O-" <?php echo $user['blood_type'] == 'O-' ? 'selected' : ''; ?>>O-</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Known Allergies / Medical Conditions</label>
                        <textarea name="allergies" id="allergies" placeholder="e.g. Penicillin allergy, Hypertension, Asthma..." rows="3" style="width: 100%; padding: 14px; border-radius: 12px; border: 1px solid #e2e8f0; background: #f8fafc; font-family: inherit;"><?php echo $user['allergies']; ?></textarea>
                    </div>

                    <button type="submit" class="btn-primary" id="save-btn" style="width: 100%; margin-top: 10px;">Update Information</button>
                </form>
            </div>
        </section>
    </main>

    <script>
        function previewImage(event) {
            const reader = new FileReader();
            reader.onload = function() {
                updateAllAvatars(reader.result);
                document.getElementById('avatarInput').value = reader.result;
                localStorage.setItem('userAvatar', reader.result);
            }
            reader.readAsDataURL(event.target.files[0]);
        }

        function setAvatar(src) {
            updateAllAvatars(src);
            document.getElementById('avatarInput').value = src;
            localStorage.setItem('userAvatar', src);
        }

        function updateAllAvatars(src) {
            document.getElementById('main-profile-preview').src = src;
            document.getElementById('sidebar-avatar').src = src;
            document.getElementById('nav-avatar').src = src;
        }

        document.getElementById('profile-form').addEventListener('submit', function(e) {
            const saveBtn = document.getElementById('save-btn');
            saveBtn.innerText = "Saving to Records...";
            saveBtn.disabled = true;
        });

        window.onload = function() {
            const savedAvatar = localStorage.getItem('userAvatar');
            if (savedAvatar) {
                updateAllAvatars(savedAvatar);
            }
        };
    </script>
</body>
</html>

