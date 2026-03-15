AttendifyV2 - Cloud-Based QR Attendance System
==============================================

This project is a simple, self-contained implementation of a cloud-based QR attendance
system inspired by the **Cloud-Based Attendance System** IMRAD capstone template.

It is designed to run on **XAMPP (PHP + MySQL)** on Windows and provides:

- A **web-based admin panel** to manage users, classes, and attendance reports.
- A **teacher interface** to start sessions and display QR codes.
- A **student interface** to scan QR codes and view attendance history.

The system emphasizes:

- QR-based, time-bound attendance.
- Offline-friendly concepts (can be extended using front-end storage).
- A professional, modern, responsive UI.

## Quick start

1. Copy this folder under your XAMPP `htdocs` (already in `AttendifyV2`).
2. Create a MySQL database named `attendify_v2` (or update `config.php`).
3. Import the SQL schema from `database/schema.sql`.
4. Start Apache and MySQL in XAMPP.
5. Visit `http://localhost/AttendifyV2/public/` in your browser.

## Stack

- PHP 8+ (plain PHP with simple routing)
- MySQL / MariaDB
- HTML5, CSS3, vanilla JS
- `endroid/qr-code` (optional, via Composer) or a simple PNG generator endpoint.

