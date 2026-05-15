<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reserve_event_id'], $_POST['selected_seats']) && !isset($_POST['login_email'])) {
    if (!$currentUser) {
        $_SESSION['reservationError'] = 'Musisz być zalogowany, aby zarezerwować miejsca.';
    } else {
        $eventId = (int) $_POST['reserve_event_id'];
        $selectedSeatsRaw = trim($_POST['selected_seats']);
        $selectedSeatsArray = array_values(array_unique(array_filter(array_map('intval', explode(',', $selectedSeatsRaw)))));

        $eventStmt = $pdo->prepare("SELECT id, total_seats, status FROM events WHERE id = :id LIMIT 1");
        $eventStmt->execute(['id' => $eventId]);
        $eventRow = $eventStmt->fetch();

        if (!$eventRow) {
            $_SESSION['reservationError'] = 'Nie znaleziono wydarzenia.';
        } elseif ($eventRow['status'] !== 'PLANOWANE') {
            $_SESSION['reservationError'] = 'Rezerwacja niedostępna dla tego wydarzenia.';
        } else {
            $totalSeats = (int) $eventRow['total_seats'];

            $hasError = false;
            foreach ($selectedSeatsArray as $seatNumber) {
                if ($seatNumber < 1 || $seatNumber > $totalSeats) {
                    $_SESSION['reservationError'] = 'Wybrano nieprawidłowy numer miejsca.';
                    $hasError = true;
                    break;
                }
            }

            if (!$hasError) {
                try {
                    $pdo->beginTransaction();

                    $existingStmt = $pdo->prepare("
                        SELECT seat_number
                        FROM occupied_seats
                        WHERE event_id = :event_id
                          AND user_id = :user_id
                          AND status = 'AKTYWNA'
                    ");
                    $existingStmt->execute([
                        'event_id' => $eventId,
                        'user_id' => $currentUser['id'],
                    ]);
                    $existingSeats = $existingStmt->fetchAll(PDO::FETCH_COLUMN);

                    $currentUserSeats = array_values(array_map('intval', $existingSeats));

                    if (!empty($selectedSeatsArray)) {
                        $placeholders = implode(',', array_fill(0, count($selectedSeatsArray), '?'));
                        $checkSql = "
                            SELECT seat_number
                            FROM occupied_seats
                            WHERE event_id = ?
                              AND status = 'AKTYWNA'
                              AND user_id <> ?
                              AND seat_number IN ($placeholders)
                        ";

                        $checkStmt = $pdo->prepare($checkSql);
                        $checkStmt->execute(array_merge([$eventId, $currentUser['id']], $selectedSeatsArray));
                        $takenSeats = $checkStmt->fetchAll(PDO::FETCH_COLUMN);

                        if (!empty($takenSeats)) {
                            $pdo->rollBack();
                            $_SESSION['reservationError'] = 'Te miejsca są już zajęte: ' . implode(', ', $takenSeats);
                            header('Location: index.php');
                            exit;
                        }
                    }

                    if (!empty($currentUserSeats)) {
                        $deletePlaceholders = implode(',', array_fill(0, count($currentUserSeats), '?'));

                        $deleteSeatsStmt = $pdo->prepare("
                            DELETE FROM occupied_seats
                            WHERE event_id = ?
                              AND user_id = ?
                              AND seat_number IN ($deletePlaceholders)
                        ");
                        $deleteSeatsStmt->execute(array_merge([$eventId, $currentUser['id']], $currentUserSeats));
                    }

                    if (!empty($selectedSeatsArray)) {
                        $seatInsertStmt = $pdo->prepare("
                            INSERT INTO occupied_seats (event_id, seat_number, user_id, status)
                            VALUES (:event_id, :seat_number, :user_id, 'AKTYWNA')
                        ");

                        foreach ($selectedSeatsArray as $seatNumber) {
                            $seatInsertStmt->execute([
                                'event_id' => $eventId,
                                'seat_number' => $seatNumber,
                                'user_id' => $currentUser['id'],
                            ]);
                        }

                        $_SESSION['reservationMessage'] = 'Rezerwacja została zaktualizowana. Wybrane miejsca: ' . implode(', ', $selectedSeatsArray);
                    } else {
                        $_SESSION['reservationMessage'] = 'Rezerwacja została anulowana.';
                    }

                    $pdo->commit();
                    header('Location: index.php');
                    exit;
                } catch (Exception $e) {
                    if ($pdo->inTransaction()) {
                        $pdo->rollBack();
                    }
                    $_SESSION['reservationError'] = 'Błąd podczas rezerwacji: ' . $e->getMessage();
                }
            }
        }
    }
    header('Location: index.php');
    exit;
}