<?php
session_start();
include "db.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if user is a health worker
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] != 'health_worker') {
    header("Location: profile.php");
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
    $specialty = $_POST['specialty'];
    $avatar = $_POST['avatar'];

    $sql = "UPDATE users SET 
            fullname = '$fullname',
            phone = '$phone',
            specialty = '$specialty',
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

$user_avatar = $user['avatar'] ?: "https://ui-avatars.com/api/?name=" . urlencode($user['fullname']) . "&background=3b82f6&color=fff";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Malasakit | Doctor Profile</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        .profile-container {
            display: flex;
            max-width: 1100px;
            margin: 30px auto;
            gap: 30px;
        }
        
        .profile-sidebar {
            width: 280px;
            background: white;
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.06);
            text-align: center;
            height: fit-content;
        }
        
        .sidebar-user img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin-bottom: 15px;
            border: 4px solid #ecfdf5;
        }
        
        .sidebar-user h3 {
            margin: 0;
            color: #064e3b;
            font-size: 1.2rem;
        }
        
        .sidebar-user p {
            margin: 5px 0 0;
            color: #10b981;
            font-weight: 600;
            font-size: 0.85rem;
        }
        
        .verified-badge {
            display: inline-block;
            background: #10b981;
            color: white;
            padding: 3px 10px;
            border-radius: 15px;
            font-size: 0.7rem;
            margin-top: 8px;
        }
        
        .sidebar-nav {
            list-style: none;
            padding: 0;
            margin-top: 25px;
            text-align: left;
        }
        
        .sidebar-nav li a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 15px;
            color: #1f2937;
            text-decoration: none;
            border-radius: 10px;
            transition: 0.3s;
            font-size: 0.95rem;
        }
        
        .sidebar-nav li a:hover, .sidebar-nav li a.active {
            background: #ecfdf5;
            color: #059669;
        }
        
        .profile-content {
            flex: 1;
        }
        
        .content-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.06);
        }
        
        .content-card h2 {
            margin: 0;
            color: #064e3b;
        }
        
        .underline-left {
            width: 60px;
            height: 4px;
            background: linear-gradient(90deg, #10b981, #059669);
            border-radius: 2px;
            margin: 10px 0 25px;
        }
        
        .avatar-selection-area {
            display: flex;
            gap: 30px;
            margin-bottom: 30px;
            padding: 20px;
            background: #f8fafc;
            border-radius: 15px;
        }
        
        .current-avatar-preview {
            position: relative;
            width: 120px;
            height: 120px;
            margin: auto;
        }
        
        .current-avatar-preview img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            border: 3px solid #10b981;
        }
        
        .upload-label {
            position: absolute;
            bottom: 0;
            right: 0;
            background: #10b981;
            color: white;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            border: 3px solid white;
        }
        
        .avatar-options {
            flex: 1;
        }
        
        .avatar-options label {
            display: block;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 12px;
        }
        
        .avatar-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
        }
        
        .avatar-grid img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            cursor: pointer;
            border: 2px solid transparent;
            transition: 0.3s;
        }
        
        .avatar-grid img:hover {
            border-color: #10b981;
            transform: scale(1.1);
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }
        
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            background: #f8fafc;
            font-size: 0.95rem;
            transition: 0.3s;
            box-sizing: border-box;
        }
        
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            outline: none;
            border-color: #10b981;
            background: white;
        }
        
        .form-group input:disabled {
            background: #e2e8f0;
            cursor: not-allowed;
        }
        
        .section-title {
            color: #059669;
            font-size: 1rem;
            margin: 25px 0 15px;
            padding-bottom: 8px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            border: none;
            padding: 14px 30px;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
            width: 100%;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(16, 185, 129, 0.3);
        }
        
        .success-message {
            background: #dcfce7;
            color: #15803d;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        @media (max-width: 768px) {
            .profile-container {
                flex-direction: column;
                padding: 0 15px;
            }
            .profile-sidebar {
                width: 100%;
            }
            .form-row {
                grid-template-columns: 1fr;
            }
            .avatar-selection-area {
                flex-direction: column;
            }
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
                    <li><a href="doctor_profile.php"><span>✏️</span> Edit Profile</a></li>
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
                <p><?php echo $user['specialty'] ?? 'Health Worker'; ?></p>
                <span class="verified-badge">✓ Verified</span>
            </div>
            <ul class="sidebar-nav">
                <li class="active"><a href="doctor_profile.php"><span>👤</span> Profile</a></li>
                <li><a href="doctor_dashboard.php"><span>📊</span> Dashboard</a></li>
                <li><a href="doctor_messaging.php"><span>💬</span> Messages</a></li>
                <li class="divider" style="height: 1px; background: #eee; margin: 10px 0;"></li>
                <li><a href="logout.php" style="color: #ef4444;"><span>🚪</span> Logout</a></li>
            </ul>
        </aside>

        <section class="profile-content">
            <div class="content-card slide-up">
                <h2>Health Worker Profile</h2>
                <p style="color: var(--text-light); font-size: 0.9rem; margin-top: -10px;">Manage your professional information and availability.</p>
                <div class="underline-left"></div>

                <?php if ($message == "success"): ?>
                <div class="success-message">
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
                            <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=Doctor1" onclick="setAvatar(this.src)" alt="Avatar 1">
                            <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=Doctor2" onclick="setAvatar(this.src)" alt="Avatar 2">
                            <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=Doctor3" onclick="setAvatar(this.src)" alt="Avatar 3">
                            <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=Doctor4" onclick="setAvatar(this.src)" alt="Avatar 4">
                            <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=Midwife" onclick="setAvatar(this.src)" alt="Avatar 5">
                            <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=Nurse" onclick="setAvatar(this.src)" alt="Avatar 6">
                            <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=Physician" onclick="setAvatar(this.src)" alt="Avatar 7">
                            <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=Specialist" onclick="setAvatar(this.src)" alt="Avatar 8">
                        </div>
                    </div>
                </div>
                
                <form id="profile-form" method="POST" action="">
                    <input type="hidden" name="avatar" id="avatarInput" value="<?php echo $user['avatar']; ?>">
                    
                    <h3 class="section-title">Personal Information</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Full Name</label>
                            <input type="text" name="fullname" id="fullname" value="<?php echo $user['fullname']; ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" value="<?php echo $user['email']; ?>" disabled>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Phone Number</label>
                            <input type="text" name="phone" id="phone" value="<?php echo $user['phone']; ?>" placeholder="09XX XXX XXXX">
                        </div>
                        <div class="form-group">
                            <label>Specialty</label>
                            <select name="specialty" id="specialty" style="width: 100%; padding: 14px; border-radius: 12px; border: 1px solid #e2e8f0; background: #f8fafc;">
                                <option value="">Select Specialty</option>
                                <option value="General Physician" <?php echo $user['specialty'] == 'General Physician' ? 'selected' : ''; ?>>General Physician</option>
                                <option value="Maternal & Child Care" <?php echo $user['specialty'] == 'Maternal & Child Care' ? 'selected' : ''; ?>>Maternal & Child Care</option>
                                <option value="Pediatric Specialist" <?php echo $user['specialty'] == 'Pediatric Specialist' ? 'selected' : ''; ?>>Pediatric Specialist</option>
                                <option value="General Health Support" <?php echo $user['specialty'] == 'General Health Support' ? 'selected' : ''; ?>>General Health Support</option>
                                <option value="Nutritionist-Dietitian" <?php echo $user['specialty'] == 'Nutritionist-Dietitian' ? 'selected' : ''; ?>>Nutritionist-Dietitian</option>
                                <option value="Elderly Care" <?php echo $user['specialty'] == 'Elderly Care' ? 'selected' : ''; ?>>Elderly Care</option>
                                <option value="Community Health Worker" <?php echo $user['specialty'] == 'Community Health Worker' ? 'selected' : ''; ?>>Community Health Worker</option>
                            </select>
                        </div>
                    </div>

                    <h3 class="section-title">Professional Details</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label>License Number (Optional)</label>
                            <input type="text" placeholder="e.g. MD-123456" disabled style="background: #e2e8f0;">
                        </div>
                        <div class="form-group">
                            <label>Status</label>
                            <select disabled style="background: #e2e8f0;">
                                <option>Available</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="btn-primary" id="save-btn">Update Profile</button>
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
            saveBtn.innerText = "Saving...";
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

