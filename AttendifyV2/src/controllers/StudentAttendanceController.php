<?php

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../auth.php';

class StudentAttendanceController
{
    public function handle(string $path): void
    {
        if ($path === '/student/attendance' || $path === '/student/attendance/') {
            $this->index();
        } elseif (str_starts_with($path, '/student/attendance/check-in')) {
            $this->checkIn();
        } else {
            http_response_code(404);
            echo 'Not Found';
        }
    }

    private function index(): void
    {
        $db = get_db();
        $user = current_user();

        $stmt = $db->prepare("
            SELECT ar.*, s.session_date, s.start_time,
                   c.code AS course_code, c.title AS course_title, sec.name AS section_name
            FROM attendance_records ar
            JOIN sessions s ON ar.session_id = s.id
            JOIN class_offerings co ON s.class_offering_id = co.id
            JOIN courses c ON co.course_id = c.id
            JOIN sections sec ON co.section_id = sec.id
            WHERE ar.student_id = :sid
            ORDER BY s.session_date DESC, s.start_time DESC
            LIMIT 50
        ");
        $stmt->execute(['sid' => $user['id']]);
        $records = $stmt->fetchAll();

        view('student/attendance/index', [
            'user' => $user,
            'records' => $records,
        ]);
    }

    private function checkIn(): void
    {
        $db = get_db();
        $user = current_user();

        $token = $_GET['token'] ?? '';
        if ($token === '') {
            $message = 'Invalid QR token.';
            $status = 'error';
            view('student/attendance/checkin_result', compact('message', 'status', 'user'));
            return;
        }

        // Find active session matching this token
        $stmt = $db->prepare("
            SELECT * FROM sessions
            WHERE qr_token = :token
              AND qr_expires_at >= NOW()
              AND status IN ('scheduled','ongoing')
            LIMIT 1
        ");
        $stmt->execute(['token' => $token]);
        $session = $stmt->fetch();

        if (!$session) {
            $message = 'This QR code is expired or invalid.';
            $status = 'error';
            view('student/attendance/checkin_result', compact('message', 'status', 'user'));
            return;
        }

        // NOTE: Enrollment verification is skipped here to keep the demo simple.
        // In a stricter version, you can re-enable the check against the enrollments table.

        // Record attendance (upsert)
        $stmt = $db->prepare("
            INSERT INTO attendance_records (session_id, student_id, status, scan_timestamp, source)
            VALUES (:sid, :uid, 'present', NOW(), 'qr')
            ON DUPLICATE KEY UPDATE
                status = VALUES(status),
                scan_timestamp = VALUES(scan_timestamp),
                source = VALUES(source)
        ");
        $stmt->execute([
            'sid' => $session['id'],
            'uid' => $user['id'],
        ]);

        // Redirect straight to My Attendance so the student immediately sees the updated record
        redirect(BASE_URL . '/student/attendance');
    }
}

