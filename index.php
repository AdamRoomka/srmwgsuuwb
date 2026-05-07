<?php
session_start();

require_once __DIR__ . '/includes/functions.php'; // funkcje

$host = '127.0.0.1';
$dbname = 'uwb_rezerwacje';
$user = 'root';
$password = '';
$port = '3306';

$events = [];
$occupiedSeatsByEvent = [];
$userReservations = [];
$usersForAdminSeatAssignment = [];
$dbError = null;
$loginError = null;
$reservationMessage = $_SESSION['reservationMessage'] ?? '';
$reservationError = $_SESSION['reservationError'] ?? '';
$_SESSION['reservationMessage'] = '';
$_SESSION['reservationError'] = '';
$currentUser = $_SESSION['user'] ?? null;

try {
    $pdo = new PDO(
        "mysql:
            host=$host;
            port=$port;
            dbname=$dbname;
        charset=utf8mb4",
        $user,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );

    if (isset($_GET['logout'])) {
        unset($_SESSION['user']);
        header('Location: index.php');
        exit;
    }

    require_once __DIR__ . '/actions/login.php'; // logowanie
    $currentUser = $_SESSION['user'] ?? null;
    require_once __DIR__ . '/actions/create_event.php'; // dodawanie wydarzenia (tylko dla administratora)
    require_once __DIR__ . '/actions/reserve_seats.php'; // rezerwacja miejsc (tylko dla zalogowanych użytkowników)
    require_once __DIR__ . '/queries/events.php'; // pobieranie wydarzeń i zajętych miejsc
    require_once __DIR__ . '/actions/update_event.php'; // aktualizacja wydarzenia (tylko dla administratora)
    require_once __DIR__ . '/actions/delete_event.php'; // usuwanie wydarzenia (tylko dla administratora)
    require_once __DIR__ . '/actions/manage_seats.php'; // zarządzanie miejscami (tylko dla administratora)
    require_once __DIR__ . '/actions/cancel_own_reservation.php'; // anulowanie własnej rezerwacji (tylko dla zalogowanych użytkowników)

    $userListStmt = $pdo->query("
        SELECT id, first_name, last_name, email
        FROM users
        ORDER BY first_name ASC, last_name ASC, email ASC
    ");
    $usersForAdminSeatAssignment = $userListStmt->fetchAll();

    if ($currentUser) {
        $userReservationsStmt = $pdo->prepare("
        SELECT 
            e.id AS event_id,
            e.name AS event_name,
            e.start_at,
            GROUP_CONCAT(os.seat_number ORDER BY os.seat_number ASC SEPARATOR ', ') AS seat_numbers
        FROM occupied_seats os
        JOIN events e ON e.id = os.event_id
        WHERE os.user_id = :user_id
          AND os.status = 'AKTYWNA'
        GROUP BY e.id, e.name, e.start_at
        ORDER BY e.start_at ASC
    ");
        $userReservationsStmt->execute([
            'user_id' => $currentUser['id']
        ]);
        $userReservations = $userReservationsStmt->fetchAll();
    }

} catch (PDOException $e) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $dbError = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Rezerwacji - Aula Główna</title>
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/seat-map.css">
    <script>
        window.currentUserId = <?php echo $currentUser ? (int) $currentUser['id'] : 'null'; ?>;
    </script>
    <script src="assets/js/app.js"></script>
    <script src="assets/js/seat-map.js"></script>
</head>

<body>
    <header>
        <div class="header-container">
            <div class="header-left">
                <a href="/" class="logo-box">
                    <img src="./IMG/uwb_wilno_logo.png" alt="Filia UwB w Wilnie" class="logo" />
                </a>
                <h1>System Rezerwacji miejsc w głównej sali Uniwersytetu UWB</h1>
            </div>
        </div>
        <?php require __DIR__ . '/views/partials/auth_section.php'; ?>
        <? // logowanie i wyświetlanie informacji o aktualnym użytkowniku ?>
    </header>

    <div class="container">
        <div class="view active">
            <h2>Wybierz wydarzenie</h2>
            <?php if ($currentUser): ?>
                <div class="info-box">
                    <h3>Moje rezerwacje</h3>

                    <?php if (!empty($userReservations)): ?>
                        <div class="my-reservations-list">
                            <?php foreach ($userReservations as $reservation): ?>
                                <div class="event-card">
                                    <h4><?php echo htmlspecialchars($reservation['event_name']); ?></h4>
                                    <p><strong>Data:</strong>
                                        <?php echo htmlspecialchars(formatDatePl($reservation['start_at'])); ?></p>
                                    <p><strong>Moje miejsca:</strong> <?php echo htmlspecialchars($reservation['seat_numbers']); ?>
                                    </p>

                                    <form method="post"
                                        onsubmit="return confirm('Czy na pewno chcesz usunąć swoją rezerwację dla tego wydarzenia?');">
                                        <input type="hidden" name="cancel_own_reservation" value="1">
                                        <input type="hidden" name="event_id" value="<?php echo (int) $reservation['event_id']; ?>">
                                        <button type="submit" class="btn btn-secondary">Usuń moją rezerwację</button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p>Nie masz jeszcze żadnych rezerwacji przypisanych do Twojego konta.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <?php if ($dbError): ?>
                <div class="error-box">
                    <h3>Błąd połączenia z bazą danych</h3>
                    <p>Sprawdź konfigurację połączenia oraz czy baza <strong>uwb_rezerwacje</strong> istnieje.</p>
                    <p><?php echo htmlspecialchars($dbError); ?></p>
                </div>
            <?php elseif (empty($events)): ?>
                <div class="info-box">
                    <p>Brak wydarzeń w bazie danych.</p>
                </div>
            <?php else: ?>
                <?php require __DIR__ . '/views/partials/messages.php'; ?>
                <? // wyświetlanie komunikatów o rezerwacji lub błędach rezerwacji ?>

                <?php if ($currentUser && $currentUser['role'] === 'ADMINISTRATOR'): ?>
                    <div style="margin: 20px 0;">
                        <button type="button" class="btn btn-primary" id="openCreateEventModal">
                            Dodaj nowe wydarzenie
                        </button>
                    </div>
                <?php endif; ?>



                <?php require __DIR__ . '/views/partials/create_event_modal.php'; ?>
                <? // modal dodawania wydarzenia (tylko dla administratora) ?>

                <div class="events-grid">
                    <?php require __DIR__ . '/views/partials/event_card.php'; ?>
                    <? // karty wydarzeń z przyciskiem rezerwacji (tylko dla zalogowanych użytkowników) ?>
                </div>

                <?php require __DIR__ . '/views/partials/reservation_modal.php'; ?>
                <? // modal rezerwacji miejsc (tylko dla zalogowanych użytkowników) ?>
            <?php endif; ?>
        </div>
    </div>

    <footer>
        <p>Projekt zespołowy: System rezerwacji miejsc UWB</p>
    </footer>
</body>

</html>