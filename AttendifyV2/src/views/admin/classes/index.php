<div class="grid grid-2">
    <div class="card">
        <h2 class="card-title">Courses</h2>
        <p class="muted">Create subjects that can be attached to sections and teachers.</p>

        <form method="post" action="<?= BASE_URL ?>/admin/classes" style="margin-top:0.6rem;">
            <input type="hidden" name="action" value="create_course">
            <div class="form-group">
                <label for="course_code">Course code</label>
                <input type="text" id="course_code" name="code" required placeholder="e.g. IT101">
            </div>
            <div class="form-group">
                <label for="course_title">Title</label>
                <input type="text" id="course_title" name="title" required placeholder="e.g. Introduction to IT">
            </div>
            <button type="submit" class="btn btn-primary">Add course</button>
        </form>

        <?php if (!empty($courses)): ?>
            <table class="table" style="margin-top:1rem;">
                <thead>
                <tr>
                    <th>Code</th>
                    <th>Title</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($courses as $c): ?>
                    <tr>
                        <td><?= e($c['code']) ?></td>
                        <td><?= e($c['title']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <div class="card">
        <h2 class="card-title">Sections</h2>
        <p class="muted">Define sections or blocks where students belong.</p>

        <form method="post" action="<?= BASE_URL ?>/admin/classes" style="margin-top:0.6rem;">
            <input type="hidden" name="action" value="create_section">
            <div class="form-group">
                <label for="section_name">Section name</label>
                <input type="text" id="section_name" name="name" required placeholder="e.g. BSIT-1A">
            </div>
            <div class="form-group">
                <label for="section_course">Course (optional text)</label>
                <input type="text" id="section_course" name="course" placeholder="e.g. BSIT">
            </div>
            <div class="form-group">
                <label for="section_year">Year level (optional)</label>
                <input type="text" id="section_year" name="year_level" placeholder="e.g. 1st Year">
            </div>
            <button type="submit" class="btn btn-primary">Add section</button>
        </form>

        <?php if (!empty($sections)): ?>
            <table class="table" style="margin-top:1rem;">
                <thead>
                <tr>
                    <th>Name</th>
                    <th>Course</th>
                    <th>Year</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($sections as $s): ?>
                    <tr>
                        <td><?= e($s['name']) ?></td>
                        <td><?= e($s['course']) ?></td>
                        <td><?= e($s['year_level']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<div class="card" style="margin-top:1.2rem;">
    <h2 class="card-title">Class offerings (link course + section + teacher)</h2>
    <p class="muted">
        Create a class offering so teachers can start QR sessions. Each offering ties one course, one section, and one teacher.
    </p>

    <form method="post" action="<?= BASE_URL ?>/admin/classes" style="margin-top:0.6rem;">
        <input type="hidden" name="action" value="create_offering">
        <div class="form-group">
            <label for="off_course">Course</label>
            <select id="off_course" name="course_id" required>
                <option value="">Select course</option>
                <?php foreach ($courses as $c): ?>
                    <option value="<?= (int)$c['id'] ?>"><?= e($c['code'] . ' - ' . $c['title']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="off_section">Section</label>
            <select id="off_section" name="section_id" required>
                <option value="">Select section</option>
                <?php foreach ($sections as $s): ?>
                    <option value="<?= (int)$s['id'] ?>"><?= e($s['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="off_teacher">Teacher</label>
            <select id="off_teacher" name="teacher_id" required>
                <option value="">Select teacher</option>
                <?php foreach ($teachers as $t): ?>
                    <option value="<?= (int)$t['id'] ?>"><?= e($t['name'] . ' (' . $t['email'] . ')') ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="off_term">Term (optional)</label>
            <input type="text" id="off_term" name="term" placeholder="e.g. 1st Sem 2025–2026">
        </div>
        <div class="form-group">
            <label for="off_schedule">Schedule pattern (optional)</label>
            <input type="text" id="off_schedule" name="schedule_pattern" placeholder="e.g. MWF 8:00–9:00 AM">
        </div>
        <button type="submit" class="btn btn-primary">Create class offering</button>
    </form>

    <?php if (!empty($classOfferings)): ?>
        <table class="table" style="margin-top:1rem;">
            <thead>
            <tr>
                <th>Course</th>
                <th>Section</th>
                <th>Teacher</th>
                <th>Term</th>
                <th>Schedule</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($classOfferings as $co): ?>
                <tr>
                    <td><?= e($co['course_code']) ?></td>
                    <td><?= e($co['section_name']) ?></td>
                    <td><?= e($co['teacher_name']) ?></td>
                    <td><?= e($co['term']) ?></td>
                    <td><?= e($co['schedule_pattern']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="muted" style="margin-top:0.8rem;">
            No class offerings yet. Once you create one for a teacher, they will be able to start QR sessions.
        </p>
    <?php endif; ?>
</div>

