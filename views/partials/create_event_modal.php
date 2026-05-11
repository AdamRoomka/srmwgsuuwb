<?php if ($currentUser && $currentUser['role'] === 'ADMINISTRATOR'): ?>
    <div id="createEventModal" class="reservation-modal">
        <div class="reservation-modal-content">
            <div class="reservation-modal-header">
                <h2>Dodaj nowe wydarzenie</h2>
                <button type="button" class="modal-close" id="closeCreateEventModal">&times;</button>
            </div>

            <form method="post" id="createEventForm" novalidate>
                <input type="hidden" name="create_event" value="1">

                <div class="step-container">
                    <div class="form-group">
                        <label for="event_name">Nazwa wydarzenia</label>
                        <input type="text" id="event_name" name="event_name" required>
                    </div>

                    <div class="form-group">
                        <label for="event_description">Opis</label>
                        <input type="text" id="event_description" name="event_description" required>
                    </div>

                    <div class="form-group">
                        <label for="event_start_at">Data i godzina rozpoczęcia</label>
                        <input type="datetime-local" id="event_start_at" name="event_start_at" required>
                    </div>

                    <div class="form-group">
                        <label for="event_duration_minutes">Czas trwania (minuty)</label>
                        <input type="number" id="event_duration_minutes" name="event_duration_minutes" min="1" required>
                    </div>

                    <div class="form-group">
                        <label for="event_total_seats">Liczba miejsc</label>
                        <input type="number" id="event_total_seats" name="event_total_seats" min="1" max="100" value="90"
                            required>
                    </div>

                    <div class="form-group">
                        <label for="event_status">Status wydarzenia</label>
                        <select id="event_status" name="event_status" required>
                            <option value="PLANOWANE">PLANOWANE</option>
                            <option value="ZAMKNIĘTE">ZAMKNIĘTE</option>
                            <option value="ANULOWANE">ANULOWANE</option>
                        </select>
                    </div>

                    <div class="action-buttons">
                        <button type="button" class="btn btn-secondary" id="cancelCreateEventBtn">Anuluj</button>
                        <button type="button" class="btn btn-primary" id="createEventBtn">Dodaj wydarzenie</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div id="editEventModal" class="reservation-modal">
        <div class="reservation-modal-content">
            <div class="reservation-modal-header">
                <h2>Edycja wydarzenia</h2>
                <button type="button" class="modal-close" id="closeEditEventModal">&times;</button>
            </div>

            <form method="post" id="editEventForm" novalidate>
                <input type="hidden" name="update_event" value="1">
                <input type="hidden" name="event_id" id="edit_event_id">

                <div class="step-container">
                    <div class="form-group">
                        <label for="edit_event_name">Nazwa wydarzenia</label>
                        <input type="text" id="edit_event_name" name="event_name" required>
                    </div>

                    <div class="form-group">
                        <label for="edit_event_description">Opis</label>
                        <input type="text" id="edit_event_description" name="event_description" required>
                    </div>

                    <div class="form-group">
                        <label for="edit_event_start_at">Data i godzina rozpoczęcia</label>
                        <input type="datetime-local" id="edit_event_start_at" name="event_start_at" required>
                    </div>

                    <div class="form-group">
                        <label for="edit_event_duration_minutes">Czas trwania (minuty)</label>
                        <input type="number" id="edit_event_duration_minutes" name="event_duration_minutes" min="1"
                            required>
                    </div>

                    <div class="form-group">
                        <label for="edit_event_total_seats">Liczba miejsc</label>
                        <input type="number" id="edit_event_total_seats" name="event_total_seats" min="1" max="100"
                            value="90" required>
                    </div>

                    <div class="form-group">
                        <label for="edit_event_status">Status wydarzenia</label>
                        <select id="edit_event_status" name="event_status" required>
                            <option value="PLANOWANE">PLANOWANE</option>
                            <option value="ZAMKNIĘTE">ZAMKNIĘTE</option>
                            <option value="ANULOWANE">ANULOWANE</option>
                        </select>
                    </div>

                    <div class="action-buttons">
                        <button type="button" class="btn btn-secondary" id="cancelEditEventBtn">Anuluj</button>
                        <button type="button" class="btn btn-primary" id="editEventBtn">Zapisz zmiany</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

<?php endif; ?>