<div id="reservationModal" class="reservation-modal">
    <div class="reservation-modal-content">
        <div class="reservation-modal-header">
            <h2 id="modalEventTitle">Wybór miejsc w Auli</h2>
            <button type="button" class="modal-close" id="closeReservationModal">&times;</button>
        </div>

        <form method="post" id="reservationForm">
            <input type="hidden" name="reserve_event_id" id="reserveEventId">
            <input type="hidden" name="selected_seats" id="selectedSeatsInput">

            <div class="step-container">
                <h3>Kliknij na krzesła, aby je zająć</h3>

                <div id="seatMap" class="seat-map"></div>
                
                <div class="legend">
                    <span class="seat-legend available"></span> Wolne
                    <span class="seat-legend selected"></span> Wybrane
                    <span class="seat-legend occupied"></span> Zajęte
                    <span class="seat-legend mine"></span> Moje miejsca
                </div>

                <div class="reservation-summary">
                    <p><strong>Wybrane miejsca:</strong> <span id="selectedSeatsList">brak</span></p>
                    <p><strong>Liczba wybranych:</strong> <span id="selectedSeatsCount">0</span></p>
                </div>

                <div class="action-buttons">
                    <button type="button" class="btn btn-secondary" id="cancelReservationBtn">Anuluj</button>
                    <button type="submit" class="btn btn-primary">Potwierdź rezerwacje</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php if ($currentUser && $currentUser['role'] === 'ADMINISTRATOR'): ?>
    <div id="manageSeatsModal" class="reservation-modal">
        <div class="reservation-modal-content">
            <div class="reservation-modal-header">
                <h2 id="manageSeatsModalTitle">Zarządzanie miejscami</h2>
                <button type="button" class="modal-close" id="closeManageSeatsModal">&times;</button>
            </div>

            <form method="post" id="manageSeatsForm">
                <input type="hidden" name="admin_manage_seats" value="1">
                <input type="hidden" name="event_id" id="manageSeatsEventId">
                <input type="hidden" name="selected_seats" id="manageSeatsSelectedSeatsInput">
                <input type="hidden" name="seat_action" id="manageSeatsActionInput" value="reserve">

                <div class="step-container">
                    <div class="form-group">
                        <label for="manageSeatsUserSelect">Przypisz do użytkownika</label>
                        <select id="manageSeatsUserSelect" name="selected_user_id" required>
                            <?php foreach ($usersForAdminSeatAssignment as $userOption): ?>
                                <option value="<?php echo (int) $userOption['id']; ?>" <?php echo $currentUser && (int) $userOption['id'] === (int) $currentUser['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($userOption['first_name'] . ' ' . $userOption['last_name'] . ' (' . $userOption['email'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <h3>Kliknij miejsca, które chcesz zarezerwować</h3>

                    <div class="legend">
                        <span class="seat-legend available"></span> Wolne
                        <span class="seat-legend selected"></span> Wybrane
                        <span class="seat-legend occupied"></span> Zajęte
                        <span class="seat-legend mine"></span> Moje miejsca
                    </div>

                    <div id="manageSeatMap" class="seat-map"></div>

                    <div class="reservation-summary">
                        <p><strong>Wybrane miejsca:</strong> <span id="manageSelectedSeatsList">brak</span></p>
                        <p><strong>Liczba wybranych:</strong> <span id="manageSelectedSeatsCount">0</span></p>
                    </div>

                    <div class="action-buttons">
                        <button type="button" class="btn btn-secondary" id="cancelManageSeatsBtn">Anuluj</button>
                        <button type="submit" class="btn btn-primary" id="reserveManagedSeatsBtn">Zarezerwuj wybrane
                            miejsca</button>
                        <button type="button" class="btn btn-secondary" id="releaseManagedSeatsBtn">Usuń rezerwację
                            miejsc</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<?php if ($currentUser && $currentUser['role'] === 'ADMINISTRATOR'): ?>
    <div id="reservationsListModal" class="reservation-modal">
        <div class="reservation-modal-content">
            <div class="reservation-modal-header">
                <h2 id="reservationsListModalTitle">Rezerwacje</h2>
                <button type="button" class="modal-close" id="closeReservationsListModal">&times;</button>
            </div>

            <div class="reservations-content-wrapper">
                <div class="reservations-seatmap-container">
                    <h3>Plan sali</h3>
                    <div class="legend">
                        <span class="seat-legend available"></span> Wolne
                        <span class="seat-legend occupied"></span> Zajęte
                        <span class="seat-legend highlighted"></span> Wybrana rezerwacja
                    </div>
                    <div id="reservationsViewSeatMap" class="seat-map"></div>
                </div>

                <div class="reservations-list-container">
                    <h3>Lista rezerwacji</h3>
                    <div id="reservationsList" class="reservations-list"></div>
                </div>
            </div>

            <div class="action-buttons">
                <button type="button" class="btn btn-secondary" id="closeReservationsListBtn">Zamknij</button>
            </div>
        </div>
    </div>
<?php endif; ?>