<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_event'])) {
    if (!$currentUser || $currentUser['role'] !== 'ADMINISTRATOR') {
        $_SESSION['reservationError'] = 'Tylko administrator może edytować wydarzenia.';
        header('Location: index.php');
        exit;
    }

    $eventId = (int) ($_POST['event_id'] ?? 0);
    $name = trim($_POST['event_name'] ?? '');
    $description = trim($_POST['event_description'] ?? '');
    $startAt = trim($_POST['event_start_at'] ?? '');
    $durationMinutes = (int) ($_POST['event_duration_minutes'] ?? 0);
    $totalSeats = (int) ($_POST['event_total_seats'] ?? 0);
    $status = trim($_POST['event_status'] ?? 'PLANOWANE');

    $allowedStatuses = ['PLANOWANE', 'ZAMKNIĘTE', 'ANULOWANE'];

    if ($eventId <= 0 || $name === '' || $description === '' || $startAt === '' || $durationMinutes <= 0 || $totalSeats <= 0) {
        $_SESSION['reservationError'] = 'Uzupełnij poprawnie wszystkie pola edycji wydarzenia.';
        header('Location: index.php');
        exit;
    }

    if (!in_array($status, $allowedStatuses, true)) {
        $_SESSION['reservationError'] = 'Wybrano nieprawidłowy status wydarzenia.';
        header('Location: index.php');
        exit;
    }

    $occupiedCountStmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM occupied_seats
        WHERE event_id = :event_id AND status = 'AKTYWNA'
    ");
    $occupiedCountStmt->execute(['event_id' => $eventId]);
    $occupiedCount = (int) $occupiedCountStmt->fetchColumn();

    if ($totalSeats < $occupiedCount) {
        $_SESSION['reservationError'] = 'Nie można ustawić liczby miejsc mniejszej niż liczba już zajętych miejsc.';
        header('Location: index.php');
        exit;
    }

    $updateStmt = $pdo->prepare("
        UPDATE events
        SET name = :name,
            description = :description,
            start_at = :start_at,
            duration_minutes = :duration_minutes,
            total_seats = :total_seats,
            status = :status
        WHERE id = :id
    ");

    $updateStmt->execute([
        'id' => $eventId,
        'name' => $name,
        'description' => $description,
        'start_at' => $startAt,
        'duration_minutes' => $durationMinutes,
        'total_seats' => $totalSeats,
        'status' => $status,
    ]);

    $_SESSION['reservationMessage'] = 'Wydarzenie zostało zaktualizowane.';
    header('Location: index.php');
    exit;
}