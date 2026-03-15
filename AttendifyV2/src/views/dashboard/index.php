<div class="grid grid-2">
    <?php if ($user['role'] === 'teacher'): ?>
        <div class="card kpi-card">
            <h2 class="card-title">Today at a glance</h2>
            <div class="kpi-grid">
                <div class="kpi">
                    <div class="kpi-label">Your classes</div>
                    <div class="kpi-value"><?= (int)$totalClasses ?></div>
                </div>
                <div class="kpi">
                    <div class="kpi-label">Sessions today</div>
                    <div class="kpi-value"><?= (int)$todaySessions ?></div>
                </div>
            </div>
        </div>
        <div class="card">
            <h2 class="card-title">Welcome, <?= e($user['name']) ?></h2>
            <p class="muted">Teacher dashboard</p>
            <p>
                Start a QR session for your current class and project the code so students can scan and record their attendance.
            </p>
            <a href="<?= BASE_URL ?>/teacher/sessions" class="btn btn-primary" style="margin-top:0.6rem;">Go to My Sessions</a>
        </div>
    <?php elseif ($user['role'] === 'student'): ?>
        <div class="card kpi-card">
            <h2 class="card-title">Overview</h2>
            <div class="kpi-grid">
                <div class="kpi">
                    <div class="kpi-label">Teachers</div>
                    <div class="kpi-value"><?= (int)$totalTeachers ?></div>
                </div>
                <div class="kpi">
                    <div class="kpi-label">Today Sessions</div>
                    <div class="kpi-value"><?= (int)$todaySessions ?></div>
                </div>
            </div>
        </div>
        <div class="card">
            <h2 class="card-title">Welcome, <?= e($user['name']) ?></h2>
            <p class="muted">Student dashboard</p>
            <p>
                Scan your teacher's QR code at the start of class to be marked present, then review your attendance history anytime.
            </p>
            <a href="<?= BASE_URL ?>/student/attendance" class="btn btn-primary" style="margin-top:0.6rem;">View My Attendance</a>
        </div>
    <?php else: ?>
        <div class="card kpi-card">
            <h2 class="card-title">Overview</h2>
            <div class="kpi-grid">
                <div class="kpi">
                    <div class="kpi-label">Students</div>
                    <div class="kpi-value"><?= (int)$totalStudents ?></div>
                </div>
                <div class="kpi">
                    <div class="kpi-label">Teachers</div>
                    <div class="kpi-value"><?= (int)$totalTeachers ?></div>
                </div>
                <div class="kpi">
                    <div class="kpi-label">Classes</div>
                    <div class="kpi-value"><?= (int)$totalClasses ?></div>
                </div>
                <div class="kpi">
                    <div class="kpi-label">Today Sessions</div>
                    <div class="kpi-value"><?= (int)$todaySessions ?></div>
                </div>
            </div>
        </div>
        <div class="card">
            <h2 class="card-title">Welcome, <?= e($user['name']) ?></h2>
            <p class="muted">Admin dashboard</p>
            <p>
                Monitor attendance across classes, manage users, and configure the cloud-based attendance system.
            </p>
        </div>
    <?php endif; ?>
</div>

