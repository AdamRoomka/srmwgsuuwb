window.App = window.App || {};

document.addEventListener('DOMContentLoaded', function () {
    App.currentUserId = window.currentUserId ?? window.appData?.currentUserId ?? null;
    window.appData = window.appData || {};
    window.appData.currentUserId = App.currentUserId;

    App.parseJsonSafely = function (value, fallback = []) {
        try {
            const parsed = JSON.parse(value || '[]');
            return Array.isArray(parsed) ? parsed : fallback;
        } catch (e) {
            return fallback;
        }
    };

    App.openModal = function (modal) {
        if (modal) {
            modal.classList.add('active');
        }
    };

    App.closeModal = function (modal) {
        if (modal) {
            modal.classList.remove('active');
        }
    };

    const loginModal = document.getElementById('loginModal');
    const openLoginModalBtn = document.getElementById('openLoginModalBtn');
    const closeLoginModal = document.getElementById('closeLoginModal');

    if (openLoginModalBtn && loginModal) {
        openLoginModalBtn.addEventListener('click', function () {
            App.openModal(loginModal);
        });
    }

    if (closeLoginModal && loginModal) {
        closeLoginModal.addEventListener('click', function () {
            App.closeModal(loginModal);
        });
    }

    if (loginModal) {
        loginModal.addEventListener('click', function (e) {
            if (e.target === loginModal) {
                App.closeModal(loginModal);
            }
        });
    }

    const editEventModal = document.getElementById('editEventModal');
    const closeEditEventModal = document.getElementById('closeEditEventModal');
    const cancelEditEventBtn = document.getElementById('cancelEditEventBtn');

    const editEventId = document.getElementById('edit_event_id');
    const editEventName = document.getElementById('edit_event_name');
    const editEventDescription = document.getElementById('edit_event_description');
    const editEventStartAt = document.getElementById('edit_event_start_at');
    const editEventDuration = document.getElementById('edit_event_duration_minutes');
    const editEventTotalSeats = document.getElementById('edit_event_total_seats');
    const editEventStatus = document.getElementById('edit_event_status');

    document.querySelectorAll('.open-edit-event-modal').forEach(button => {
        button.addEventListener('click', function () {
            if (!editEventModal) return;

            editEventId.value = this.dataset.eventId || '';
            editEventName.value = this.dataset.eventName || '';
            editEventDescription.value = this.dataset.eventDescription || '';
            editEventStartAt.value = this.dataset.eventStartAt || '';
            editEventDuration.value = this.dataset.eventDuration || '';
            editEventTotalSeats.value = this.dataset.eventTotalSeats || '';
            editEventStatus.value = this.dataset.eventStatus || 'PLANOWANE';

            App.openModal(editEventModal);
        });
    });

    if (closeEditEventModal && editEventModal) {
        closeEditEventModal.addEventListener('click', function () {
            App.closeModal(editEventModal);
        });
    }

    if (cancelEditEventBtn && editEventModal) {
        cancelEditEventBtn.addEventListener('click', function () {
            App.closeModal(editEventModal);
        });
    }

    if (editEventModal) {
        editEventModal.addEventListener('click', function (e) {
            if (e.target === editEventModal) {
                App.closeModal(editEventModal);
            }
        });
    }

    document.dispatchEvent(new CustomEvent('app:ready'));

    const createEventModal = document.getElementById('createEventModal');
    const openCreateEventModal = document.getElementById('openCreateEventModal');
    const closeCreateEventModal = document.getElementById('closeCreateEventModal');
    const cancelCreateEventBtn = document.getElementById('cancelCreateEventBtn');
    const createEventBtn = document.getElementById('createEventBtn');

    if (openCreateEventModal && createEventModal) {
        openCreateEventModal.addEventListener('click', function () {
            App.openModal(createEventModal);
        });
    }

    if (closeCreateEventModal && createEventModal) {
        closeCreateEventModal.addEventListener('click', function () {
            App.closeModal(createEventModal);
        });
    }

    if (cancelCreateEventBtn && createEventModal) {
        cancelCreateEventBtn.addEventListener('click', function () {
            App.closeModal(createEventModal);
        });
    }

    const createEventForm = document.getElementById('createEventForm');

    if (createEventBtn && createEventModal) {
        createEventBtn.addEventListener('click', function (e) {
            if (!checkCreateEventFormValidity()) {
                e.preventDefault();
            } else {
                createEventForm.submit();
            }
        });
    }

    function checkCreateEventFormValidity() {
        const form = document.getElementById('createEventForm');
        if (!form) return false;

        const name = form.querySelector('#event_name');
        const startAt = form.querySelector('#event_start_at');
        const duration = form.querySelector('#event_duration_minutes');
        const totalSeats = form.querySelector('#event_total_seats');
        const description = form.querySelector('#event_description');
        const status = form.querySelector('#event_status');

        let isValid = true;
        let errorMessages = "";

        function setFirstError(message) {
            if (!errorMessages) {
                errorMessages = message;
            }
            isValid = false;
        }

        if (!name || !name.value.trim()) {
            setFirstError('Nazwa wydarzenia jest wymagana.');
            messageToastSet("event_name");
        }

        if (!description || !description.value.trim()) {
            setFirstError('Opis wydarzenia jest wymagany.');
            messageToastSet("event_description");
        }

        if (!startAt || !startAt.value.trim()) {
            setFirstError('Data i godzina rozpoczęcia są wymagane.');
            messageToastSet("event_start_at");
        }

        if (!duration || !duration.value.trim()) {
            setFirstError('Czas trwania wydarzenia jest wymagany.');
            messageToastSet("event_duration_minutes");
        }

        if (!/^\d+$/.test(duration.value.trim())) {
            setFirstError('Czas trwania musi być liczbą.');
            messageToastSet("event_duration_minutes");
        }

        if (!totalSeats || !totalSeats.value.trim()) {
            setFirstError('Liczba miejsc jest wymagana.');
            messageToastSet("event_total_seats");
        }

        if (!/^\d+$/.test(totalSeats.value.trim())) {
            setFirstError('Liczba miejsc musi być liczbą.');
            messageToastSet("event_total_seats");
        }

        if (!status || !status.value.trim()) {
            setFirstError('Status wydarzenia jest wymagany.');
            messageToastSet("event_status");
        }

        if (!isValid && errorMessages) {
            alert(errorMessages);
        }

        return isValid;
    }

    function messageToastSet(labelname) {
        const label = document.getElementById(labelname);
        label.classList.add("error");
    }

    if (createEventModal) {
        createEventModal.addEventListener('click', function (e) {
            if (e.target === createEventModal) {
                App.closeModal(createEventModal);
            }
        });
    }

    const toastNotification = document.getElementById('toastNotification');
    const closeToast = document.getElementById('closeToast');

    if (closeToast && toastNotification) {
        closeToast.addEventListener('click', function () {
            toastNotification.remove();
        });
    }

    if (toastNotification) {
        setTimeout(function () {
            toastNotification.remove();
        }, 5000);
    }

    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.pencil');
        const allModals = document.querySelectorAll('.buttonsEventModalClass');

        if (btn) {
            e.preventDefault();
            const card = btn.closest('.event-card');
            const modal = card.querySelector('.buttonsEventModalClass');
            allModals.forEach(m => {
                if (m !== modal) m.style.display = 'none';
            });
            const isFlex = window.getComputedStyle(modal).display === 'flex';
            modal.style.display = isFlex ? 'none' : 'flex';
        } else {
            allModals.forEach(modal => {
                if (modal.style.display === 'flex' && !e.target.closest('.buttonsEventModalClass')) {
                    modal.style.display = 'none';
                }
            });
        }
    });

    // Handle reservations list modal
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.open-reservations-list-modal');
        if (!btn) return;

        console.log('Rezerwacje button clicked');
        const reservationsListModal = document.getElementById('reservationsListModal');
        const reservationsListModalTitle = document.getElementById('reservationsListModalTitle');
        const reservationsList = document.getElementById('reservationsList');
        const reservationsViewSeatMap = document.getElementById('reservationsViewSeatMap');

        if (!reservationsListModal) {
            console.error('reservationsListModal not found');
            alert('BŁĄD: Modal rezerwacji nie został znaleziony');
            return;
        }

        const occupiedSeatsData = App.parseJsonSafely(btn.dataset.occupiedSeats || '[]');
        const totalSeatsFromButton = parseInt(btn.dataset.totalSeats || '0', 10);
        const eventName = btn.dataset.eventName || '';

        console.log('occupiedSeatsData:', occupiedSeatsData);
        console.log('eventName:', eventName);

        if (reservationsListModalTitle) {
            reservationsListModalTitle.textContent = 'Rezerwacje: ' + eventName;
        }

        // Render reservations list
        if (reservationsList) {
            reservationsList.innerHTML = '';

            if (occupiedSeatsData.length === 0) {
                reservationsList.innerHTML = '<p style="text-align: center; color: #666;">Brak rezerwacji</p>';
            } else {
                // Group by user
                const usersMap = {};
                occupiedSeatsData.forEach(seat => {
                    const userId = seat.user_id || 'null';
                    const userName = (seat.user_id && seat.first_name && seat.last_name) ? (seat.first_name + ' ' + seat.last_name) : 'Zarezerwowano';
                    if (!usersMap[userId]) {
                        usersMap[userId] = {
                            name: userName,
                            seats: []
                        };
                    }
                    usersMap[userId].seats.push(seat.seat_number);
                });

                // Create list
                const list = document.createElement('div');
                list.className = 'reservations-items';

                Object.keys(usersMap).forEach(userId => {
                    const userData = usersMap[userId];
                    const item = document.createElement('div');
                    item.className = 'reservation-item';
                    item.dataset.userId = userId;
                    item.dataset.seats = userData.seats.sort((a, b) => a - b).join(',');

                    const seatsText = userData.seats.sort((a, b) => a - b).join(', ');
                    item.innerHTML = `
                        <div class="reservation-user">
                            <strong>${userData.name}</strong>
                        </div>
                        <div class="reservation-seats">
                            Miejsca: ${seatsText}
                        </div>
                    `;

                    list.appendChild(item);
                });

                reservationsList.appendChild(list);
            }
        }

        // Render seat map for the current event every time, even when there are no reservations.
        if (reservationsViewSeatMap) {
            const occupiedSeatNumbers = occupiedSeatsData
                .map(s => parseInt(s.seat_number, 10))
                .filter(Number.isFinite);
            const occupiedMax = occupiedSeatNumbers.length ? Math.max(...occupiedSeatNumbers) : 0;
            const totalSeats = Math.max(totalSeatsFromButton, occupiedMax);

            const hallLayout = [
                { type: 'row', blocks: [4, 2, 3, 2, 3, 2, 7] },
                { type: 'row', blocks: [4, 2, 4, 1, 3, 2, 6] },
                { type: 'row', blocks: [4, 2, 4, 1, 3, 2, 6] },
                { type: 'row', blocks: [4, 2, 4, 1, 3, 2, 6] },
                { type: 'row', blocks: [4, 2, 4, 1, 3, 2, 4] },
                { type: 'row', blocks: [0, 7, 3, 1, 0, 5, 4] }
            ];

            // Build hall structure
            let seatNumber = 1;
            const rows = [];
            hallLayout.forEach(layoutRow => {
                const slots = [];
                layoutRow.blocks.forEach((count, index) => {
                    const isSeatBlock = index % 2 === 0;
                    if (isSeatBlock) {
                        for (let i = 0; i < count; i++) {
                            if (seatNumber <= totalSeats) {
                                slots.push({ type: 'seat', seatNumber });
                                seatNumber++;
                            } else {
                                slots.push({ type: 'empty' });
                            }
                        }
                    } else {
                        for (let i = 0; i < count; i++) {
                            slots.push({ type: 'gap' });
                        }
                    }
                });
                rows.push({ type: 'row', slots });
            });

            // Render seats
            reservationsViewSeatMap.innerHTML = '';
            const hallBody = document.createElement('div');
            hallBody.className = 'hall-body';

            rows.forEach(rowData => {
                const row = document.createElement('div');
                row.className = 'hall-row-grid';
                row.style.gridTemplateColumns = `repeat(${rowData.slots.length}, 38px)`;

                rowData.slots.forEach(slot => {
                    if (slot.type === 'gap' || slot.type === 'empty') {
                        const spacer = document.createElement('div');
                        spacer.className = 'seat-spacer';
                        row.appendChild(spacer);
                        return;
                    }

                    const seatNum = slot.seatNumber;
                    const seat = document.createElement('div');
                    seat.className = 'seat';
                    seat.dataset.seatNumber = seatNum;
                    seat.textContent = seatNum;

                    const isOccupied = occupiedSeatNumbers.includes(seatNum);

                    if (isOccupied) {
                        seat.classList.add('occupied');
                        const occupiedSeat = occupiedSeatsData.find(s => parseInt(s.seat_number, 10) === seatNum);
                        if (occupiedSeat) {
                            let tooltip = 'Zarezerwowano';
                            if (occupiedSeat.user_id && occupiedSeat.first_name && occupiedSeat.last_name) {
                                tooltip = occupiedSeat.first_name.charAt(0).toUpperCase() + occupiedSeat.last_name;
                            }
                            seat.dataset.userInitials = tooltip;
                            seat.title = tooltip;
                        }
                    }

                    row.appendChild(seat);
                });

                hallBody.appendChild(row);
            });

            reservationsViewSeatMap.appendChild(hallBody);
        }

        // Add click handler for reservation items
        const reservationItems = reservationsList.querySelectorAll('.reservation-item');
        reservationItems.forEach(item => {
            item.addEventListener('click', function () {
                // Remove active class from all items
                reservationItems.forEach(i => i.classList.remove('active'));
                // Add active class to clicked item
                this.classList.add('active');

                // Get seats for this user
                const userSeats = this.dataset.seats.split(',').map(s => parseInt(s.trim()));

                // Remove highlights from all seats
                const allSeats = reservationsViewSeatMap.querySelectorAll('.seat');
                allSeats.forEach(seat => seat.classList.remove('highlighted'));

                // Highlight user's seats
                userSeats.forEach(seatNum => {
                    const seat = reservationsViewSeatMap.querySelector(`[data-seat-number="${seatNum}"]`);
                    if (seat) {
                        seat.classList.add('highlighted');
                    }
                });
            });
        });

        // Open modal
        App.openModal(reservationsListModal);

        // Setup close handlers
        const closeReservationsListModal = document.getElementById('closeReservationsListModal');
        const closeReservationsListBtn = document.getElementById('closeReservationsListBtn');

        if (closeReservationsListModal) {
            closeReservationsListModal.onclick = function () {
                App.closeModal(reservationsListModal);
            };
        }

        if (closeReservationsListBtn) {
            closeReservationsListBtn.onclick = function () {
                App.closeModal(reservationsListModal);
            };
        }

        reservationsListModal.onclick = function (e) {
            if (e.target === reservationsListModal) {
                App.closeModal(reservationsListModal);
            }
        };
    });
});