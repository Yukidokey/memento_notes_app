<?php
session_start();
include "db.php";

// Get user data if logged in
$user_name = "";
$user_avatar = "https://ui-avatars.com/api/?name=User&background=10b981&color=fff";

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $sql = "SELECT fullname, avatar FROM users WHERE id = $user_id";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $user_name = $row['fullname'];
        if ($row['avatar']) {
            $user_avatar = $row['avatar'];
        } else {
            $user_avatar = "https://ui-avatars.com/api/?name=" . urlencode($user_name) . "&background=10b981&color=fff";
        }
    }
}

// Fetch health workers from database
$workers = [];
$sql = "SELECT * FROM health_workers ORDER BY status DESC, name ASC";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $workers[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Malasakit Healthcare | Doctors & Health Workers</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        .doctors-container { padding: 60px 20px; max-width: 1100px; margin: 0 auto; }
        
        /* Search Section */
        .search-bar { 
            background: var(--white); padding: 20px; border-radius: 20px; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.05); display: flex; gap: 15px; 
            margin-top: -30px; position: relative; z-index: 10; margin-bottom: 40px;
        }
        .search-bar input { flex: 1; padding: 12px 20px; border-radius: 12px; border: 1px solid #e2e8f0; background: #f8fafc; outline: none; transition: 0.3s; }
        .search-bar input:focus { border-color: var(--mint); background: white; box-shadow: 0 0 0 4px var(--sage); }

        /* Grid System */
        .doctor-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; }
        
        /* Card Styling */
        .doctor-card { 
            background: var(--white); border-radius: 28px; overflow: hidden; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.04); border: 1px solid #f1f5f9; 
            transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); text-align: center; padding-bottom: 25px; 
        }
        .doctor-card:hover { transform: translateY(-12px); border-color: var(--mint); box-shadow: 0 20px 40px rgba(6, 78, 59, 0.1); }
        
        .doctor-header { background: var(--sage); padding: 40px 20px; position: relative; }
        .doctor-img { width: 110px; height: 110px; border-radius: 50%; border: 5px solid var(--white); object-fit: cover; margin-bottom: 10px; background: white; }
        
        /* Status Badges */
        .status-badge { position: absolute; top: 20px; right: 20px; padding: 5px 14px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
        .status-online { background: #dcfce7; color: #15803d; }
        .status-offline { background: #f1f5f9; color: #64748b; }

        .doctor-info h3 { color: var(--emerald); margin: 20px 0 5px; font-size: 1.25rem; }
        .doctor-info p { color: var(--text-light); font-size: 0.9rem; margin-bottom: 5px; }
        .specialty-tag { display: inline-block; background: #f0fdf4; color: var(--mint); padding: 4px 12px; border-radius: 8px; font-size: 0.75rem; font-weight: 600; margin-bottom: 20px; }

        /* Contact Section */
        .contact-btns { display: flex; justify-content: center; gap: 12px; padding: 0 25px; margin-top: 10px; }
        .btn-icon { width: 48px; height: 48px; border-radius: 14px; display: flex; align-items: center; justify-content: center; text-decoration: none; transition: 0.3s; background: #f8fafc; border: 1px solid #e2e8f0; font-size: 1.2rem; }
        .btn-icon:hover { background: var(--mint); color: white; border-color: var(--mint); transform: scale(1.1); }
        
        .btn-book { flex: 1; padding: 12px; border-radius: 14px; font-size: 0.85rem; }

        @media (max-width: 600px) {
            .search-bar { flex-direction: column; }
            .btn-icon { width: 40px; height: 40px; }
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
                <li><a href="doctors.php" class="active">Doctors</a></li>
                <li><a href="appointments.php">Appointments</a></li>
                <li><a href="dashboard.php">Dashboard</a></li>
            </ul>
            <div class="profile-dropdown">
                <?php if(isset($_SESSION['user'])): ?>
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
                <?php else: ?>
                <a href="login.php" class="btn-primary" style="padding: 8px 20px; border-radius: 20px;">Sign In</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <header class="hero" style="height: 35vh; min-height: 300px;">
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <h1 class="fade-in">Our Health <span class="highlight">Heroes</span></h1>
            <p class="fade-in">Dedicated professionals bringing quality care to your doorstep.</p>
        </div>
    </header>

    <main class="doctors-container">
        <div class="search-bar fade-in">
            <input type="text" id="searchInput" placeholder="Search by name, Barangay, or specialty (e.g. Midwife)...">
            <button class="btn-primary" style="border-radius: 12px;" onclick="searchWorkers()">Find Worker</button>
        </div>

        <div class="doctor-grid" id="doctorGrid">
            <?php if (count($workers) > 0): ?>
                <?php foreach ($workers as $index => $worker): ?>
                <div class="doctor-card slide-up" style="animation-delay: <?php echo $index * 0.1; ?>s">
                    <div class="doctor-header">
                        <span class="status-badge <?php echo $worker['status'] == 'Available' ? 'status-online' : 'status-offline'; ?>">
                            <?php echo $worker['status']; ?>
                        </span>
                        <img src="<?php echo $worker['image_url']; ?>" class="doctor-img" alt="<?php echo $worker['name']; ?>">
                    </div>
                    <div class="doctor-info">
                        <h3><?php echo $worker['name']; ?></h3>
                        <p><?php echo $worker['location']; ?></p>
                        <span class="specialty-tag"><?php echo $worker['specialty']; ?></span>
                        <div class="contact-btns">
                            <?php if (!empty($worker['phone'])): ?>
                            <a href="tel:<?php echo $worker['phone']; ?>" class="btn-icon" title="Call">📞</a>
                            <?php endif; ?>
                            <?php if (!empty($worker['email'])): ?>
                            <a href="mailto:<?php echo $worker['email']; ?>" class="btn-icon">✉️</a>
                            <?php endif; ?>
                            <button class="btn-primary btn-book" onclick="location.href='appointments.php?worker=<?php echo urlencode($worker['name']); ?>'">Book Appointment</button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="text-align: center; grid-column: 1/-1; color: #64748b;">No health workers found. Please run the database setup.</p>
            <?php endif; ?>
        </div>
    </main>

    <footer class="main-footer" style="margin-top: 80px;">
        <div class="container footer-grid">
            <div class="footer-col">
                <div class="logo-footer">🌿 Malasakit</div>
                <p>Ensuring compassion reaches every corner of our community.</p>
            </div>
        </div>
        <div class="footer-bottom">
            &copy; 2026 Malasakit Healthcare Project. Supporting our local health heroes.
        </div>
    </footer>

    <script>
        // Search functionality
        function searchWorkers() {
            const input = document.getElementById('searchInput');
            const filter = input.value.toLowerCase();
            const cards = document.querySelectorAll('.doctor-card');
            
            cards.forEach(card => {
                const text = card.textContent.toLowerCase();
                card.style.display = text.includes(filter) ? '' : 'none';
            });
        }

        // Also search on Enter key
        document.getElementById('searchInput').addEventListener('keypress', function (e) {
            if (e.key === 'Enter') searchWorkers();
        });

        window.onload = function() {
            const savedAvatar = localStorage.getItem('userAvatar');
            if (savedAvatar) {
                document.getElementById('nav-avatar').src = savedAvatar;
            }
        };
    </script>
</body>
</html>

