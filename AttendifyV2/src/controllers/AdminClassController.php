<?php

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../auth.php';

class AdminClassController
{
    public function handle(string $path): void
    {
        // Only /admin/classes supported for now
        if ($path === '/admin/classes' || $path === '/admin/classes/') {
            $this->index();
            return;
        }

        http_response_code(404);
        echo 'Not Found';
    }

    private function index(): void
    {
        require_login();
        require_role('admin');

        $db = get_db();

        // Handle basic create actions
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';

            if ($action === 'create_course') {
                $code = trim($_POST['code'] ?? '');
                $title = trim($_POST['title'] ?? '');
                if ($code !== '' && $title !== '') {
                    $stmt = $db->prepare('INSERT INTO courses (code, title) VALUES (:code, :title)');
                    $stmt->execute(['code' => $code, 'title' => $title]);
                }
            } elseif ($action === 'create_section') {
                $name = trim($_POST['name'] ?? '');
                $course = trim($_POST['course'] ?? '');
                $year = trim($_POST['year_level'] ?? '');
                if ($name !== '') {
                    $stmt = $db->prepare('INSERT INTO sections (name, course, year_level) VALUES (:name, :course, :year)');
                    $stmt->execute(['name' => $name, 'course' => $course, 'year' => $year]);
                }
            } elseif ($action === 'create_offering') {
                $courseId = (int)($_POST['course_id'] ?? 0);
                $sectionId = (int)($_POST['section_id'] ?? 0);
                $teacherId = (int)($_POST['teacher_id'] ?? 0);
                $term = trim($_POST['term'] ?? '');
                $schedule = trim($_POST['schedule_pattern'] ?? '');

                if ($courseId > 0 && $sectionId > 0 && $teacherId > 0) {
                    $stmt = $db->prepare('
                        INSERT INTO class_offerings (course_id, section_id, teacher_id, term, schedule_pattern)
                        VALUES (:course_id, :section_id, :teacher_id, :term, :schedule)
                    ');
                    $stmt->execute([
                        'course_id' => $courseId,
                        'section_id' => $sectionId,
                        'teacher_id' => $teacherId,
                        'term' => $term,
                        'schedule' => $schedule,
                    ]);
                }
            }

            redirect(BASE_URL . '/admin/classes');
        }

        // Load data for listing / selects
        $courses = $db->query('SELECT id, code, title FROM courses ORDER BY code')->fetchAll();
        $sections = $db->query('SELECT id, name, course, year_level FROM sections ORDER BY name')->fetchAll();

        $stmt = $db->query("SELECT id, name, email FROM users WHERE role = 'teacher' ORDER BY name");
        $teachers = $stmt->fetchAll();

        $sql = "
            SELECT co.id, c.code AS course_code, c.title AS course_title,
                   s.name AS section_name, u.name AS teacher_name,
                   co.term, co.schedule_pattern
            FROM class_offerings co
            JOIN courses c ON co.course_id = c.id
            JOIN sections s ON co.section_id = s.id
            JOIN users u ON co.teacher_id = u.id
            ORDER BY c.code, s.name
        ";
        $classOfferings = $db->query($sql)->fetchAll();

        $user = current_user();
        view('admin/classes/index', compact('user', 'courses', 'sections', 'teachers', 'classOfferings'));
    }
}

