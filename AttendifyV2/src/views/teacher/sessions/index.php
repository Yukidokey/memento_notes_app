<?php $flashError = $_SESSION['flash_error'] ?? null; unset($_SESSION['flash_error']); ?>
<div class="card">
    <div class="card-header" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0.7rem;">
        <div>
            <h2 class="card-title">My Recent Sessions</h2>
            <p class="muted">Generate a QR-powered session and project it in class.</p>
        </div>
        <form method="post" action="<?= BASE_URL ?>/teacher/sessions/start">
            <button type="submit" class="btn btn-primary">Start New Session</button>
        </form>
    </div>

    <?php if ($flashError): ?>
        <div class="alert alert-error"><?= e($flashError) ?></div>
    <?php endif; ?>

    <?php if (empty($sessions)): ?>
        <p class="muted">No sessions found. Click "Start New Session" to create one once the admin has assigned you to a class.</p>
    <?php else: ?>
        <table class="table">
            <thead>
            <tr>
                <th>Date</th>
                <th>Time</th>
                <th>Class</th>
                <th>Status</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($sessions as $s): ?>
                <tr>
                    <td><?= e($s['session_date']) ?></td>
                    <td><?= e(substr($s['start_time'], 0, 5)) ?></td>
                    <td><?= e($s['course_code'] . ' - ' . $s['section_name']) ?></td>
                    <td><?= e(ucfirst($s['status'])) ?></td>
                    <td>
                        <a href="<?= BASE_URL ?>/teacher/sessions/show?id=<?= (int)$s['id'] ?>" class="link">View</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

