<div class="card">
    <h2 class="card-title">My Attendance</h2>
    <p class="muted">Scan your teacher's QR code in class to record your attendance. Each successful scan will appear in this list.</p>

    <?php if (empty($records)): ?>
        <p class="muted">No attendance records yet.</p>
    <?php else: ?>
        <table class="table">
            <thead>
            <tr>
                <th>Date</th>
                <th>Time</th>
                <th>Class</th>
                <th>Section</th>
                <th>Status</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($records as $r): ?>
                <tr>
                    <td><?= e($r['session_date']) ?></td>
                    <td><?= e(substr($r['start_time'], 0, 5)) ?></td>
                    <td><?= e($r['course_code']) ?></td>
                    <td><?= e($r['section_name']) ?></td>
                    <td><?= e(ucfirst($r['status'])) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

