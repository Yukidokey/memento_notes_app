<?php

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../auth.php';

class TeacherSessionController
{
    public function handle(string $path): void
    {
        if ($path === '/teacher/sessions' || $path === '/teacher/sessions/') {
            $this->index();
        } elseif ($path === '/teacher/sessions/start' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->start();
        } elseif (str_starts_with($path, '/teacher/sessions/show')) {
            $this->show();
        } else {
            http_response_code(404);
            echo 'Not Found';
        }
    }

    private function index(): void
    {
        $db = get_db();
        $user = current_user();

        // Fetch latest sessions by this teacher
        $stmt = $db->prepare("
            SELECT s.*, c.code AS course_code, c.title AS course_title, sec.name AS section_name
            FROM sessions s
            JOIN class_offerings co ON s.class_offering_id = co.id
            JOIN courses c ON co.course_id = c.id
            JOIN sections sec ON co.section_id = sec.id
            WHERE co.teacher_id = :tid
            ORDER BY s.session_date DESC, s.start_time DESC
            LIMIT 20
        ");
        $stmt->execute(['tid' => $user['id']]);
        $sessions = $stmt->fetchAll();

        view('teacher/sessions/index', [
            'user' => $user,
            'sessions' => $sessions,
        ]);
    }

    private function start(): void
    {
        $db = get_db();
        $user = current_user();

        $classOfferingId = isset($_POST['class_offering_id']) ? (int)$_POST['class_offering_id'] : 0;

        if ($classOfferingId <= 0) {
            // For simplicity, try to find any class offering for this teacher
            $stmt = $db->prepare("SELECT id FROM class_offerings WHERE teacher_id = :tid LIMIT 1");
            $stmt->execute(['tid' => $user['id']]);
            $row = $stmt->fetch();
            if (!$row) {
                $error = 'No class offering is assigned to you yet. Please ask the admin to create one.';
                $_SESSION['flash_error'] = $error;
                redirect(BASE_URL . '/teacher/sessions');
            }
            $classOfferingId = (int)$row['id'];
        }

        $qrToken = bin2hex(random_bytes(16));
        $now = new DateTimeImmutable();
        $expires = $now->modify('+10 minutes');

        $stmt = $db->prepare("
            INSERT INTO sessions (class_offering_id, session_date, start_time, qr_token, qr_expires_at, status, created_by)
            VALUES (:coid, :sdate, :stime, :token, :expires, 'ongoing', :uid)
        ");
        $stmt->execute([
            'coid' => $classOfferingId,
            'sdate' => $now->format('Y-m-d'),
            'stime' => $now->format('H:i:s'),
            'token' => $qrToken,
            'expires' => $expires->format('Y-m-d H:i:s'),
            'uid' => $user['id'],
        ]);

        $sessionId = (int)$db->lastInsertId();
        redirect(BASE_URL . '/teacher/sessions/show?id=' . $sessionId);
    }

    private function show(): void
    {
        $db = get_db();
        $user = current_user();
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        $stmt = $db->prepare("
            SELECT s.*, c.code AS course_code, c.title AS course_title, sec.name AS section_name
            FROM sessions s
            JOIN class_offerings co ON s.class_offering_id = co.id
            JOIN courses c ON co.course_id = c.id
            JOIN sections sec ON co.section_id = sec.id
            WHERE s.id = :id AND co.teacher_id = :tid
        ");
        $stmt->execute(['id' => $id, 'tid' => $user['id']]);
        $session = $stmt->fetch();

        if (!$session) {
            http_response_code(404);
            echo 'Session not found';
            return;
        }

        // URL encoded into the QR so student scanners open the check-in page directly.
        // Build a full absolute URL using the configured app host so it works reliably on mobile.
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = defined('APP_HOST') && APP_HOST ? APP_HOST : ($_SERVER['HTTP_HOST'] ?? 'localhost');
        $basePath = rtrim(parse_url(BASE_URL, PHP_URL_PATH), '/');
        $qrUrl = $scheme . '://' . $host . $basePath . '/student/attendance/check-in?token=' . urlencode($session['qr_token']);

        // Attendance summary
        $stmt = $db->prepare("
            SELECT COUNT(*) AS total,
                   SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) AS present_count
            FROM attendance_records
            WHERE session_id = :sid
        ");
        $stmt->execute(['sid' => $session['id']]);
        $summary = $stmt->fetch() ?: ['total' => 0, 'present_count' => 0];

        view('teacher/sessions/show', [
            'user' => $user,
            'session' => $session,
            'qrUrl' => $qrUrl,
            'summary' => $summary,
        ]);
    }
}

