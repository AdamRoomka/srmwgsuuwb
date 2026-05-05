<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_manage_seats'])) {
    if (!$currentUser || $currentUser['role'] !== 'ADMINISTRATOR') {
        $_SESSION['reservationError'] = 'Tylko administrator może zarządzać miejscami.';
        header('Location: index.php');
        exit;
    }

    $eventId = (int) ($_POST['event_id'] ?? 0);
    $selectedSeatsRaw = trim($_POST['selected_seats'] ?? '');
    $selectedUserIdRaw = trim($_POST['selected_user_id'] ?? '');
    $seatAction = trim($_POST['seat_action'] ?? '');

    $selectedSeatsArray = array_values(array_unique(array_filter(array_map('intval', explode(',', $selectedSeatsRaw)))));
    $selectedUserId = $selectedUserIdRaw === '' ? null : (int) $selectedUserIdRaw;

    if ($eventId <= 0) {
        $_SESSION['reservationError'] = 'Nieprawidłowe wydarzenie.';
        header('Location: index.php');
        exit;
    }

    if (empty($selectedSeatsArray)) {
        $_SESSION['reservationError'] = 'Nie wybrano żadnych miejsc.';
        header('Location: index.php');
        exit;
    }

    if (!in_array($seatAction, ['reserve', 'release'], true)) {
        $_SESSION['reservationError'] = 'Nieprawidłowa akcja.';
        header('Location: index.php');
        exit;
    }

    $eventStmt = $pdo->prepare("
        SELECT id, total_seats, status
        FROM events
        WHERE id = :id
        LIMIT 1
    ");
    $eventStmt->execute(['id' => $eventId]);
    $eventRow = $eventStmt->fetch();

    if (!$eventRow) {
        $_SESSION['reservationError'] = 'Nie znaleziono wydarzenia.';
        header('Location: index.php');
        exit;
    }

    if ($eventRow['status'] !== 'PLANOWANE') {
        $_SESSION['reservationError'] = 'Miejscami można zarządzać tylko dla wydarzeń PLANOWANYCH.';
        header('Location: index.php');
        exit;
    }

    $totalSeats = (int) $eventRow['total_seats'];

    foreach ($selectedSeatsArray as $seatNumber) {
        if ($seatNumber < 1 || $seatNumber > $totalSeats) {
            $_SESSION['reservationError'] = 'Wybrano nieprawidłowy numer miejsca.';
            header('Location: index.php');
            exit;
        }
    }

    if ($seatAction === 'reserve' && $selectedUserId !== null) {
        $userStmt = $pdo->prepare("
            SELECT id
            FROM users
            WHERE id = :id
            LIMIT 1
        ");
        $userStmt->execute(['id' => $selectedUserId]);
        $userExists = $userStmt->fetch();

        if (!$userExists) {
            $_SESSION['reservationError'] = 'Wybrany użytkownik nie istnieje.';
            header('Location: index.php');
            exit;
        }
    }

    try {
        $pdo->beginTransaction();

        if ($seatAction === 'reserve') {
            $placeholders = implode(',', array_fill(0, count($selectedSeatsArray), '?'));

            $checkStmt = $pdo->prepare("
                SELECT seat_number
                FROM occupied_seats
                WHERE event_id = ?
                  AND status = 'AKTYWNA'
                  AND seat_number IN ($placeholders)
            ");
            $checkStmt->execute(array_merge([$eventId], $selectedSeatsArray));
            $takenSeats = $checkStmt->fetchAll(PDO::FETCH_COLUMN);

            if (!empty($takenSeats)) {
                $pdo->rollBack();
                $_SESSION['reservationError'] = 'Te miejsca są już zajęte: ' . implode(', ', $takenSeats);
                header('Location: index.php');
                exit;
            }

            $reservationStmt = $pdo->prepare("
                INSERT INTO reservations (user_id, event_id, reserved_seats, status)
                VALUES (:user_id, :event_id, :reserved_seats, 'AKTYWNA')
            ");
            $reservationStmt->execute([
                'user_id' => $selectedUserId,
                'event_id' => $eventId,
                'reserved_seats' => count($selectedSeatsArray),
            ]);

            $seatInsertStmt = $pdo->prepare("
                INSERT INTO occupied_seats (event_id, seat_number, user_id, status)
                VALUES (:event_id, :seat_number, :user_id, 'AKTYWNA')
            ");

            foreach ($selectedSeatsArray as $seatNumber) {
                $seatInsertStmt->execute([
                    'event_id' => $eventId,
                    'seat_number' => $seatNumber,
                    'user_id' => $selectedUserId,
                ]);
            }

            if ($selectedUserId !== null) {
                $_SESSION['reservationMessage'] = 'Miejsca zostały przypisane do wybranego użytkownika.';
            } else {
                $_SESSION['reservationMessage'] = 'Miejsca zostały zarezerwowane bez przypisania do konkretnej osoby.';
            }
        }

        if ($seatAction === 'release') {
            $placeholders = implode(',', array_fill(0, count($selectedSeatsArray), '?'));

            $checkStmt = $pdo->prepare("
                SELECT id, seat_number
                FROM occupied_seats
                WHERE event_id = ?
                  AND status = 'AKTYWNA'
                  AND seat_number IN ($placeholders)
            ");
            $checkStmt->execute(array_merge([$eventId], $selectedSeatsArray));
            $occupiedRows = $checkStmt->fetchAll();

            if (count($occupiedRows) !== count($selectedSeatsArray)) {
                $pdo->rollBack();
                $_SESSION['reservationError'] = 'Niektóre wybrane miejsca nie są obecnie zarezerwowane.';
                header('Location: index.php');
                exit;
            }

            $deleteSeatsStmt = $pdo->prepare("
                DELETE FROM occupied_seats
                WHERE event_id = ?
                  AND seat_number IN ($placeholders)
            ");
            $deleteSeatsStmt->execute(array_merge([$eventId], $selectedSeatsArray));

            $updateReservationsStmt = $pdo->prepare("
                UPDATE reservations
                SET reserved_seats = reserved_seats - 1
                WHERE event_id = :event_id
                  AND status = 'AKTYWNA'
                  AND reserved_seats > 0
            ");
            foreach ($selectedSeatsArray as $seatNumber) {
                $updateReservationsStmt->execute(['event_id' => $eventId]);
            }

            $deleteEmptyReservationsStmt = $pdo->prepare("
                DELETE FROM reservations
                WHERE event_id = :event_id
                  AND status = 'AKTYWNA'
                  AND reserved_seats <= 0
            ");
            $deleteEmptyReservationsStmt->execute(['event_id' => $eventId]);

            $_SESSION['reservationMessage'] = 'Wybrane miejsca zostały zwolnione.';
        }

        $pdo->commit();
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        $_SESSION['reservationError'] = 'Nie udało się zapisać zmian miejsc.';
    }

    header('Location: index.php');
    exit;
}