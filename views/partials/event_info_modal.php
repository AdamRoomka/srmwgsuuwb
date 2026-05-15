<div class="modal reservation-modal" id="eventInfoModal">
    <div class="reservation-modal-content info-modal">
        
        <div class="reservation-modal-header">
            <h2 id="eventInfoTitle"></h2>
            <button type="button" class="modal-close" id="closeEventInfoModal">&times;</button>
        </div>

        <div class="step-container">
            <h4>Opis:</h4>
            <p id="eventInfoDescription"></p>

            <div class="event-info-grid">

                <div>
                    <i class="fa-regular fa-calendar-days planned icon"></i>
                    <strong>Data:</strong>
                    <span id="eventInfoDate"></span>
                </div>

                <div>
                    <i class="fa-regular fa-clock planned icon"></i>
                    <strong>Czas trwania:</strong>
                    <span id="eventInfoDuration"></span>
                </div>

                <div>
                    <i class="fa-solid fa-ticket planned icon"></i>
                    <strong>Liczba miejsc:</strong>
                    <span id="eventInfoSeats"></span>
                </div>

                <div>
                    <i class="fa-regular fa-circle-check planned icon"></i>
                    <strong>Dostępne miejsca:</strong>
                    <span id="eventInfoAvailable"></span>
                </div>

            </div>
            <div class="event-info-buttons">

                <button class="btn btn-primary" id="goToReservationSeats">
                    Zarezerwuj miejsca
                </button>

                <button class="btn btn-primary" id="goToPreviewSeats">
                    Zobacz miejsca
                </button>
            </div>
        </div>
    </div>
</div>