<div class="card">
    <h2 class="card-title">User Management</h2>
    <p class="muted">
        This is a simple admin view showing all registered users. You can extend this to add, edit, or deactivate accounts.
    </p>

    <?php if (empty($users)): ?>
        <p class="muted">No users found.</p>
    <?php else: ?>
        <table class="table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th>Created</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= (int)$u['id'] ?></td>
                    <td><?= e($u['name']) ?></td>
                    <td><?= e($u['email']) ?></td>
                    <td><?= e(ucfirst($u['role'])) ?></td>
                    <td><?= e(ucfirst($u['status'])) ?></td>
                    <td><?= e($u['created_at']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

