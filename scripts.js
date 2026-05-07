document.addEventListener('DOMContentLoaded', function () {
    const currentUserId = window.currentUserId;
    const modal = document.getElementById('reservationModal');
    const closeModalBtn = document.getElementById('closeReservationModal');
    const cancelReservationBtn = document.getElementById('cancelReservationBtn');
    const seatMap = document.getElementById('seatMap');
    const selectedSeatsList = document.getElementById('selectedSeatsList');
    const selectedSeatsCount = document.getElementById('selectedSeatsCount');
    const modalEventTitle = document.getElementById('modalEventTitle');
    const reserveEventId = document.getElementById('reserveEventId');
    const selectedSeatsInput = document.getElementById('selectedSeatsInput');
    const reservationForm = document.getElementById('reservationForm');
    const createEventModal = document.getElementById('createEventModal');
    const openCreateEventModal = document.getElementById('openCreateEventModal');
    const closeCreateEventModal = document.getElementById('closeCreateEventModal');
    const cancelCreateEventBtn = document.getElementById('cancelCreateEventBtn');
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

    const manageSeatsModal = document.getElementById('manageSeatsModal');
    const closeManageSeatsModal = document.getElementById('closeManageSeatsModal');
    const cancelManageSeatsBtn = document.getElementById('cancelManageSeatsBtn');
    const manageSeatMap = document.getElementById('manageSeatMap');
    const manageSeatsModalTitle = document.getElementById('manageSeatsModalTitle');
    const manageSeatsEventId = document.getElementById('manageSeatsEventId');
    const manageSeatsSelectedSeatsInput = document.getElementById('manageSeatsSelectedSeatsInput');
    const manageSelectedSeatsList = document.getElementById('manageSelectedSeatsList');
    const manageSelectedSeatsCount = document.getElementById('manageSelectedSeatsCount');

    const manageSeatsActionInput = document.getElementById('manageSeatsActionInput');
    const reserveManagedSeatsBtn = document.getElementById('reserveManagedSeatsBtn');
    const releaseManagedSeatsBtn = document.getElementById('releaseManagedSeatsBtn');



    let selectedSeats = [];
    let occupiedSeats = [];
    let currentEventId = null;
    let currentTotalSeats = 0;
    let adminSelectedSeats = [];
    let adminOccupiedSeats = [];
    let adminCurrentTotalSeats = 0;



    function parseOccupiedSeats(jsonString) {
        try {
            const parsed = JSON.parse(jsonString || '[]');
            return Array.isArray(parsed) ? parsed : [];
        } catch (e) {
            return [];
        }
    }

    function renderSeats() {
        if (!seatMap) return;

        seatMap.innerHTML = '';

        for (let i = 1; i <= currentTotalSeats; i++) {
            const seat = document.createElement('div');
            seat.classList.add('seat');
            seat.dataset.seatNumber = i;
            seat.title = 'Miejsce ' + i;
            seat.textContent = i;

            const occupiedSeat = occupiedSeats.find(item => parseInt(item.seat_number, 10) === i);
            const isOccupied = !!occupiedSeat;
            const isMine = isOccupied && currentUserId !== null && parseInt(occupiedSeat.user_id, 10) === currentUserId;
            const isSelected = selectedSeats.includes(i);

            if (isOccupied) {
                seat.classList.add('occupied');
            }

            if (isMine) {
                seat.classList.add('mine');
            }

            if (isSelected) {
                seat.classList.add('selected');
            }

            seat.addEventListener('click', function () {
                if (isOccupied) {
                    return;
                }

                if (selectedSeats.includes(i)) {
                    selectedSeats = selectedSeats.filter(num => num !== i);
                } else {
                    selectedSeats.push(i);
                }

                updateSelectedSeatsInfo();
                renderSeats();
            });

            seatMap.appendChild(seat);
        }
    }

    function updateSelectedSeatsInfo() {
        const sortedSeats = [...selectedSeats].sort((a, b) => a - b);
        selectedSeatsCount.textContent = sortedSeats.length;
        selectedSeatsList.textContent = sortedSeats.length ? sortedSeats.join(', ') : 'brak';
        selectedSeatsInput.value = sortedSeats.join(',');
    }

    function openModal(eventName, eventId, totalSeats, availableSeats, occupiedSeatsJson) {
        currentEventId = eventId;
        currentTotalSeats = totalSeats;
        selectedSeats = [];
        occupiedSeats = parseOccupiedSeats(occupiedSeatsJson);

        modalEventTitle.textContent = 'Rezerwacja: ' + eventName;
        reserveEventId.value = eventId;
        selectedSeatsInput.value = '';

        updateSelectedSeatsInfo();
        renderSeats();
        modal.classList.add('active');
    }

    document.querySelectorAll('.open-reservation-modal').forEach(button => {
        button.addEventListener('click', function () {
            const eventName = this.dataset.eventName;
            const eventId = this.dataset.eventId;
            const totalSeats = parseInt(this.dataset.totalSeats, 10);
            const availableSeats = parseInt(this.dataset.availableSeats, 10);
            const occupiedSeatsJson = this.dataset.occupiedSeats || '[]';

            openModal(eventName, eventId, totalSeats, availableSeats, occupiedSeatsJson);
        });
    });

    closeModalBtn.addEventListener('click', function () {
        modal.classList.remove('active');
    });

    cancelReservationBtn.addEventListener('click', function () {
        modal.classList.remove('active');
    });

    reservationForm.addEventListener('submit', function (e) {
        if (selectedSeats.length < 1) {
            e.preventDefault();
            alert('Wybierz przynajmniej jedno miejsce.');
            return;
        }

        selectedSeatsInput.value = selectedSeats.join(',');
    });

    modal.addEventListener('click', function (e) {
        if (e.target === modal) {
            modal.classList.remove('active');
        }
    });

    (function () {
        const toast = document.getElementById('toastNotification');
        if (!toast) return;

        const closeBtn = document.getElementById('closeToast');
        let toastTimer = null;

        function hideToast() {
            if (!toast) return;
            toast.classList.add('toast-hide');

            setTimeout(function () {
                if (toast && toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, 300);
        }

        toastTimer = setTimeout(function () {
            hideToast();
        }, 10000);

        if (closeBtn) {
            closeBtn.addEventListener('click', function () {
                clearTimeout(toastTimer);
                hideToast();
            });
        }
    })();

    if (openCreateEventModal && createEventModal) {
        openCreateEventModal.addEventListener('click', function () {
            createEventModal.classList.add('active');
        });
    }

    if (closeCreateEventModal && createEventModal) {
        closeCreateEventModal.addEventListener('click', function () {
            createEventModal.classList.remove('active');
        });
    }

    if (cancelCreateEventBtn && createEventModal) {
        cancelCreateEventBtn.addEventListener('click', function () {
            createEventModal.classList.remove('active');
        });
    }

    if (createEventModal) {
        createEventModal.addEventListener('click', function (e) {
            if (e.target === createEventModal) {
                createEventModal.classList.remove('active');
            }
        });
    }

    document.querySelectorAll('.open-edit-event-modal').forEach(button => {
        button.addEventListener('click', function () {
            editEventId.value = this.dataset.eventId;
            editEventName.value = this.dataset.eventName;
            editEventDescription.value = this.dataset.eventDescription;
            editEventStartAt.value = this.dataset.eventStartAt;
            editEventDuration.value = this.dataset.eventDuration;
            editEventTotalSeats.value = this.dataset.eventTotalSeats;
            editEventStatus.value = this.dataset.eventStatus;

            editEventModal.classList.add('active');
        });
    });

    if (closeEditEventModal && editEventModal) {
        closeEditEventModal.addEventListener('click', function () {
            editEventModal.classList.remove('active');
        });
    }

    if (cancelEditEventBtn && editEventModal) {
        cancelEditEventBtn.addEventListener('click', function () {
            editEventModal.classList.remove('active');
        });
    }

    if (editEventModal) {
        editEventModal.addEventListener('click', function (e) {
            if (e.target === editEventModal) {
                editEventModal.classList.remove('active');
            }
        });
    }

    function parseAdminOccupiedSeats(jsonString) {
        try {
            const parsed = JSON.parse(jsonString || '[]');
            return Array.isArray(parsed) ? parsed : [];
        } catch (e) {
            return [];
        }
    }

    function renderAdminSeats() {
        if (!manageSeatMap) return;

        manageSeatMap.innerHTML = '';
        const occupiedSeatNumbers = adminOccupiedSeats.map(seat => parseInt(seat.seat_number, 10));

        for (let i = 1; i <= adminCurrentTotalSeats; i++) {
            const seat = document.createElement('div');
            seat.classList.add('seat');
            seat.dataset.seatNumber = i;
            seat.title = 'Miejsce ' + i;
            seat.textContent = i;

            const occupiedSeat = adminOccupiedSeats.find(item => parseInt(item.seat_number, 10) === i);
            const isOccupied = !!occupiedSeat;
            const isMine = isOccupied && currentUserId !== null && parseInt(occupiedSeat.user_id, 10) === currentUserId;
            const isSelected = adminSelectedSeats.includes(i);

            if (isOccupied) {
                seat.classList.add('occupied');
            }

            if (isSelected) {
                seat.classList.add('selected');
            }

            if (isOccupied && isSelected) {
                seat.classList.add('selected-for-release');
            }

            if (isMine) {
                seat.classList.add('mine');
            }

            seat.addEventListener('click', function () {
                if (isSelected) {
                    adminSelectedSeats = adminSelectedSeats.filter(num => num !== i);
                } else {
                    adminSelectedSeats.push(i);
                }

                updateAdminSelectedSeatsInfo();
                renderAdminSeats();
            });

            manageSeatMap.appendChild(seat);
        }
    }

    function updateAdminSelectedSeatsInfo() {
        const sortedSeats = [...adminSelectedSeats].sort((a, b) => a - b);
        manageSelectedSeatsCount.textContent = sortedSeats.length;
        manageSelectedSeatsList.textContent = sortedSeats.length ? sortedSeats.join(', ') : 'brak';
        manageSeatsSelectedSeatsInput.value = sortedSeats.join(',');
    }

    function openManageSeatsModal(eventName, eventId, totalSeats, occupiedSeatsJson) {
        adminSelectedSeats = [];
        adminCurrentTotalSeats = totalSeats;
        adminOccupiedSeats = parseAdminOccupiedSeats(occupiedSeatsJson);

        manageSeatsModalTitle.textContent = 'Zarządzanie miejscami: ' + eventName;
        manageSeatsEventId.value = eventId;
        manageSeatsSelectedSeatsInput.value = '';
        manageSeatsActionInput.value = 'reserve';

        updateAdminSelectedSeatsInfo();
        renderAdminSeats();

        manageSeatsModal.classList.add('active');
    }

    document.querySelectorAll('.open-manage-seats-modal').forEach(button => {
        button.addEventListener('click', function () {
            const eventName = this.dataset.eventName;
            const eventId = this.dataset.eventId;
            const totalSeats = parseInt(this.dataset.totalSeats, 10);
            const occupiedSeatsJson = this.dataset.occupiedSeats || '[]';

            openManageSeatsModal(eventName, eventId, totalSeats, occupiedSeatsJson);
        });
    });

    if (closeManageSeatsModal && manageSeatsModal) {
        closeManageSeatsModal.addEventListener('click', function () {
            manageSeatsModal.classList.remove('active');
        });
    }

    if (cancelManageSeatsBtn && manageSeatsModal) {
        cancelManageSeatsBtn.addEventListener('click', function () {
            manageSeatsModal.classList.remove('active');
        });
    }

    if (manageSeatsModal) {
        manageSeatsModal.addEventListener('click', function (e) {
            if (e.target === manageSeatsModal) {
                manageSeatsModal.classList.remove('active');
            }
        });
    }

    if (releaseManagedSeatsBtn) {
        releaseManagedSeatsBtn.addEventListener('click', function () {
            if (!adminSelectedSeats.length) {
                alert('Wybierz miejsca do zwolnienia.');
                return;
            }

            manageSeatsActionInput.value = 'release';
            document.getElementById('manageSeatsForm').submit();
        });
    }

    if (reserveManagedSeatsBtn) {
        reserveManagedSeatsBtn.addEventListener('click', function () {
            manageSeatsActionInput.value = 'reserve';
        });
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
});