<?php
$endTime = $session['end_time'] ? substr($session['end_time'], 0, 5) : '—';
?>
<div class="grid grid-2">
    <div class="card">
        <h2 class="card-title">Live Session QR</h2>
        <p class="muted">
            Project this QR on the screen. Students will scan it and be redirected to the check-in page.
        </p>
        <div style="display:flex;justify-content:center;margin:1rem 0;">
            <div style="background:#fff;border-radius:1rem;padding:0.8rem;">
                <img
                    src="https://api.qrserver.com/v1/create-qr-code/?size=260x260&data=<?= urlencode($qrUrl) ?>"
                    alt="QR Code for attendance"
                    style="display:block;width:260px;height:260px;"
                >
            </div>
        </div>
        <p class="muted" style="font-size:0.8rem;">
            Expires at: <?= e($session['qr_expires_at']) ?>
        </p>
        <p class="muted" style="font-size:0.8rem;">
            QR content: <code style="word-break:break-all;"><?= e($qrUrl) ?></code>
        </p>
    </div>

    <div class="card">
        <h2 class="card-title">Session Details</h2>
        <p><strong>Class:</strong> <?= e($session['course_code'] . ' - ' . $session['course_title']) ?></p>
        <p><strong>Section:</strong> <?= e($session['section_name']) ?></p>
        <p><strong>Date:</strong> <?= e($session['session_date']) ?></p>
        <p><strong>Time:</strong> <?= e(substr($session['start_time'], 0, 5)) ?> - <?= e($endTime) ?></p>

        <h3 class="card-title" style="margin-top:1rem;font-size:1rem;">Attendance Summary</h3>
        <p>
            <strong>Present:</strong> <?= (int)$summary['present_count'] ?> /
            <strong>Scans:</strong> <?= (int)$summary['total'] ?>
        </p>
        <p class="muted" style="font-size:0.85rem;">
            Detailed per-student view can be implemented as an extension (e.g. show enrolled list with statuses).
        </p>
    </div>
</div>

