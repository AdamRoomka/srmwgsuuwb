<div id="reservationModal" class="reservation-modal">
    <div class="reservation-modal-content">
        <div class="reservation-modal-header">
            <h2 id="modalEventTitle">Wybór miejsc</h2>
            <button type="button" class="modal-close" id="closeReservationModal">&times;</button>
        </div>

        <form method="post" id="reservationForm">
            <input type="hidden" name="reserve_event_id" id="reserveEventId">
            <input type="hidden" name="selected_seats" id="selectedSeatsInput">

            <div class="step-container">
                <h3>Krok 1: Ile miejsc potrzebujesz?</h3>
                <div class="form-group">
                    <input type="number" id="seatCountInput" min="1" value="1">
                </div>

                <h2>Wybór miejsc w Auli</h2>
                <h3>Krok 2: Kliknij na krzesła, aby je zająć</h3>

                <div class="screen-indicator">SCENA</div>

                <div class="legend">
                    <span class="seat-legend available"></span> Wolne
                    <span class="seat-legend selected"></span> Wybrane
                    <span class="seat-legend occupied"></span> Zajęte
                </div>

                <div id="seatMap" class="seat-map"></div>

                <div class="reservation-summary">
                    <p><strong>Wybrane miejsca:</strong> <span id="selectedSeatsList">brak</span></p>
                    <p><strong>Liczba wybranych:</strong> <span id="selectedSeatsCount">0</span></p>
                </div>

                <div class="action-buttons">
                    <button type="button" class="btn btn-secondary" id="cancelReservationBtn">Anuluj</button>
                    <button type="submit" class="btn btn-primary">Potwierdź rezerwację</button>
                </div>
            </div>
        </form>
    </div>
</div>