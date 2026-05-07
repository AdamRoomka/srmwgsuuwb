window.App = window.App || {};

document.addEventListener('app:ready', function () {
    const currentUserId = App.currentUserId ?? null;

    const seatMap = document.getElementById('seatMap');
    const manageSeatMap = document.getElementById('manageSeatMap');

    const reservationModal = document.getElementById('reservationModal');
    const closeReservationModal = document.getElementById('closeReservationModal');
    const cancelReservationBtn = document.getElementById('cancelReservationBtn');

    const manageSeatsModal = document.getElementById('manageSeatsModal');
    const closeManageSeatsModal = document.getElementById('closeManageSeatsModal');
    const cancelManageSeatsBtn = document.getElementById('cancelManageSeatsBtn');

    const selectedSeatsInput = document.getElementById('selectedSeatsInput');
    const selectedSeatsList = document.getElementById('selectedSeatsList');
    const selectedSeatsCount = document.getElementById('selectedSeatsCount');
    const reserveEventId = document.getElementById('reserveEventId');

    const manageSeatsEventId = document.getElementById('manageSeatsEventId');
    const manageSeatsSelectedSeatsInput = document.getElementById('manageSeatsSelectedSeatsInput');
    const manageSelectedSeatsList = document.getElementById('manageSelectedSeatsList');
    const manageSelectedSeatsCount = document.getElementById('manageSelectedSeatsCount');
    const manageSeatsActionInput = document.getElementById('manageSeatsActionInput');
    const reserveManagedSeatsBtn = document.getElementById('reserveManagedSeatsBtn');
    const releaseManagedSeatsBtn = document.getElementById('releaseManagedSeatsBtn');
    const manageSeatsModalTitle = document.getElementById('manageSeatsModalTitle');

    let selectedSeats = [];
    let occupiedSeats = [];
    let currentTotalSeats = 0;

    let adminSelectedSeats = [];
    let adminOccupiedSeats = [];
    let adminCurrentTotalSeats = 0;

    const hallLayout = [
        { type: 'row', blocks: [4, 2, 3, 2, 3, 2, 7] },
        { type: 'row', blocks: [4, 2, 4, 1, 3, 2, 6] },
        { type: 'row', blocks: [4, 2, 4, 1, 3, 2, 6] },
        { type: 'row', blocks: [4, 2, 4, 1, 3, 2, 6] },
        { type: 'row', blocks: [4, 2, 4, 1, 3, 2, 4] },
        { type: 'row', blocks: [0, 7, 3, 1, 0, 5, 4] }
    ];

    function buildHallStructure(totalSeats) {
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

        return rows;
    }

    function updateSelectedSeatsInfo() {
        if (!selectedSeatsList || !selectedSeatsCount || !selectedSeatsInput) return;

        const sorted = [...selectedSeats].sort((a, b) => a - b);
        selectedSeatsList.textContent = sorted.length ? sorted.join(', ') : 'brak';
        selectedSeatsCount.textContent = sorted.length;
        selectedSeatsInput.value = sorted.join(',');
    }

    function updateAdminSelectedSeatsInfo() {
        if (!manageSelectedSeatsList || !manageSelectedSeatsCount || !manageSeatsSelectedSeatsInput) return;

        const sorted = [...adminSelectedSeats].sort((a, b) => a - b);
        manageSelectedSeatsList.textContent = sorted.length ? sorted.join(', ') : 'brak';
        manageSelectedSeatsCount.textContent = sorted.length;
        manageSeatsSelectedSeatsInput.value = sorted.join(',');
    }

    function renderSeatGrid(target, structure, occupiedSource, selectedSource, isAdminMode = false) {
        if (!target) return;

        target.innerHTML = '';

        const hallBody = document.createElement('div');
        hallBody.className = 'hall-body';

        structure.forEach(rowData => {
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

                const seatNumber = slot.seatNumber;
                const seat = document.createElement('div');
                seat.className = 'seat';
                seat.dataset.seatNumber = seatNumber;
                seat.textContent = seatNumber;

                const occupiedSeat = occupiedSource.find(item => parseInt(item.seat_number, 10) === seatNumber);
                const isOccupied = !!occupiedSeat;
                // console.log(`Rendering seat ${seatNumber}: occupied=${isOccupied}, occupiedSeatUserId=${occupiedSeat ? occupiedSeat.user_id : 'N/A'}, currentUserId=${currentUserId}`);
                const isMine = isOccupied && currentUserId !== null && parseInt(occupiedSeat.user_id, 10) === currentUserId;
                const isSelected = selectedSource.includes(seatNumber);

                if (isOccupied) seat.classList.add('occupied');
                if (isMine) seat.classList.add('mine');
                if (isSelected) seat.classList.add('selected');
                if (isAdminMode && isOccupied && isSelected) seat.classList.add('selected-for-release');

                seat.addEventListener('click', function () {
                    if (!isAdminMode && isOccupied) return;

                    if (isAdminMode) {
                        if (adminSelectedSeats.includes(seatNumber)) {
                            adminSelectedSeats = adminSelectedSeats.filter(num => num !== seatNumber);
                        } else {
                            adminSelectedSeats.push(seatNumber);
                        }
                        updateAdminSelectedSeatsInfo();
                        renderAdminSeats();
                    } else {
                        if (selectedSeats.includes(seatNumber)) {
                            selectedSeats = selectedSeats.filter(num => num !== seatNumber);
                        } else {
                            selectedSeats.push(seatNumber);
                        }
                        updateSelectedSeatsInfo();
                        renderSeats();
                    }
                });

                row.appendChild(seat);
            });

            hallBody.appendChild(row);
        });

        target.appendChild(hallBody);
    }

    function renderSeats() {
        renderSeatGrid(
            seatMap,
            buildHallStructure(currentTotalSeats),
            occupiedSeats,
            selectedSeats,
            false
        );
    }

    function renderAdminSeats() {
        renderSeatGrid(
            manageSeatMap,
            buildHallStructure(adminCurrentTotalSeats),
            adminOccupiedSeats,
            adminSelectedSeats,
            true
        );
    }

    document.querySelectorAll('.open-reservation-modal').forEach(button => {
        button.addEventListener('click', function () {
            selectedSeats = [];
            currentTotalSeats = parseInt(this.dataset.totalSeats || '0', 10);
            occupiedSeats = App.parseJsonSafely(this.dataset.occupiedSeats || '[]');

            if (reserveEventId) reserveEventId.value = this.dataset.eventId || '';
            updateSelectedSeatsInfo();
            renderSeats();
            App.openModal(reservationModal);
        });
    });

    document.querySelectorAll('.open-manage-seats-modal').forEach(button => {
        button.addEventListener('click', function () {
            adminSelectedSeats = [];
            adminCurrentTotalSeats = parseInt(this.dataset.totalSeats || '0', 10);
            adminOccupiedSeats = App.parseJsonSafely(this.dataset.occupiedSeats || '[]');

            if (manageSeatsEventId) manageSeatsEventId.value = this.dataset.eventId || '';
            if (manageSeatsModalTitle) manageSeatsModalTitle.textContent = 'Zarządzanie miejscami: ' + (this.dataset.eventName || '');

            if (manageSeatsActionInput) manageSeatsActionInput.value = 'reserve';

            updateAdminSelectedSeatsInfo();
            renderAdminSeats();
            App.openModal(manageSeatsModal);
        });
    });

    if (closeReservationModal && reservationModal) {
        closeReservationModal.addEventListener('click', function () {
            App.closeModal(reservationModal);
        });
    }

    if (cancelReservationBtn && reservationModal) {
        cancelReservationBtn.addEventListener('click', function () {
            App.closeModal(reservationModal);
        });
    }

    if (reservationModal) {
        reservationModal.addEventListener('click', function (e) {
            if (e.target === reservationModal) {
                App.closeModal(reservationModal);
            }
        });
    }

    if (closeManageSeatsModal && manageSeatsModal) {
        closeManageSeatsModal.addEventListener('click', function () {
            App.closeModal(manageSeatsModal);
        });
    }

    if (cancelManageSeatsBtn && manageSeatsModal) {
        cancelManageSeatsBtn.addEventListener('click', function () {
            App.closeModal(manageSeatsModal);
        });
    }

    if (manageSeatsModal) {
        manageSeatsModal.addEventListener('click', function (e) {
            if (e.target === manageSeatsModal) {
                App.closeModal(manageSeatsModal);
            }
        });
    }

    if (reserveManagedSeatsBtn) {
        reserveManagedSeatsBtn.addEventListener('click', function () {
            if (manageSeatsActionInput) {
                manageSeatsActionInput.value = 'reserve';
            }
        });
    }

    if (releaseManagedSeatsBtn) {
        releaseManagedSeatsBtn.addEventListener('click', function () {
            if (!adminSelectedSeats.length) {
                alert('Wybierz miejsca do zwolnienia.');
                return;
            }

            if (manageSeatsActionInput) {
                manageSeatsActionInput.value = 'release';
            }

            const manageSeatsForm = document.getElementById('manageSeatsForm');
            if (manageSeatsForm) {
                manageSeatsForm.submit();
            }
        });
    }
});