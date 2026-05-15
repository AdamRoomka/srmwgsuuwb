<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_event'])) {
    if (!$currentUser || $currentUser['role'] !== 'ADMINISTRATOR') {
        $_SESSION['reservationError'] = 'Tylko administrator może usuwać wydarzenia.';
        header('Location: index.php');
        exit;
    }

    $eventId = (int) ($_POST['event_id'] ?? 0);

    if ($eventId <= 0) {
        $_SESSION['reservationError'] = 'Nieprawidłowe ID wydarzenia.';
        header('Location: index.php');
        exit;
    }

    $pdo->beginTransaction();

    try {
        $deleteSeatsStmt = $pdo->prepare("
            DELETE FROM occupied_seats
            WHERE event_id = :event_id
        ");
        $deleteSeatsStmt->execute(['event_id' => $eventId]);

        $deleteEventStmt = $pdo->prepare("
            DELETE FROM events
            WHERE id = :event_id
        ");
        $deleteEventStmt->execute(['event_id' => $eventId]);

        $pdo->commit();

        $_SESSION['reservationMessage'] = 'Wydarzenie zostało usunięte.';
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        $_SESSION['reservationError'] = 'Nie udało się usunąć wydarzenia.';
    }

    header('Location: index.php');
    exit;
}