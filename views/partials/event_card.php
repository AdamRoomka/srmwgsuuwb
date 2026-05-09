<?php foreach ($events as $event): ?>
    <?php
    $eventOccupiedSeats = $occupiedSeatsByEvent[(int) $event['id']] ?? [];
    $occupiedCount = count($eventOccupiedSeats);
    $availableSeats = getAvailableSeats($event, $occupiedSeatsByEvent);
    $statusClass = match ($event['status']) {
        'PLANOWANE' => 'planned',
        'ZAMKNIĘTE' => 'closed',
        'ANULOWANE' => 'cancelled',
        default => 'planned'
    };
    ?>
    <div class="event-card">
        <?php if ($currentUser && $currentUser['role'] === 'ADMINISTRATOR'): ?>
            <button type="button" class="pencil" data-event-id="<?= $event['id'] ?>">
                <i class="fa-solid fa-pencil"></i>
            </button>
        <?php endif; ?>

        <div class="top">
            <i class="fa-regular fa-calendar-check <?php echo $statusClass; ?> icon"></i>
            <div class="status-badge <?php echo $statusClass; ?>">
                <?php echo htmlspecialchars(statusLabel($event['status'])); ?>
            </div>
        </div>

        <h3><?php echo htmlspecialchars($event['name']); ?></h3>
        <p class="description"><?php echo htmlspecialchars($event['description']); ?></p>
        <p><i class="fa-regular fa-calendar"></i> <strong>Data:</strong>
            <?php echo htmlspecialchars(formatDatePl($event['start_at'])); ?></p>
        <p><i class="fa-regular fa-clock"></i> <strong>Czas trwania:</strong>
            <?php echo (int) $event['duration_minutes']; ?> min</p>
        <p><i class="fa-solid fa-chair"></i> <strong>Liczba miejsc:</strong> <?php echo (int) $event['total_seats']; ?></p>
        <p><i class="fa-solid fa-ban"></i> <strong>Zarezerwowane:</strong> <?php echo $occupiedCount; ?></p>
        <p><i class="fa-solid fa-circle-check" style="color: green;"></i> <strong>Dostępne:</strong>
            <?php echo $availableSeats; ?></p>

        <div class="event-actions">
            <?php if ($currentUser && $event['status'] === 'PLANOWANE' && $availableSeats > 0): ?>
                <button class="btn btn-reserve open-reservation-modal" data-event-id="<?php echo (int) $event['id']; ?>"
                    data-event-name="<?php echo htmlspecialchars($event['name']); ?>"
                    data-total-seats="<?php echo (int) $event['total_seats']; ?>"
                    data-available-seats="<?php echo $availableSeats; ?>"
                    data-occupied-seats='<?php echo htmlspecialchars(json_encode($eventOccupiedSeats), ENT_QUOTES, 'UTF-8'); ?>'>
                    Rezerwuj
                    <i class="fa-solid fa-chevron-right"></i>
                </button>
            <?php elseif (!$currentUser && $event['status'] === 'PLANOWANE' && $availableSeats > 0): ?>
                <p class="reserve-note">Zaloguj się, aby zarezerwować miejsca.</p>
            <?php elseif ($availableSeats <= 0 && $event['status'] === 'PLANOWANE'): ?>
                <p class="blocked-msg">BRAK MIEJSC</p>
            <?php else: ?>
                <div class="blocked-msg">
                    <i class="fa-solid fa-info"></i>
                    <p> Rezerwacja niedostępna dla tego wydarzenia.</p>
                </div>
            <?php endif; ?>

            <?php if ($currentUser && $currentUser['role'] === 'ADMINISTRATOR'): ?>

                <div class="buttonsEventModalClass">
                    <button type="button" class="btn btn-secondary open-edit-event-modal"
                        data-event-id="<?php echo (int) $event['id']; ?>"
                        data-event-name="<?php echo htmlspecialchars($event['name']); ?>"
                        data-event-description="<?php echo htmlspecialchars($event['description']); ?>"
                        data-event-start-at="<?php echo date('Y-m-d\\TH:i', strtotime($event['start_at'])); ?>"
                        data-event-duration="<?php echo (int) $event['duration_minutes']; ?>"
                        data-event-total-seats="<?php echo (int) $event['total_seats']; ?>"
                        data-event-status="<?php echo htmlspecialchars($event['status']); ?>">
                        Edytuj
                    </button>

                    <form method="post" onsubmit="return confirm('Czy na pewno chcesz usunąć to wydarzenie?');">
                        <input type="hidden" name="delete_event" value="1">
                        <input type="hidden" name="event_id" value="<?php echo (int) $event['id']; ?>">
                        <button type="submit" class="btn btn-secondary">Usuń</button>
                    </form>
                    <button type="button" class="btn btn-secondary open-manage-seats-modal"
                        data-event-id="<?php echo (int) $event['id']; ?>"
                        data-event-name="<?php echo htmlspecialchars($event['name']); ?>"
                        data-total-seats="<?php echo (int) $event['total_seats']; ?>"
                        data-occupied-seats='<?php echo htmlspecialchars(json_encode($eventOccupiedSeats), ENT_QUOTES, 'UTF-8'); ?>'>
                        Zarządzaj miejscami
                    </button>
                    <button type="button" class="btn btn-secondary open-reservations-list-modal"
                        data-event-id="<?php echo (int) $event['id']; ?>"
                        data-event-name="<?php echo htmlspecialchars($event['name']); ?>"
                        data-total-seats="<?php echo (int) $event['total_seats']; ?>"
                        data-occupied-seats='<?php echo htmlspecialchars(json_encode($eventOccupiedSeats), ENT_QUOTES, 'UTF-8'); ?>'>
                        Rezerwacje
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endforeach; ?>