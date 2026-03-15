<?php

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../auth.php';

class DashboardController
{
    public function index(): void
    {
        $db = get_db();
        $user = current_user();

        // Simple stats
        $totalStudents = $db->query("SELECT COUNT(*) AS c FROM users WHERE role = 'student'")->fetch()['c'] ?? 0;
        $totalTeachers = $db->query("SELECT COUNT(*) AS c FROM users WHERE role = 'teacher'")->fetch()['c'] ?? 0;
        $totalClasses = $db->query('SELECT COUNT(*) AS c FROM class_offerings')->fetch()['c'] ?? 0;
        $todaySessions = $db->prepare('SELECT COUNT(*) AS c FROM sessions WHERE session_date = CURDATE()');
        $todaySessions->execute();
        $todaySessionsCount = $todaySessions->fetch()['c'] ?? 0;

        view('dashboard/index', [
            'user' => $user,
            'totalStudents' => $totalStudents,
            'totalTeachers' => $totalTeachers,
            'totalClasses' => $totalClasses,
            'todaySessions' => $todaySessionsCount,
        ]);
    }
}

