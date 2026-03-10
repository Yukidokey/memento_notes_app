<?php
session_start();
include "db.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user data
$sql = "SELECT * FROM users WHERE id = $user_id";
$result = $conn->query($sql);
$user = $result->fetch_assoc();

$user_avatar = $user['avatar'] ?: "https://ui-avatars.com/api/?name=" . urlencode($user['fullname']) . "&background=10b981&color=fff";

// Get health workers for contact list
$workers = [];
$sql = "SELECT * FROM health_workers ORDER BY status DESC, name ASC";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $workers[] = $row;
    }
}

// Get messages for the user
$messages = [];
$selected_worker_id = isset($_GET['worker']) ? $_GET['worker'] : (count($workers) > 0 ? $workers[0]['id'] : 0);

if ($selected_worker_id > 0) {
    $sql = "SELECT * FROM messages WHERE user_id = $user_id AND worker_id = $selected_worker_id ORDER BY created_at ASC";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $messages[] = $row;
        }
    }
}

// Handle sending message
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['message'])) {
    $worker_id = $_POST['worker_id'];
    $message = $_POST['message'];
    
    $sql = "INSERT INTO messages (user_id, worker_id, sender_type, message) VALUES ($user_id, $worker_id, 'user', '$message')";
    if ($conn->query($sql) === TRUE) {
        // Refresh page to show new message
        header("Location: messaging.php?worker=$worker_id");
        exit();
    }
}

$selected_worker = null;
foreach ($workers as $w) {
    if ($w['id'] == $selected_worker_id) {
        $selected_worker = $w;
        break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Malasakit Healthcare | Messages</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        .messaging-wrapper {
            display: grid;
            grid-template-columns: 350px 1fr;
            height: 80vh;
            background: white;
            margin-top: -50px;
            border-radius: 30px;
            overflow: hidden;
            box-shadow: 0 20px 50px rgba(0,0,0,0.1);
            position: relative;
            z-index: 10;
        }

        /* Sidebar / Contact List */
        .chat-sidebar {
            border-right: 1px solid #f1f5f9;
            display: flex;
            flex-direction: column;
            background: #f8fafc;
        }
        .sidebar-header { padding: 25px; background: white; border-bottom: 1px solid #f1f5f9; }
        .contact-list { overflow-y: auto; flex: 1; }
        .contact-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 20px;
            cursor: pointer;
            transition: 0.3s;
            border-bottom: 1px solid #f1f5f9;
            text-decoration: none;
            color: inherit;
        }
        .contact-item:hover, .contact-item.active { background: white; border-left: 4px solid var(--mint); }
        .contact-avatar { width: 50px; height: 50px; border-radius: 50%; background: #e2e8f0; object-fit: cover; }
        .contact-info h4 { font-size: 0.95rem; color: var(--text-dark); margin-bottom: 4px; }
        .contact-info p { font-size: 0.8rem; color: var(--text-light); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

        /* Chat Window */
        .chat-window { display: flex; flex-direction: column; background: white; }
        .chat-header {
            padding: 20px 30px;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .chat-body {
            flex: 1;
            padding: 30px;
            overflow-y: auto;
            background-image: radial-gradient(#e2e8f0 1px, transparent 1px);
            background-size: 20px 20px;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        /* Bubbles */
        .message { max-width: 70%; padding: 12px 18px; border-radius: 20px; font-size: 0.95rem; line-height: 1.5; }
        .msg-received { background: #f1f5f9; align-self: flex-start; border-bottom-left-radius: 4px; }
        .msg-sent { background: var(--emerald); color: white; align-self: flex-end; border-bottom-right-radius: 4px; }

        /* Input Area */
        .chat-input-area { padding: 20px 30px; border-top: 1px solid #f1f5f9; display: flex; gap: 15px; }
        .chat-input-area input {
            flex: 1;
            padding: 15px;
            border-radius: 15px;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            outline: none;
            font-family: inherit;
        }

        @media (max-width: 850px) {
            .messaging-wrapper { grid-template-columns: 1fr; }
            .chat-sidebar { display: none; } /* In a real app, you'd use a toggle */
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
            <h1>Consultation <span class="highlight">Chat</span></h1>
        </div>
    </header>

    <main class="container">
        <div class="messaging-wrapper fade-in">
            <aside class="chat-sidebar">
                <div class="sidebar-header">
                    <input type="text" placeholder="Search contacts..." style="width:100%; padding:10px; border-radius:10px; border:1px solid #e2e8f0;">
                </div>
                <div class="contact-list">
                    <?php foreach ($workers as $worker): ?>
                    <a href="messaging.php?worker=<?php echo $worker['id']; ?>" class="contact-item <?php echo $selected_worker_id == $worker['id'] ? 'active' : ''; ?>">
                        <img src="<?php echo $worker['image_url']; ?>" class="contact-avatar">
                        <div class="contact-info">
                            <h4><?php echo $worker['name']; ?></h4>
                            <p><?php echo $worker['specialty']; ?></p>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </aside>

            <section class="chat-window">
                <?php if ($selected_worker): ?>
                <div class="chat-header">
                    <div style="display:flex; align-items:center; gap:12px;">
                        <img src="<?php echo $selected_worker['image_url']; ?>" style="width:40px; height:40px; border-radius:50%;">
                        <div>
                            <h4 style="margin:0;"><?php echo $selected_worker['name']; ?></h4>
                            <small style="color:#10b981;">● <?php echo $selected_worker['status']; ?></small>
                        </div>
                    </div>
                    <button class="btn-outline" style="padding: 5px 15px; font-size: 0.8rem;" onclick="location.href='appointments.php'">Book Visit</button>
                </div>

                <div class="chat-body" id="chatBody">
                    <?php if (count($messages) > 0): ?>
                        <?php foreach ($messages as $msg): ?>
                        <div class="message <?php echo $msg['sender_type'] == 'user' ? 'msg-sent' : 'msg-received'; ?>">
                            <?php echo $msg['message']; ?>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <!-- Default messages -->
                        <div class="message msg-received">
                            Magandang umaga! I saw your request for a consultation. How are you feeling today?
                        </div>
                        <div class="message msg-sent">
                            Good morning po, Ma'am Maria. I've had a slight cough for 2 days now.
                        </div>
                        <div class="message msg-received">
                            Do you have a fever as well? I will be in Purok 7 tomorrow morning, I can drop by your house.
                        </div>
                    <?php endif; ?>
                </div>

                <form class="chat-input-area" method="POST" action="">
                    <input type="hidden" name="worker_id" value="<?php echo $selected_worker_id; ?>">
                    <button type="button" style="background:none; border:none; font-size:1.5rem; cursor:pointer;">📎</button>
                    <input type="text" placeholder="Type your message here..." name="message" id="chatInput" required>
                    <button type="submit" class="btn-primary" style="padding: 10px 25px; border-radius:12px;">Send</button>
                </form>
                <?php else: ?>
                <div class="chat-body" style="justify-content: center; align-items: center;">
                    <p style="color: #64748b;">Select a health worker to start chatting.</p>
                </div>
                <?php endif; ?>
            </section>
        </div>
    </main>

    <script>
        // Scroll to bottom of chat
        const chatBody = document.getElementById('chatBody');
        if (chatBody) {
            chatBody.scrollTop = chatBody.scrollHeight;
        }

        // Sync Navbar Avatar
        window.onload = function() {
            const savedAvatar = localStorage.getItem('userAvatar');
            if (savedAvatar) document.getElementById('nav-avatar').src = savedAvatar;
        };
    </script>
</body>
</html>

