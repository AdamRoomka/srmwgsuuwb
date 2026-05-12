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

    if ($seatAction === 'reserve' && $selectedUserId === null) {
        $_SESSION['reservationError'] = 'Wybierz użytkownika, do którego mają zostać przypisane miejsca.';
        header('Location: index.php');
        exit;
    }

    if ($seatAction === 'reserve') {
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
            $existingStmt = $pdo->prepare("
                SELECT seat_number
                FROM occupied_seats
                WHERE event_id = :event_id
                  AND user_id = :user_id
                  AND status = 'AKTYWNA'
            ");
            $existingStmt->execute([
                'event_id' => $eventId,
                'user_id' => $selectedUserId,
            ]);
            $existingSeats = array_map('intval', $existingStmt->fetchAll(PDO::FETCH_COLUMN));

            if (!empty($existingSeats)) {
                $deletePlaceholders = implode(',', array_fill(0, count($existingSeats), '?'));

                $deleteSeatsStmt = $pdo->prepare("
                    DELETE FROM occupied_seats
                    WHERE event_id = ?
                      AND user_id = ?
                      AND seat_number IN ($deletePlaceholders)
                ");
                $deleteSeatsStmt->execute(array_merge([$eventId, $selectedUserId], $existingSeats));
            }

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

            $_SESSION['reservationMessage'] = 'Miejsca zostały przypisane do wybranego użytkownika.';
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

            if (!empty($occupiedRows)) {
                $occupiedSeatNumbers = array_map(static function ($row) {
                    return (int) $row['seat_number'];
                }, $occupiedRows);

                $occupiedPlaceholders = implode(',', array_fill(0, count($occupiedSeatNumbers), '?'));

                $deleteSeatsStmt = $pdo->prepare("
                    DELETE FROM occupied_seats
                    WHERE event_id = ?
                      AND seat_number IN ($occupiedPlaceholders)
                ");
                $deleteSeatsStmt->execute(array_merge([$eventId], $occupiedSeatNumbers));

                $countByUser = [];
                foreach ($occupiedRows as $row) {
                    $userId = (int) $row['user_id'];
                    $countByUser[$userId] = ($countByUser[$userId] ?? 0) + 1;
                }

                foreach ($countByUser as $userId => $seatCount) {
                    $updateReservationsStmt->execute([
                        'event_id' => $eventId,
                        'user_id' => $userId,
                        'seat_count' => $seatCount,
                    ]);

                    $deleteEmptyReservationsStmt->execute([
                        'event_id' => $eventId,
                        'user_id' => $userId,
                    ]);
                }

                $_SESSION['reservationMessage'] = 'Wybrane zajęte miejsca zostały zwolnione.';
            } else {
                $_SESSION['reservationMessage'] = 'Żadne z wybranych miejsc nie było zajęte.';
            }
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