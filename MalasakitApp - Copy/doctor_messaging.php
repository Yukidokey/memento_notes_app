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
$worker_name = $user['fullname'] ?? 'Health Worker';

// Get patients who have messaged this health worker - from appointments AND from users table
$patients = [];

// First get from appointments
$sql = "SELECT DISTINCT u.id, u.fullname, u.avatar 
        FROM users u 
        INNER JOIN appointments a ON u.id = a.user_id 
        WHERE a.health_worker = '$worker_name' 
        ORDER BY u.fullname";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $patients[] = $row;
    }
}

// Also get all patients from users table (in case they haven't booked yet but want to chat)
$sql = "SELECT id, fullname, avatar FROM users WHERE user_type = 'patient' ORDER BY fullname ASC";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Check if already in list
        $exists = false;
        foreach ($patients as $p) {
            if ($p['id'] == $row['id']) {
                $exists = true;
                break;
            }
        }
        if (!$exists) {
            $patients[] = $row;
        }
    }
}

// Default to first patient if none selected
$selected_patient_id = isset($_GET['patient']) ? intval($_GET['patient']) : 0;

// Get messages with selected patient from database
$messages = [];
if ($selected_patient_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM messages WHERE (patient_id = ? AND health_worker_id = ?) ORDER BY created_at ASC");
    if ($stmt) {
        $stmt->bind_param("ii", $selected_patient_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $messages[] = $row;
        }
        $stmt->close();
    }
}

// Handle sending message
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['message'])) {
    $patient_id = intval($_POST['patient_id']);
    $message = trim($_POST['message']);
    if (!empty($message) && $patient_id > 0) {
        // Save message to database
        $stmt = $conn->prepare("INSERT INTO messages (patient_id, health_worker_id, sender_type, message, created_at) VALUES (?, ?, 'health_worker', ?, NOW())");
        if ($stmt) {
            $stmt->bind_param("iis", $patient_id, $user_id, $message);
            $stmt->execute();
            $stmt->close();
        }
    }
    header("Location: doctor_messaging.php?patient=$patient_id");
    exit();
}

$selected_patient = null;
foreach ($patients as $p) {
    if ($p['id'] == $selected_patient_id) {
        $selected_patient = $p;
        break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Malasakit | Doctor Messages</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { margin: 0; background: #f0fdf4; }
        
        .chat-wrapper {
            max-width: 1100px;
            margin: -30px auto 50px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.1);
            display: flex;
            min-height: 600px;
            overflow: hidden;
        }
        
        .chat-sidebar {
            width: 320px;
            background: #fafbfc;
            border-right: 1px solid #e5e7eb;
            display: flex;
            flex-direction: column;
        }
        
        .chat-sidebar-header {
            padding: 20px;
            background: linear-gradient(135deg, #059669, #10b981);
            color: white;
        }
        
        .chat-sidebar-header h2 { margin: 0 0 10px 0; font-size: 1.3rem; }
        .chat-sidebar-header p { margin: 0; font-size: 0.85rem; opacity: 0.9; }
        
        .chat-contacts {
            flex: 1;
            overflow-y: auto;
        }
        
        .chat-contact {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            gap: 12px;
            cursor: pointer;
            transition: 0.2s;
            border-bottom: 1px solid #f3f4f6;
            text-decoration: none;
            color: inherit;
        }
        
        .chat-contact:hover { background: #f0fdf4; }
        
        .chat-contact.active {
            background: #ecfdf5;
            border-left: 4px solid #059669;
        }
        
        .chat-contact-img {
            width: 46px;
            height: 46px;
            border-radius: 50%;
        }
        
        .chat-contact-img img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .chat-contact-details h4 {
            margin: 0;
            font-size: 0.95rem;
            color: #1f2937;
        }
        
        .chat-contact-details p {
            margin: 3px 0 0;
            font-size: 0.8rem;
            color: #6b7280;
        }
        
        .chat-main {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        .chat-header {
            padding: 15px 25px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            gap: 12px;
            background: white;
        }
        
        .chat-header-img {
            width: 42px;
            height: 42px;
            border-radius: 50%;
        }
        
        .chat-header-text h3 {
            margin: 0;
            font-size: 1rem;
            color: #1f2937;
        }
        
        .chat-header-text p {
            margin: 2px 0 0;
            font-size: 0.8rem;
            color: #059669;
        }
        
        .chat-messages {
            flex: 1;
            padding: 20px 25px;
            overflow-y: auto;
            background: #fff;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        
        .chat-msg {
            display: flex;
            gap: 10px;
            max-width: 70%;
        }
        
        .chat-msg.sent {
            align-self: flex-end;
            flex-direction: row-reverse;
        }
        
        .chat-msg-img {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            flex-shrink: 0;
        }
        
        .chat-msg-content {
            padding: 11px 15px;
            border-radius: 16px;
            font-size: 0.94rem;
            line-height: 1.5;
        }
        
        .chat-msg:not(.sent) .chat-msg-content {
            background: white;
            border: 1px solid #e5e7eb;
            border-bottom-left-radius: 4px;
        }
        
        .chat-msg.sent .chat-msg-content {
            background: linear-gradient(135deg, #059669, #10b981);
            color: white;
            border-bottom-right-radius: 4px;
        }
        
        .chat-msg-time {
            font-size: 0.7rem;
            margin-top: 4px;
            opacity: 0.7;
        }
        
        .chat-input {
            padding: 15px 25px;
            border-top: 1px solid #e5e7eb;
            background: white;
        }
        
        .chat-input form {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #f3f4f6;
            border-radius: 25px;
            padding: 6px 15px;
        }
        
        .chat-input form input[type="text"] {
            flex: 1;
            border: none;
            background: none;
            padding: 10px;
            font-size: 0.95rem;
            outline: none;
        }
        
        .chat-send-btn {
            background: #059669 !important;
            color: white !important;
            padding: 8px 20px !important;
            border-radius: 20px !important;
            font-weight: 600;
            border: none;
            cursor: pointer;
        }
        
        .chat-empty {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #9ca3af;
        }
        
        .chat-empty span { font-size: 3.5rem; margin-bottom: 10px; }
        
        @media (max-width: 768px) {
            .chat-wrapper { flex-direction: column; margin: 0; border-radius: 0; min-height: 100vh; }
            .chat-sidebar { width: 100%; max-height: 180px; }
            .chat-contacts { display: flex; overflow-x: auto; }
            .chat-contact { min-width: 140px; flex-direction: column; text-align: center; padding: 10px; }
            .chat-contact-details { display: none; }
        }
    </style>
</head>
<body>

    <nav class="navbar">
        <div class="nav-container">
            <div class="logo">🌿 Malasakit <span style="font-size: 0.7rem; background: #10b981; color: white; padding: 2px 8px; border-radius: 10px;">Doctor</span></div>
            <ul class="nav-menu">
                <li><a href="doctor_dashboard.php">Dashboard</a></li>
                <li><a href="doctor_messaging.php" class="active">Messages</a></li>
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

    <div style="height: 50px;"></div>

    <main>
        <div class="chat-wrapper fade-in">
            <!-- Sidebar -->
            <aside class="chat-sidebar">
                <div class="chat-sidebar-header">
                    <h2>Patient Messages</h2>
                    <p>Chat with your patients</p>
                </div>
                <div class="chat-contacts">
                    <?php if (count($patients) > 0): ?>
                        <?php foreach ($patients as $patient): ?>
                        <a href="doctor_messaging.php?patient=<?php echo $patient['id']; ?>" class="chat-contact <?php echo $selected_patient_id == $patient['id'] ? 'active' : ''; ?>">
                            <div class="chat-contact-img">
                                <img src="<?php echo $patient['avatar'] ?? "https://ui-avatars.com/api/?name=" . urlencode($patient['fullname']) . "&background=10b981&color=fff"; ?>" alt="<?php echo $patient['fullname']; ?>">
                            </div>
                            <div class="chat-contact-details">
                                <h4><?php echo $patient['fullname']; ?></h4>
                                <p>Patient</p>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div style="padding: 20px; text-align: center; color: #9ca3af;">
                            <p>No patients yet</p>
                            <p style="font-size: 0.8rem;">Patients will appear after booking appointments</p>
                        </div>
                    <?php endif; ?>
                </div>
            </aside>

            <!-- Chat Main -->
            <section class="chat-main">
                <?php if ($selected_patient): ?>
                <div class="chat-header">
                    <img src="<?php echo $selected_patient['avatar'] ?? "https://ui-avatars.com/api/?name=" . urlencode($selected_patient['fullname']) . "&background=10b981&color=fff"; ?>" class="chat-header-img">
                    <div class="chat-header-text">
                        <h3><?php echo $selected_patient['fullname']; ?></h3>
                        <p>Patient</p>
                    </div>
                </div>

                <div class="chat-messages" id="chatMessages">
                    <?php if (count($messages) > 0): ?>
                        <?php foreach ($messages as $msg): ?>
                        <div class="chat-msg <?php echo $msg['sender_type'] == 'health_worker' ? 'sent' : ''; ?>">
                            <img src="<?php echo $msg['sender_type'] == 'health_worker' ? $user_avatar : ($msg['avatar'] ?? "https://ui-avatars.com/api/?name=Patient&background=10b981&color=fff"); ?>" class="chat-msg-img">
                            <div class="chat-msg-content">
                                <?php echo htmlspecialchars($msg['message']); ?>
                                <div class="chat-msg-time"><?php echo date('H:i', strtotime($msg['created_at'])); ?></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="chat-input">
                    <form method="POST" action="doctor_messaging.php?patient=<?php echo $selected_patient_id; ?>">
                        <input type="hidden" name="patient_id" value="<?php echo $selected_patient_id; ?>">
                        <input type="text" name="message" placeholder="Type your reply..." required>
                        <button type="submit" class="chat-send-btn">Send</button>
                    </form>
                </div>
                <?php else: ?>
                <div class="chat-empty">
                    <span>💬</span>
                    <p>Select a patient to start chatting</p>
                </div>
                <?php endif; ?>
            </section>
        </div>
    </main>

    <script>
        const chatMsgs = document.getElementById('chatMessages');
        if (chatMsgs) chatMsgs.scrollTop = chatMsgs.scrollHeight;
        
        window.onload = function() {
            const savedAvatar = localStorage.getItem('userAvatar');
            if (savedAvatar) document.getElementById('nav-avatar').src = savedAvatar;
        };
    </script>
</body>
</html>

