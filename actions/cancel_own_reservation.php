<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_own_reservation'])) {
    if (!$currentUser) {
        $_SESSION['reservationError'] = 'Musisz być zalogowany.';
        header('Location: index.php');
        exit;
    }

    $eventId = (int) ($_POST['event_id'] ?? 0);
    $userId = (int) $currentUser['id'];

    if ($eventId <= 0) {
        $_SESSION['reservationError'] = 'Nieprawidłowe wydarzenie.';
        header('Location: index.php');
        exit;
    }

    try {
        $pdo->beginTransaction();

        $userSeatsStmt = $pdo->prepare("
            SELECT seat_number
            FROM occupied_seats
            WHERE event_id = :event_id
              AND user_id = :user_id
              AND status = 'AKTYWNA'
        ");
        $userSeatsStmt->execute([
            'event_id' => $eventId,
            'user_id' => $userId,
        ]);
        $userSeats = $userSeatsStmt->fetchAll(PDO::FETCH_COLUMN);

        if (empty($userSeats)) {
            $pdo->rollBack();
            $_SESSION['reservationError'] = 'Nie masz rezerwacji dla tego wydarzenia.';
            header('Location: index.php');
            exit;
        }

        $deleteSeatsStmt = $pdo->prepare("
            DELETE FROM occupied_seats
            WHERE event_id = :event_id
              AND user_id = :user_id
              AND status = 'AKTYWNA'
        ");
        $deleteSeatsStmt->execute([
            'event_id' => $eventId,
            'user_id' => $userId,
        ]);

        $deleteReservationsStmt = $pdo->prepare("
            DELETE FROM reservations
            WHERE event_id = :event_id
              AND user_id = :user_id
              AND status = 'AKTYWNA'
        ");
        $deleteReservationsStmt->execute([
            'event_id' => $eventId,
            'user_id' => $userId,
        ]);

        $pdo->commit();

        $_SESSION['reservationMessage'] = 'Twoja rezerwacja została usunięta.';
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        $_SESSION['reservationError'] = 'Nie udało się usunąć Twojej rezerwacji.';
    }

    header('Location: index.php');
    exit;
}