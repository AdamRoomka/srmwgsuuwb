window.App = window.App || {};

document.addEventListener('app:ready', function () {
    const reservationButtons = document.querySelectorAll('.open-reservation-modal');
    const publicReservationPreviewButtons = document.querySelectorAll('.open-public-reservation-preview');
    const manageButtons = document.querySelectorAll('.open-manage-seats-modal');
    const reservationsListButtons = document.querySelectorAll('.open-reservations-list-modal');

    const currentUserId = App.currentUserId ?? null;

    const seatMap = document.getElementById('seatMap');
    const manageSeatMap = document.getElementById('manageSeatMap');

    const reservationModal = document.getElementById('reservationModal');
    const closeReservationModal = document.getElementById('closeReservationModal');
    const cancelReservationBtn = document.getElementById('cancelReservationBtn');

    const publicReservationPreviewModal = document.getElementById('publicReservationPreviewModal');
    const publicReservationPreviewTitle = document.getElementById('publicReservationPreviewTitle');
    const publicReservationPreviewSeatMap = document.getElementById('publicReservationPreviewSeatMap');
    const closePublicReservationPreviewModal = document.getElementById('closePublicReservationPreviewModal');
    const closePublicReservationPreviewBtn = document.getElementById('closePublicReservationPreviewBtn');

    const manageSeatsModal = document.getElementById('manageSeatsModal');
    const closeManageSeatsModal = document.getElementById('closeManageSeatsModal');
    const cancelManageSeatsBtn = document.getElementById('cancelManageSeatsBtn');

    const selectedSeatsInput = document.getElementById('selectedSeatsInput');
    const selectedSeatsList = document.getElementById('selectedSeatsList');
    const selectedSeatsCount = document.getElementById('selectedSeatsCount');
    const reserveEventId = document.getElementById('reserveEventId');

    const manageSeatsEventId = document.getElementById('manageSeatsEventId');
    const manageSeatsUserSelect = document.getElementById('manageSeatsUserSelect');
    const manageSeatsSelectedSeatsInput = document.getElementById('manageSeatsSelectedSeatsInput');
    const manageSelectedSeatsList = document.getElementById('manageSelectedSeatsList');
    const manageSelectedSeatsCount = document.getElementById('manageSelectedSeatsCount');
    const manageSeatsActionInput = document.getElementById('manageSeatsActionInput');
    const reserveManagedSeatsBtn = document.getElementById('reserveManagedSeatsBtn');
    const releaseManagedSeatsBtn = document.getElementById('releaseManagedSeatsBtn');
    const manageSeatsModalTitle = document.getElementById('manageSeatsModalTitle');

    const reservationsListModal = document.getElementById('reservationsListModal');
    const closeReservationsListModal = document.getElementById('closeReservationsListModal');
    const closeReservationsListBtn = document.getElementById('closeReservationsListBtn');
    const reservationsList = document.getElementById('reservationsList');
    const reservationsListModalTitle = document.getElementById('reservationsListModalTitle');

    const eventInfoModal = document.getElementById('eventInfoModal');
    const closeEventInfoModal = document.getElementById('closeEventInfoModal');

    const eventInfoTitle = document.getElementById('eventInfoTitle');
    const eventInfoDescription = document.getElementById('eventInfoDescription');
    const eventInfoDate = document.getElementById('eventInfoDate');
    const eventInfoDuration = document.getElementById('eventInfoDuration');
    const eventInfoSeats = document.getElementById('eventInfoSeats');
    const eventInfoAvailable = document.getElementById('eventInfoAvailable');

    const goToReservationSeats = document.getElementById('goToReservationSeats');
    const goToPreviewSeats = document.getElementById('goToPreviewSeats');

    let pendingReservationData = null;

    let selectedSeats = [];
    let occupiedSeats = [];
    let currentTotalSeats = 0;
    let originalOwnSeats = []; // Track original user seats to show changes

    let adminSelectedSeats = [];
    let adminOriginalSeats = [];
    let adminReleaseSeats = [];
    let adminOccupiedSeats = [];
    let adminCurrentTotalSeats = 0;
    let adminHighlightedUserId = null;

    const hallLayout = [
        { type: 'row', blocks: [17] },
        { type: 'row', blocks: [17] },
        { type: 'row', blocks: [17] },
        { type: 'row', blocks: [17] },
        { type: 'row', blocks: [15] },
        { type: 'row', blocks: [7] }
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

    function getOwnOccupiedSeatsForEvent(occupiedSeatsData, userId) {
        if (userId === null) return [];

        return occupiedSeatsData
            .filter(item => item.user_id !== null && parseInt(item.user_id, 10) === userId)
            .map(item => parseInt(item.seat_number, 10))
            .filter(Number.isFinite);
    }

    function updateAdminSelectedSeatsInfo() {
        if (!manageSelectedSeatsList || !manageSelectedSeatsCount || !manageSeatsSelectedSeatsInput) return;

        const sorted = [...adminSelectedSeats].sort((a, b) => a - b);
        manageSelectedSeatsList.textContent = sorted.length ? sorted.join(', ') : 'brak';
        manageSelectedSeatsCount.textContent = sorted.length;
        manageSeatsSelectedSeatsInput.value = sorted.join(',');
    }

    function renderSeatGrid(target, structure, occupiedSource, selectedSource, isAdminMode = false, highlightedUserId = currentUserId, allowSelection = true) {
        if (!target) return;

        target.classList.toggle('readonly-seat-map', !allowSelection);
        target.innerHTML = '';

        const hallBody = document.createElement('div');
        hallBody.className = 'hall-body';

        structure.forEach(rowData => {
            const row = document.createElement('div');
            row.className = 'hall-row-grid';
            row.style.gridTemplateColumns = `repeat(${rowData.slots.length}, 38px)`;

            rowData.slots.forEach(slot => {

                const seatNumber = slot.seatNumber;
                const seat = document.createElement('div');
                seat.className = 'seat';
                seat.dataset.seatNumber = seatNumber;
                seat.textContent = seatNumber;

                const occupiedSeat = occupiedSource.find(item => {

                    const parsedSeatNumber = parseInt(item.seat_number, 10);
                    return parsedSeatNumber === seatNumber;
                }) || null;
                const isOccupied = !!occupiedSeat;
                const activeHighlightedUserId = isAdminMode ? highlightedUserId : currentUserId;
                const isMine = isOccupied && occupiedSeat.user_id !== null && activeHighlightedUserId !== null && parseInt(occupiedSeat.user_id, 10) === activeHighlightedUserId;
                const isSelected = selectedSource.includes(seatNumber);

                if (occupiedSeat) {
                    if (occupiedSeat.user_id && occupiedSeat.first_name && occupiedSeat.last_name) {
                        const seatLabel = occupiedSeat.first_name.charAt(0).toUpperCase() + occupiedSeat.last_name;
                        seat.dataset.userInitials = seatLabel;
                        seat.title = seatLabel;
                    } else {
                        seat.dataset.userInitials = 'Zarezerwowano';
                        seat.title = 'Zarezerwowano';
                    }
                }

                if (isOccupied) seat.classList.add('occupied');
                if (isMine) seat.classList.add('mine');
                if (isSelected) seat.classList.add('selected');

                // Mark new free seats selected for addition
                if (!isAdminMode && !isOccupied && isSelected) {
                    seat.classList.add('adding');
                }

                // Mark new free seats selected for addition in admin mode
                if (isAdminMode && !isOccupied && isSelected) {
                    seat.classList.add('adding');
                }

                if (allowSelection) {
                    seat.addEventListener('click', function () {
                        if (!isAdminMode && isOccupied && !isMine) return;

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
                }

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
            true,
            adminHighlightedUserId
        );
    }

    function renderReservationsList(occupiedSeatsData, totalSeatsFromButton = 0) {

        if (!reservationsList) {
            console.error('reservationsList element is null!');
            return;
        }

        reservationsList.innerHTML = '';

        if (occupiedSeatsData.length === 0) {
            console.log('No occupied seats');
            reservationsList.innerHTML = '<p style="text-align: center; color: #666;">Brak rezerwacji</p>';
            return;
        }

        // Group by user
        const usersMap = {};
        occupiedSeatsData.forEach(seat => {
            const userId = seat.user_id ?? 'null';
            const userName = seat.user_id && seat.first_name && seat.last_name
                ? seat.first_name + ' ' + seat.last_name
                : 'Zarezerwowano';
            if (!usersMap[userId]) {
                usersMap[userId] = {
                    name: userName,
                    seats: []
                };
            }
            usersMap[userId].seats.push(parseInt(seat.seat_number, 10));
        });

        console.log('Users map:', usersMap);

        // Create list
        const list = document.createElement('div');
        list.className = 'reservations-items';

        Object.keys(usersMap).forEach(userId => {
            const userData = usersMap[userId];
            const item = document.createElement('div');
            item.className = 'reservation-item';

            const seatsText = userData.seats
                .filter(Number.isFinite)
                .sort((a, b) => a - b)
                .join(', ');
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

    document.querySelectorAll('.open-event-info-modal').forEach(button => {

        button.addEventListener('click', function () {


            pendingReservationData = this.dataset;

            eventInfoTitle.textContent = this.dataset.eventName;
            eventInfoDescription.textContent = this.dataset.eventDescription;
            eventInfoDate.textContent = this.dataset.eventDate;
            eventInfoDuration.textContent = this.dataset.eventDuration + ' min';
            eventInfoSeats.textContent = this.dataset.totalSeats;
            eventInfoAvailable.textContent = this.dataset.availableSeats;

            const isLoggedIn = currentUserId !== null;

            if (isLoggedIn) {

                goToReservationSeats.style.display = 'flex';
                goToPreviewSeats.style.display = 'none';

            } else {

                goToReservationSeats.style.display = 'none';
                goToPreviewSeats.style.display = 'flex';

            }

            document.querySelector(".container").style.overflow = "hidden";
            App.openModal(eventInfoModal);
        });

    });

    if (goToReservationSeats) {

        goToReservationSeats.addEventListener('click', function () {

            if (!pendingReservationData) return;

            currentTotalSeats = parseInt(pendingReservationData.totalSeats || '0', 10);

            occupiedSeats = App.parseJsonSafely(
                pendingReservationData.occupiedSeats || '[]'
            );

            selectedSeats = getOwnOccupiedSeatsForEvent(
                occupiedSeats,
                currentUserId
            );

            originalOwnSeats = getOwnOccupiedSeatsForEvent(
                occupiedSeats,
                currentUserId
            );

            if (reserveEventId) {
                reserveEventId.value = pendingReservationData.eventId || '';
            }

            updateSelectedSeatsInfo();

            renderSeats();

            document.querySelector(".container").style.overflow = '';
            App.closeModal(eventInfoModal);

            document.querySelector(".container").style.overflow = 'hidden';
            App.openModal(reservationModal);

        });

    }

    if (goToPreviewSeats) {

        goToPreviewSeats.addEventListener('click', function () {

            if (!pendingReservationData) return;

            const totalSeats = parseInt(
                pendingReservationData.totalSeats || '0',
                10
            );

            const previewOccupiedSeats = App.parseJsonSafely(
                pendingReservationData.occupiedSeats || '[]'
            );

            const eventName = pendingReservationData.eventName || '';

            if (publicReservationPreviewTitle) {
                publicReservationPreviewTitle.textContent =
                    'Podgląd sali';
            }

            renderSeatGrid(
                publicReservationPreviewSeatMap,
                buildHallStructure(totalSeats),
                previewOccupiedSeats,
                [],
                false,
                currentUserId,
                false
            );

            document.querySelector(".container").style.overflow = '';
            App.closeModal(eventInfoModal);

            document.querySelector(".container").style.overflow = 'hidden';
            App.openModal(publicReservationPreviewModal);

        });

    }

    if (closeEventInfoModal && eventInfoModal) {

        closeEventInfoModal.addEventListener('click', function () {
            document.querySelector(".container").style.overflow = '';
            App.closeModal(eventInfoModal);
        });

    }

    if (eventInfoModal) {

        eventInfoModal.addEventListener('click', function (e) {

            if (e.target === eventInfoModal) {
                document.querySelector(".container").style.overflow = '';
                App.closeModal(eventInfoModal);
            }

        });

    }

    publicReservationPreviewButtons.forEach(button => {
        button.addEventListener('click', function () {
            const totalSeats = parseInt(this.dataset.totalSeats || '0', 10);
            const previewOccupiedSeats = App.parseJsonSafely(this.dataset.occupiedSeats || '[]');
            const eventName = this.dataset.eventName || '';

            if (publicReservationPreviewTitle) {
                publicReservationPreviewTitle.textContent = 'Podgląd sali: ' + eventName;
            }

            renderSeatGrid(
                publicReservationPreviewSeatMap,
                buildHallStructure(totalSeats),
                previewOccupiedSeats,
                [],
                false,
                currentUserId,
                false
            );

            document.querySelector(".container").style.overflow = 'hidden';
            App.openModal(publicReservationPreviewModal);
        });
    });

    document.querySelectorAll('.open-manage-seats-modal').forEach(button => {
        button.addEventListener('click', function () {
            adminCurrentTotalSeats = parseInt(this.dataset.totalSeats || '0', 10);
            adminOccupiedSeats = App.parseJsonSafely(this.dataset.occupiedSeats || '[]');
            adminHighlightedUserId = manageSeatsUserSelect ? parseInt(manageSeatsUserSelect.value || String(currentUserId ?? ''), 10) || null : currentUserId;
            adminOriginalSeats = getOwnOccupiedSeatsForEvent(adminOccupiedSeats, adminHighlightedUserId);
            adminSelectedSeats = [...adminOriginalSeats];
            adminReleaseSeats = [];

            if (manageSeatsEventId) manageSeatsEventId.value = this.dataset.eventId || '';
            if (manageSeatsModalTitle) manageSeatsModalTitle.textContent = 'Zarządzanie miejscami: ' + (this.dataset.eventName || '');

            if (manageSeatsActionInput) manageSeatsActionInput.value = 'reserve';

            updateAdminSelectedSeatsInfo();
            renderAdminSeats();
            document.querySelector(".container").style.overflow = 'hidden';
            App.openModal(manageSeatsModal);
        });
    });

    if (manageSeatsUserSelect) {
        manageSeatsUserSelect.addEventListener('change', function () {
            const parsedUserId = parseInt(this.value || '', 10);
            adminHighlightedUserId = Number.isFinite(parsedUserId) ? parsedUserId : null;
            adminOriginalSeats = getOwnOccupiedSeatsForEvent(adminOccupiedSeats, adminHighlightedUserId);
            adminSelectedSeats = [...adminOriginalSeats];
            adminReleaseSeats = [];
            updateAdminSelectedSeatsInfo();
            renderAdminSeats();
        });
    }

    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.open-reservations-list-modal');
        if (!btn) return;

        const occupiedSeatsData = App.parseJsonSafely(btn.dataset.occupiedSeats || '[]');
        const totalSeatsFromButton = parseInt(btn.dataset.totalSeats || '0', 10);
        const eventName = btn.dataset.eventName || '';


        if (!reservationsListModal) {
            console.error('reservationsListModal not found!');
            alert('BŁĄD: Modal nie został znaleziony. Zaloguj się ponownie.');
            return;
        }

        if (reservationsListModalTitle) {
            reservationsListModalTitle.textContent = 'Rezerwacje: ' + eventName;
        }

        renderReservationsList(occupiedSeatsData, totalSeatsFromButton);

        document.querySelector(".container").style.overflow = 'hidden';
        App.openModal(reservationsListModal);
    });

    // Keep the old forEach approach as backup
    document.querySelectorAll('.open-reservations-list-modal').forEach(button => {
        button.addEventListener('click', function (e) {
            const occupiedSeatsData = App.parseJsonSafely(this.dataset.occupiedSeats || '[]');
            const totalSeatsFromButton = parseInt(this.dataset.totalSeats || '0', 10);
            const eventName = this.dataset.eventName || '';

            if (!reservationsListModal) {
                console.error('reservationsListModal not found!');
                alert('BŁĄD: Modal nie został znaleziony. Zaloguj się ponownie.');
                return;
            }

            if (reservationsListModalTitle) {
                reservationsListModalTitle.textContent = 'Rezerwacje: ' + eventName;
            }

            renderReservationsList(occupiedSeatsData, totalSeatsFromButton);

            document.querySelector(".container").style.overflow = 'hidden';
            App.openModal(reservationsListModal);
        });
    });

    if (closeReservationModal && reservationModal) {
        closeReservationModal.addEventListener('click', function () {
            document.querySelector(".container").style.overflow = '';
            App.closeModal(reservationModal);
        });
    }

    if (cancelReservationBtn && reservationModal) {
        cancelReservationBtn.addEventListener('click', function () {
            document.querySelector(".container").style.overflow = '';
            App.closeModal(reservationModal);
        });
    }

    if (reservationModal) {
        reservationModal.addEventListener('click', function (e) {
            if (e.target === reservationModal) {
                document.querySelector(".container").style.overflow = '';
                App.closeModal(reservationModal);
            }
        });
    }

    if (closePublicReservationPreviewModal && publicReservationPreviewModal) {
        closePublicReservationPreviewModal.addEventListener('click', function () {
            document.querySelector(".container").style.overflow = '';
            App.closeModal(publicReservationPreviewModal);
        });
    }

    if (closePublicReservationPreviewBtn && publicReservationPreviewModal) {
        closePublicReservationPreviewBtn.addEventListener('click', function () {
            document.querySelector(".container").style.overflow = '';
            App.closeModal(publicReservationPreviewModal);
        });
    }

    if (publicReservationPreviewModal) {
        publicReservationPreviewModal.addEventListener('click', function (e) {
            if (e.target === publicReservationPreviewModal) {
                document.querySelector(".container").style.overflow = '';
                App.closeModal(publicReservationPreviewModal);
            }
        });
    }

    if (closeManageSeatsModal && manageSeatsModal) {
        closeManageSeatsModal.addEventListener('click', function () {
            document.querySelector(".container").style.overflow = '';
            App.closeModal(manageSeatsModal);
        });
    }

    if (cancelManageSeatsBtn && manageSeatsModal) {
        cancelManageSeatsBtn.addEventListener('click', function () {
            document.querySelector(".container").style.overflow = '';
            App.closeModal(manageSeatsModal);
        });
    }

    if (manageSeatsModal) {
        manageSeatsModal.addEventListener('click', function (e) {
            if (e.target === manageSeatsModal) {
                document.querySelector(".container").style.overflow = '';
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
            console.log('Admin original seats:', adminOriginalSeats);
            console.log('Admin selected seats:', adminSelectedSeats);
            const originalSet = new Set(adminOriginalSeats);
            const selectedSet = new Set(adminSelectedSeats);

            adminReleaseSeats = [
                ...adminOriginalSeats.filter(seatNumber => !selectedSet.has(seatNumber)),
                ...adminSelectedSeats.filter(seatNumber => !originalSet.has(seatNumber))
            ];

            adminReleaseSeats = [...new Set(adminReleaseSeats)];

            if (!adminReleaseSeats.length) {
                alert('Wybierz miejsca do zwolnienia.');
                return;
            }

            if (manageSeatsSelectedSeatsInput) {
                manageSeatsSelectedSeatsInput.value = adminReleaseSeats.sort((a, b) => a - b).join(',');
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

    if (closeReservationsListModal && reservationsListModal) {
        closeReservationsListModal.addEventListener('click', function () {
            document.querySelector(".container").style.overflow = '';
            App.closeModal(reservationsListModal);
        });
    }

    if (closeReservationsListBtn && reservationsListModal) {
        closeReservationsListBtn.addEventListener('click', function () {
            document.querySelector(".container").style.overflow = '';
            App.closeModal(reservationsListModal);
        });
    }

    if (reservationsListModal) {
        reservationsListModal.addEventListener('click', function (e) {
            if (e.target === reservationsListModal) {
                document.querySelector(".container").style.overflow = '';
                App.closeModal(reservationsListModal);
            }
        });
    }
});