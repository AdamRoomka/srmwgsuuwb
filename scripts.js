document.addEventListener('DOMContentLoaded', function () {
            const modal = document.getElementById('reservationModal');
            const closeModalBtn = document.getElementById('closeReservationModal');
            const cancelReservationBtn = document.getElementById('cancelReservationBtn');
            const seatMap = document.getElementById('seatMap');
            const seatCountInput = document.getElementById('seatCountInput');
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

            let selectedSeats = [];
            let occupiedSeats = [];
            let maxSelectableSeats = 1;
            let currentEventId = null;
            let currentTotalSeats = 0;

            function parseOccupiedSeats(jsonString) {
                try {
                    const parsed = JSON.parse(jsonString || '[]');
                    return Array.isArray(parsed) ? parsed : [];
                } catch (e) {
                    return [];
                }
            }

            function renderSeats() {
                seatMap.innerHTML = '';
                const occupiedSeatNumbers = occupiedSeats.map(seat => parseInt(seat.seat_number, 10));

                for (let i = 1; i <= currentTotalSeats; i++) {
                    const seat = document.createElement('div');
                    seat.classList.add('seat');
                    seat.dataset.seatNumber = i;
                    seat.title = 'Miejsce ' + i;
                    seat.textContent = i;

                    if (occupiedSeatNumbers.includes(i)) {
                        seat.classList.add('occupied');
                    }

                    if (selectedSeats.includes(i)) {
                        seat.classList.add('selected');
                    }

                    seat.addEventListener('click', function () {
                        if (occupiedSeatNumbers.includes(i)) {
                            return;
                        }

                        if (selectedSeats.includes(i)) {
                            selectedSeats = selectedSeats.filter(num => num !== i);
                        } else {
                            if (selectedSeats.length >= maxSelectableSeats) {
                                alert('Możesz wybrać maksymalnie ' + maxSelectableSeats + ' miejsc.');
                                return;
                            }
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
                maxSelectableSeats = 1;

                modalEventTitle.textContent = 'Rezerwacja: ' + eventName;
                seatCountInput.value = 1;
                seatCountInput.min = 1;
                seatCountInput.max = Math.max(1, availableSeats);
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

            seatCountInput.addEventListener('input', function () {
                let value = parseInt(this.value, 10) || 1;
                const realMax = Math.max(1, parseInt(this.max, 10) || 1);

                if (value < 1) value = 1;
                if (value > realMax) value = realMax;

                this.value = value;
                maxSelectableSeats = value;

                if (selectedSeats.length > maxSelectableSeats) {
                    selectedSeats = selectedSeats.slice(0, maxSelectableSeats);
                }

                updateSelectedSeatsInfo();
                renderSeats();
            });

            closeModalBtn.addEventListener('click', function () {
                modal.classList.remove('active');
            });

            cancelReservationBtn.addEventListener('click', function () {
                modal.classList.remove('active');
            });

            reservationForm.addEventListener('submit', function (e) {
                if (selectedSeats.length !== maxSelectableSeats) {
                    e.preventDefault();
                    alert('Wybierz dokładnie ' + maxSelectableSeats + ' miejsc.');
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
        });