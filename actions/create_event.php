<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_event']) && !isset($_POST['login_email'])) {
    if (!$currentUser || $currentUser['role'] !== 'ADMINISTRATOR') {
        $reservationError = 'Tylko administrator może dodawać wydarzenia.';
    } else {
        $name = trim($_POST['event_name'] ?? '');
        $description = trim($_POST['event_description'] ?? '');
        $startAt = trim($_POST['event_start_at'] ?? '');
        $durationMinutes = (int) ($_POST['event_duration_minutes'] ?? 0);
        $totalSeats = (int) ($_POST['event_total_seats'] ?? 0);
        $status = trim($_POST['event_status'] ?? 'PLANOWANE');

        $allowedStatuses = ['PLANOWANE', 'ZAMKNIĘTE', 'ANULOWANE'];

        if ($name === '' || $description === '' || $startAt === '' || $durationMinutes <= 0 || $totalSeats <= 0) {
            $reservationError = 'Uzupełnij poprawnie wszystkie pola wydarzenia.';
        } elseif (!in_array($status, $allowedStatuses, true)) {
            $reservationError = 'Wybrano nieprawidłowy status wydarzenia.';
        } else {
            $insertEventStmt = $pdo->prepare("
                INSERT INTO events (name, description, start_at, duration_minutes, total_seats, status, created_by)
                VALUES (:name, :description, :start_at, :duration_minutes, :total_seats, :status, :created_by)
            ");

            $insertEventStmt->execute([
                'name' => $name,
                'description' => $description,
                'start_at' => $startAt,
                'duration_minutes' => $durationMinutes,
                'total_seats' => $totalSeats,
                'status' => $status,
                'created_by' => $currentUser['id'],
            ]);

            $_SESSION['reservationMessage'] = 'Nowe wydarzenie zostało dodane.';
            header('Location: index.php');
            exit;
        }
    }
}