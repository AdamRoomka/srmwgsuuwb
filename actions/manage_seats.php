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

    $eventStmt = $pdo->prepare("\n        SELECT id, total_seats, status\n        FROM events\n        WHERE id = :id\n        LIMIT 1\n    ");
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

    if ($seatAction === 'reserve' && $selectedUserId === null) {
        $_SESSION['reservationError'] = 'Wybierz użytkownika, do którego mają zostać przypisane miejsca.';
        header('Location: index.php');
        exit;
    }

    if ($seatAction === 'reserve') {
        $userStmt = $pdo->prepare("\n            SELECT id\n            FROM users\n            WHERE id = :id\n            LIMIT 1\n        ");
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

            $checkStmt = $pdo->prepare("\n                SELECT seat_number\n                FROM occupied_seats\n                WHERE event_id = ?\n                  AND status = 'AKTYWNA'\n                  AND seat_number IN ($placeholders)\n            ");
            $checkStmt->execute(array_merge([$eventId], $selectedSeatsArray));
            $takenSeats = $checkStmt->fetchAll(PDO::FETCH_COLUMN);

            if (!empty($takenSeats)) {
                $pdo->rollBack();
                $_SESSION['reservationError'] = 'Te miejsca są już zajęte: ' . implode(', ', $takenSeats);
                header('Location: index.php');
                exit;
            }

            $reservationStmt = $pdo->prepare("\n                INSERT INTO reservations (user_id, event_id, reserved_seats, status)\n                VALUES (:user_id, :event_id, :reserved_seats, 'AKTYWNA')\n            ");
            $reservationStmt->execute([
                'user_id' => $selectedUserId,
                'event_id' => $eventId,
                'reserved_seats' => count($selectedSeatsArray),
            ]);

            $seatInsertStmt = $pdo->prepare("\n                INSERT INTO occupied_seats (event_id, seat_number, user_id, status)\n                VALUES (:event_id, :seat_number, :user_id, 'AKTYWNA')\n            ");

            foreach ($selectedSeatsArray as $seatNumber) {
                $seatInsertStmt->execute([
                    'event_id' => $eventId,
                    'seat_number' => $seatNumber,
                    'user_id' => $selectedUserId,
                ]);
            }

            $_SESSION['reservationMessage'] = 'Miejsca zostały przypisane do wybranego użytkownika.';
        }

        if ($seatAction === 'release') {
            $placeholders = implode(',', array_fill(0, count($selectedSeatsArray), '?'));

            $checkStmt = $pdo->prepare("\n                SELECT id, seat_number\n                FROM occupied_seats\n                WHERE event_id = ?\n                  AND status = 'AKTYWNA'\n                  AND seat_number IN ($placeholders)\n            ");
            $checkStmt->execute(array_merge([$eventId], $selectedSeatsArray));
            $occupiedRows = $checkStmt->fetchAll();

            if (count($occupiedRows) !== count($selectedSeatsArray)) {
                $pdo->rollBack();
                $_SESSION['reservationError'] = 'Niektóre wybrane miejsca nie są obecnie zarezerwowane.';
                header('Location: index.php');
                exit;
            }

            $deleteSeatsStmt = $pdo->prepare("\n                DELETE FROM occupied_seats\n                WHERE event_id = ?\n                  AND seat_number IN ($placeholders)\n            ");
            $deleteSeatsStmt->execute(array_merge([$eventId], $selectedSeatsArray));

            $updateReservationsStmt = $pdo->prepare("\n                UPDATE reservations\n                SET reserved_seats = reserved_seats - 1\n                WHERE event_id = :event_id\n                  AND status = 'AKTYWNA'\n                  AND reserved_seats > 0\n            ");
            foreach ($selectedSeatsArray as $seatNumber) {
                $updateReservationsStmt->execute(['event_id' => $eventId]);
            }

            $deleteEmptyReservationsStmt = $pdo->prepare("\n                DELETE FROM reservations\n                WHERE event_id = :event_id\n                  AND status = 'AKTYWNA'\n                  AND reserved_seats <= 0\n            ");
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