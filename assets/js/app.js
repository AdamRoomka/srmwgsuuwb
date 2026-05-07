window.App = window.App || {};

document.addEventListener('DOMContentLoaded', function () {
    App.currentUserId = window.appData?.currentUserId ?? null;

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

    if (createEventModal) {
        createEventModal.addEventListener('click', function (e) {
            if (e.target === createEventModal) {
                App.closeModal(createEventModal);
            }
        });
    }
});