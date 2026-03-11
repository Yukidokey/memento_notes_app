<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'health_worker') {
    header("Location: doctor_messaging.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT * FROM users WHERE id = $user_id";
$result = $conn->query($sql);
$user = $result ? $result->fetch_assoc() : [];

$user_avatar = isset($user['avatar']) && $user['avatar'] ? $user['avatar'] : "https://ui-avatars.com/api/?name=" . urlencode($user['fullname'] ?? 'User') . "&background=10b981&color=fff";
$user_name = $user['fullname'] ?? 'User';

$workers = [];

$sql = "SELECT id, name, specialty, status, image_url FROM health_workers ORDER BY status DESC, name ASC";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $workers[] = $row;
    }
}

$sql = "SELECT id, fullname as name, specialty, avatar as image_url FROM users WHERE user_type = 'health_worker' ORDER BY fullname ASC";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $exists = false;
        foreach ($workers as $w) {
            if (strtolower($w['name']) == strtolower($row['name'])) {
                $exists = true;
                break;
            }
        }
        if (!$exists) {
            $workers[] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'specialty' => $row['specialty'] ?? 'Health Worker',
                'status' => 'Available',
                'image_url' => $row['image_url'] ?? "https://ui-avatars.com/api/?name=" . urlencode($row['name']) . "&background=3b82f6&color=fff"
            ];
        }
    }
}

if (count($workers) == 0) {
    $workers = [];
}

$selected_worker_id = isset($_GET['worker']) ? intval($_GET['worker']) : 0;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['message'])) {
    $worker_id = intval($_POST['worker_id']);
    $message = trim($_POST['message']);
    $sender_type = 'patient'; // This page is for patients
    $patient_id = $user_id;
    if (!empty($message) && $worker_id > 0) {
        $stmt = $conn->prepare("INSERT INTO messages (patient_id, worker_id, sender_type, message, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param('iiss', $patient_id, $worker_id, $sender_type, $message);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: messaging.php?worker=$worker_id");
    exit();
}

$selected_worker = null;
foreach ($workers as $w) { 
    if ($w['id'] == $selected_worker_id) { 
        $selected_worker = $w; 
        break; 
    } 
}

// Fetch chat messages from the database (both directions)
$chat_messages = [];
if ($selected_worker_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM messages WHERE (patient_id = ? AND worker_id = ?) ORDER BY created_at ASC");
    $stmt->bind_param('ii', $user_id, $selected_worker_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $chat_messages[] = $row;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Malasakit | Messages</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { margin: 0; background: #f0fdf4; }
        .chat-wrapper { max-width: 1100px; margin: -30px auto 50px; background: white; border-radius: 20px; box-shadow: 0 8px 30px rgba(0,0,0,0.1); display: flex; min-height: 600px; overflow: hidden; }
        .chat-sidebar { width: 320px; background: #fafbfc; border-right: 1px solid #e5e7eb; display: flex; flex-direction: column; }
        .chat-sidebar-header { padding: 20px; background: linear-gradient(135deg, #10b981, #059669); color: white; }
        .chat-sidebar-header h2 { margin: 0 0 15px 0; font-size: 1.3rem; }
        .chat-search { position: relative; }
        .chat-search input { width: 100%; padding: 10px 15px 10px 40px; border: none; border-radius: 20px; background: rgba(255,255,255,0.2); color: white; font-size: 0.9rem; box-sizing: border-box; }
        .chat-search input::placeholder { color: rgba(255,255,255,0.7); }
        .chat-search span { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); }
        .chat-contacts { flex: 1; overflow-y: auto; }
        .chat-contact { display: flex; align-items: center; padding: 15px 20px; gap: 12px; cursor: pointer; transition: 0.2s; border-bottom: 1px solid #f3f4f6; text-decoration: none; color: inherit; }
        .chat-contact:hover { background: #f0fdf4; }
        .chat-contact.active { background: #ecfdf5; border-left: 4px solid #10b981; }
        .chat-contact-img { width: 46px; height: 46px; border-radius: 50%; position: relative; }
        .chat-contact-img img { width: 100%; height: 100%; border-radius: 50%; object-fit: cover; }
        .online-status { position: absolute; bottom: 0; right: 0; width: 12px; height: 12px; background: #10b981; border-radius: 50%; border: 2px solid #fafbfc; }
        .chat-contact-details h4 { margin: 0; font-size: 0.95rem; color: #1f2937; }
        .chat-contact-details p { margin: 3px 0 0; font-size: 0.8rem; color: #6b7280; }
        .chat-main { flex: 1; display: flex; flex-direction: column; }
        .chat-header { padding: 15px 25px; border-bottom: 1px solid #e5e7eb; display: flex; align-items: center; justify-content: space-between; background: white; }
        .chat-header-info { display: flex; align-items: center; gap: 12px; }
        .chat-header-img { width: 42px; height: 42px; border-radius: 50%; }
        .chat-header-text h3 { margin: 0; font-size: 1rem; color: #1f2937; }
        .chat-header-text p { margin: 2px 0 0; font-size: 0.8rem; color: #10b981; }
        .chat-header-actions { display: flex; gap: 15px; }
        .chat-header-actions button { background: none; border: none; font-size: 1.1rem; cursor: pointer; color: #6b7280; padding: 8px; border-radius: 50%; }
        .chat-header-actions button:hover { background: #f3f4f6; color: #10b981; }
        .chat-messages { flex: 1; padding: 20px 25px; overflow-y: auto; background: #fff; display: flex; flex-direction: column; gap: 12px; }
        .chat-msg { display: flex; gap: 10px; max-width: 70%; }
        .chat-msg.sent { align-self: flex-end; flex-direction: row-reverse; }
        .chat-msg-img { width: 30px; height: 30px; border-radius: 50%; flex-shrink: 0; }
        .chat-msg-content { padding: 11px 15px; border-radius: 16px; font-size: 0.94rem; line-height: 1.5; }
        .chat-msg:not(.sent) .chat-msg-content { background: white; border: 1px solid #e5e7eb; border-bottom-left-radius: 4px; }
        .chat-msg.sent .chat-msg-content { background: linear-gradient(135deg, #10b981, #059669); color: white; border-bottom-right-radius: 4px; }
        .chat-msg-time { font-size: 0.7rem; margin-top: 4px; opacity: 0.7; }
        .chat-input { padding: 15px 25px; border-top: 1px solid #e5e7eb; background: white; }
        .chat-input form { display: flex; align-items: center; gap: 10px; background: #f3f4f6; border-radius: 25px; padding: 6px 15px; }
        .chat-input form button { background: none; border: none; font-size: 1.1rem; cursor: pointer; color: #6b7280; padding: 5px; }
        .chat-input form button:hover { color: #10b981; }
        .chat-input form input[type="text"] { flex: 1; border: none; background: none; padding: 10px; font-size: 0.95rem; outline: none; }
        .chat-send-btn { background: #10b981 !important; color: white !important; padding: 8px 20px !important; border-radius: 20px !important; font-weight: 600; }
        .chat-empty { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; color: #9ca3af; }
        .chat-empty span { font-size: 3.5rem; margin-bottom: 10px; }
        @media (max-width: 768px) { .chat-wrapper { flex-direction: column; margin: 0; border-radius: 0; min-height: 100vh; } .chat-sidebar { width: 100%; max-height: 180px; } .chat-contacts { display: flex; overflow-x: auto; } .chat-contact { min-width: 140px; flex-direction: column; text-align: center; padding: 10px; } .chat-contact-details { display: none; } }
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
    <div style="height: 50px;"></div>
    <main>
        <div class="chat-wrapper fade-in">
            <aside class="chat-sidebar">
                <div class="chat-sidebar-header">
                    <h2>Messages</h2>
                    <div class="chat-search">
                        <span>🔍</span>
                        <input type="text" id="searchContacts" placeholder="Search conversations..." onkeyup="filterContacts()">
                    </div>
                </div>
                <div class="chat-contacts" id="contactsList">
                    <?php foreach ($workers as $worker): ?>
                    <a href="messaging.php?worker=<?php echo $worker['id']; ?>" class="chat-contact <?php echo $selected_worker_id == $worker['id'] ? 'active' : ''; ?>" data-name="<?php echo strtolower($worker['name']); ?>" data-specialty="<?php echo strtolower($worker['specialty']); ?>">
                        <div class="chat-contact-img">
                            <img src="<?php echo $worker['image_url']; ?>" alt="<?php echo $worker['name']; ?>">
                            <?php if ($worker['status'] == 'Available'): ?>
                            <div class="online-status"></div>
                            <?php endif; ?>
                        </div>
                        <div class="chat-contact-details">
                            <h4><?php echo $worker['name']; ?></h4>
                            <p><?php echo $worker['specialty']; ?></p>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </aside>
            <section class="chat-main">
                <?php if ($selected_worker): ?>
                <div class="chat-header">
                    <div class="chat-header-info">
                        <img src="<?php echo $selected_worker['image_url']; ?>" class="chat-header-img">
                        <div class="chat-header-text">
                            <h3><?php echo $selected_worker['name']; ?></h3>
                            <p><?php echo $selected_worker['status']; ?></p>
                        </div>
                    </div>
                    <div class="chat-header-actions">
                        <button title="Voice Call">📞</button>
                        <button title="Video Call">📹</button>
                        <button title="More">⋯</button>
                    </div>
                </div>
                <div class="chat-messages" id="chatMessages">
                    <?php if (count($chat_messages) > 0): ?>
                        <?php foreach ($chat_messages as $msg): ?>
                        <div class="chat-msg <?php echo $msg['sender_type'] == 'patient' ? 'sent' : ''; ?>">
                            <img src="<?php echo $msg['sender_type'] == 'patient' ? $user_avatar : "https://ui-avatars.com/api/?name=Health+Worker&background=3b82f6&color=fff"; ?>" class="chat-msg-img">
                            <div class="chat-msg-content">
                                <?php echo htmlspecialchars($msg['message']); ?>
                                <div class="chat-msg-time"><?php echo date('H:i', strtotime($msg['created_at'])); ?></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <div class="chat-input">
                    <form method="POST" action="messaging.php?worker=<?php echo $selected_worker_id; ?>">
                        <input type="hidden" name="worker_id" value="<?php echo $selected_worker_id; ?>">
                        <button type="button" onclick="document.getElementById('fileInput').click()">📎</button>
                        <input type="file" id="fileInput" style="display:none">
                        <button type="button">📷</button>
                        <input type="text" name="message" placeholder="Type your message..." required>
                        <button type="submit" class="chat-send-btn">Send</button>
                    </form>
                </div>
                <?php else: ?>
                <div class="chat-empty">
                    <span>💬</span>
                    <p>Select a conversation</p>
                </div>
                <?php endif; ?>
            </section>
        </div>
    </main>
    <script>
        function filterContacts() {
            const search = document.getElementById('searchContacts').value.toLowerCase();
            const contacts = document.querySelectorAll('.chat-contact');
            contacts.forEach(contact => {
                const name = contact.dataset.name;
                const specialty = contact.dataset.specialty;
                if (name.includes(search) || specialty.includes(search)) {
                    contact.style.display = 'flex';
                } else {
                    contact.style.display = 'none';
                }
            });
        }
        const chatMsgs = document.getElementById('chatMessages');
        if (chatMsgs) chatMsgs.scrollTop = chatMsgs.scrollHeight;
        window.onload = function() {
            const savedAvatar = localStorage.getItem('userAvatar');
            if (savedAvatar) document.getElementById('nav-avatar').src = savedAvatar;
        };
    </script>
</body>
</html>

