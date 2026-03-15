<div class="card">
    <h2 class="card-title">QR Check-in</h2>
    <?php if ($status === 'success'): ?>
        <div class="alert" style="background:rgba(0,255,163,0.08);border:1px solid rgba(0,255,163,0.4);color:#a3ffde;">
            <?= e($message) ?>
        </div>
        <p class="muted">You can review your attendance under "My Attendance".</p>
    <?php else: ?>
        <div class="alert alert-error">
            <?= e($message) ?>
        </div>
        <p class="muted">If you think this is a mistake, please contact your teacher.</p>
    <?php endif; ?>
    <a href="<?= BASE_URL ?>/student/attendance" class="btn btn-primary" style="margin-top:0.8rem;display:inline-block;">Back to My Attendance</a>
</div>

