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
        <div class="status-badge <?php echo $statusClass; ?>">
            <?php echo htmlspecialchars(statusLabel($event['status'])); ?>
        </div>

        <h3><?php echo htmlspecialchars($event['name']); ?></h3>
        <p><strong>Data:</strong> <?php echo htmlspecialchars(formatDatePl($event['start_at'])); ?></p>
        <p><?php echo htmlspecialchars($event['description']); ?></p>
        <p><strong>Czas trwania:</strong> <?php echo (int) $event['duration_minutes']; ?> min</p>
        <p><strong>Liczba miejsc:</strong> <?php echo (int) $event['total_seats']; ?></p>
        <p><strong>Zarezerwowane:</strong> <?php echo $occupiedCount; ?></p>
        <p><strong>Dostępne:</strong> <?php echo $availableSeats; ?></p>

        <?php if ($currentUser && $event['status'] === 'PLANOWANE' && $availableSeats > 0): ?>
            <button class="btn btn-primary open-reservation-modal" data-event-id="<?php echo (int) $event['id']; ?>"
                data-event-name="<?php echo htmlspecialchars($event['name']); ?>"
                data-total-seats="<?php echo (int) $event['total_seats']; ?>"
                data-available-seats="<?php echo $availableSeats; ?>"
                data-occupied-seats='<?php echo htmlspecialchars(json_encode($eventOccupiedSeats), ENT_QUOTES, 'UTF-8'); ?>'>
                Rezerwuj
            </button>
        <?php elseif (!$currentUser && $event['status'] === 'PLANOWANE' && $availableSeats > 0): ?>
            <p class="reserve-note">Zaloguj się, aby zarezerwować miejsca.</p>
        <?php elseif ($availableSeats <= 0 && $event['status'] === 'PLANOWANE'): ?>
            <p class="blocked-msg">FULL (BRAK MIEJSC)</p>
        <?php else: ?>
            <p class="blocked-msg">Rezerwacja niedostępna dla tego wydarzenia.</p>
        <?php endif; ?>
    </div>
<?php endforeach; ?>