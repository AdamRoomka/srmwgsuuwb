<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reserve_event_id'], $_POST['selected_seats']) && !isset($_POST['login_email'])) {
    if (!$currentUser) {
        $reservationError = 'Musisz być zalogowany, aby zarezerwować miejsca.';
    } else {
        $eventId = (int) $_POST['reserve_event_id'];
        $selectedSeatsRaw = trim($_POST['selected_seats']);
        $selectedSeatsArray = array_values(array_unique(array_filter(array_map('intval', explode(',', $selectedSeatsRaw)))));
        $reservedSeatsCount = count($selectedSeatsArray);

        if ($reservedSeatsCount <= 0) {
            $reservationError = 'Nie wybrano żadnych miejsc.';
        } else {
            $eventStmt = $pdo->prepare("SELECT id, total_seats, status FROM events WHERE id = :id LIMIT 1");
            $eventStmt->execute(['id' => $eventId]);
            $eventRow = $eventStmt->fetch();

            if (!$eventRow) {
                $reservationError = 'Nie znaleziono wydarzenia.';
            } elseif ($eventRow['status'] !== 'PLANOWANE') {
                $reservationError = 'Rezerwacja niedostępna dla tego wydarzenia.';
            } else {
                $totalSeats = (int) $eventRow['total_seats'];

                foreach ($selectedSeatsArray as $seatNumber) {
                    if ($seatNumber < 1 || $seatNumber > $totalSeats) {
                        $reservationError = 'Wybrano nieprawidłowy numer miejsca.';
                        break;
                    }
                }

                if (!$reservationError) {
                    $placeholders = implode(',', array_fill(0, count($selectedSeatsArray), '?'));

                    $checkSql = "
                        SELECT seat_number
                        FROM occupied_seats
                        WHERE event_id = ?
                          AND status = 'AKTYWNA'
                          AND seat_number IN ($placeholders)
                    ";

                    $checkStmt = $pdo->prepare($checkSql);
                    $checkStmt->execute(array_merge([$eventId], $selectedSeatsArray));
                    $takenSeats = $checkStmt->fetchAll(PDO::FETCH_COLUMN);

                    if (!empty($takenSeats)) {
                        $reservationError = 'Te miejsca są już zajęte: ' . implode(', ', $takenSeats);
                    } else {
                        $pdo->beginTransaction();

                        $reservationStmt = $pdo->prepare("
                            INSERT INTO reservations (user_id, event_id, reserved_seats, status)
                            VALUES (:user_id, :event_id, :reserved_seats, 'AKTYWNA')
                        ");
                        $reservationStmt->execute([
                            'user_id' => $currentUser['id'],
                            'event_id' => $eventId,
                            'reserved_seats' => $reservedSeatsCount,
                        ]);

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

                        $pdo->commit();
                        $reservationMessage = 'Rezerwacja została zapisana. Wybrane miejsca: ' . implode(', ', $selectedSeatsArray);
                    }
                }
            }
        }
    }
}