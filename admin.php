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

    $currentUser = $_SESSION['user'] ?? null;

    $userListStmt = $pdo->query("
        SELECT id, first_name, last_name, email, created_at, last_activity
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
                os.created_at, -- DODANO TĘ KOLUMNĘ
                GROUP_CONCAT(os.seat_number ORDER BY os.seat_number ASC SEPARATOR ', ') AS seat_numbers
            FROM occupied_seats os
            JOIN events e ON e.id = os.event_id
            WHERE os.user_id = :user_id
              AND os.status = 'AKTYWNA'
            GROUP BY e.id, e.name, e.start_at, os.created_at -- DODANO TĘ KOLUMNĘ TUTAJ
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

if ($currentUser['role'] != "ADMINISTRATOR") {
    $_SESSION['error'] = "Brak uprawnień do wyświetlenia tej strony.";
    header("Location: index.php");
    exit();
}

// User count
$totalUsersCount = $pdo->query("SELECT COUNT(id) FROM users")->fetchColumn();

$currentMonthUsersCount = $pdo->query("
    SELECT COUNT(id) 
    FROM users 
    WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) 
      AND YEAR(created_at) = YEAR(CURRENT_DATE())
")->fetchColumn();

// Administratorow count
$adminsCount = $pdo->query("
    SELECT COUNT(u.id) 
    FROM users u
    JOIN roles r ON u.role_id = r.id
    WHERE r.name = 'ADMINISTRATOR' OR r.name = 'Administrator'
")->fetchColumn();

// active users count
$regularUsersCount = $pdo->query("
    SELECT COUNT(u.id) 
    FROM users u
    JOIN roles r ON u.role_id = r.id
    WHERE r.name != 'admin' AND r.name != 'Administrator'
")->fetchColumn();

function renderUserRows($users)
{
    foreach ($users as $userItem): ?>
        <tr class="user-row" data-role="<?= $userItem['id'] == 1 ? 'ADMINISTRATOR' : 'USER'; ?>"
            data-search="<?= strtolower($userItem['first_name'] . ' ' . $userItem['last_name'] . ' ' . $userItem['email']); ?>">
            <td>
                <div class="user-info">
                    <div class="user-avatar"><?= strtoupper(substr($userItem['first_name'], 0, 1)); ?></div>
                    <div class="user-details">
                        <strong><?= htmlspecialchars($userItem['first_name'] . ' ' . $userItem['last_name']); ?></strong>
                        <span><?= $userItem['id'] == 1 ? 'Administrator' : 'Użytkownik'; ?></span>
                    </div>
                </div>
            </td>
            <td>
                <div class="emain-info"><?= htmlspecialchars($userItem['email']); ?></div>
            </td>
            <td>
                <select class="role-select">
                    <option value="USER" <?= $userItem['id'] != 1 ? 'selected' : ''; ?>>Użytkownik</option>
                    <option value="ADMINISTRATOR" <?= $userItem['id'] == 1 ? 'selected' : ''; ?>>Administrator</option>
                </select>
            </td>
            <td><?= $userItem['last_activity'] ? (new DateTime($userItem['last_activity']))->format('d.m.Y H:i') : '-'; ?></td>
            <td><?= (new DateTime($userItem['created_at']))->format('d.m.Y'); ?></td>
            <td>
                <div class="table-actions">
                    <button class="action-button reset-password-button disabled"><i class="fa-solid fa-key"></i> Resetuj
                        hasło</button>
                    <button class="action-button delete-user-button"><i class="fa-solid fa-trash"></i> Usuń</button>
                </div>
            </td>
        </tr>
    <?php endforeach;
}

if (isset($_GET['ajax_refresh'])) {
    $stmt = $pdo->query("SELECT id, first_name, last_name, email, created_at, last_activity FROM users ORDER BY first_name ASC, last_name ASC, email ASC");
    renderUserRows($stmt->fetchAll());
    exit;
}

updateLastActivity($pdo, $currentUser);

?>

<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Rezerwacji - Admin</title>
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script>
        window.currentUserId = <?php echo $currentUser ? (int) $currentUser['id'] : 'null'; ?>;
        window.appData = window.appData || {};
        window.appData.currentUserId = window.currentUserId;
    </script>
    <script src="assets/js/app.js"></script>
    <script src="assets/js/auth.js"></script>
    <script src="assets/js/seat-map.js"></script>
</head>

<body>
    <header>
        <div class="header-container">
            <div class="header-left">
                <a href="/" class="logo-box">
                    <img src="./IMG/uwb_wilno_logo.png" alt="Filia UwB w Wilnie" class="logo" />
                </a>
                <h1>Zarządzanie kontami użytkowników</h1>
            </div>

            <?php require __DIR__ . '/views/partials/auth_section.php'; ?>
        </div>
    </header>

    <main class="admin-page">
        <section class="admin-top-section">
            <div class="admin-stats-grid">
                <div class="admin-stat-card">
                    <div class="admin-stat-icon purple">
                        <i class="fa-solid fa-users"></i>
                    </div>

                    <div class="admin-stat-content">
                        <span>Wszyscy użytkownicy</span>
                        <strong>
                            <?= (int) $totalUsersCount ?>
                        </strong>
                        <small>
                            <?= $currentMonthUsersCount > 0 ? '+' . (int) $currentMonthUsersCount : '0' ?>
                            w tym miesiącu
                        </small>
                    </div>
                </div>

                <div class="admin-stat-card">
                    <div class="admin-stat-icon green">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>

                    <div class="admin-stat-content">
                        <span>Administratorzy</span>
                        <strong>
                            <?= (int) $adminsCount ?>
                        </strong>
                        <small>Aktywne konta</small>
                    </div>
                </div>

                <div class="admin-stat-card">
                    <div class="admin-stat-icon orange">
                        <i class="fa-solid fa-user"></i>
                    </div>

                    <div class="admin-stat-content">
                        <span>Użytkownicy</span>
                        <strong>
                            <?= (int) $regularUsersCount ?>
                        </strong>
                        <small>Aktywne konta</small>
                    </div>
                </div>

                <div class="admin-stat-card disabled">
                    <div class="admin-stat-icon red">
                        <i class="fa-solid fa-envelope"></i>
                    </div>

                    <div class="admin-stat-content">
                        <span>Wysłane linki resetujące</span>
                        <strong>0</strong>
                        <small>W tym miesiącu</small>
                    </div>
                </div>
            </div>
        </section>

        <section class="admin-users-section">
            <div class="admin-filters">
                <div class="admin-search-box">
                    <i class="fa-solid fa-magnifying-glass"></i>

                    <input type="text" id="userSearchInput" placeholder="Szukaj użytkownika (imię, nazwisko, email)...">
                </div>

                <div class="admin-filter-right">
                    <select id="roleFilter">
                        <option value="ALL">Wszystkie role</option>
                        <option value="ADMINISTRATOR">Administratorzy</option>
                        <option value="USER">Użytkownicy</option>
                    </select>

                    <button class="refresh-button" id="refreshTable">
                        <i class="fa-solid fa-rotate-right"></i>
                        Odśwież
                    </button>
                </div>
            </div>

            <div class="admin-table-wrapper">
                <table class="admin-users-table">
                    <thead>
                        <tr>
                            <th>Użytkownik</th>
                            <th>Email</th>
                            <th>Rola</th>
                            <th>Ostatnia aktywność</th>
                            <th>Dołączył</th>
                            <th>Akcje</th>
                        </tr>
                    </thead>

                    <tbody id="usersTableBody">
                        <?php renderUserRows($usersForAdminSeatAssignment); ?>
                    </tbody>
                </table>
            </div>
        </section>

        <div class="admin-pagination">
            <button class="pagination-button" id="prevPageButton">
                <i class="fa-solid fa-chevron-left"></i>
            </button>
            <div class="pagination-pages" id="paginationPages"></div>
            <button class="pagination-button" id="nextPageButton">
                <i class="fa-solid fa-chevron-right"></i>
            </button>
        </div>

    </main>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const searchInput = document.getElementById('userSearchInput');
            const roleFilter = document.getElementById('roleFilter');
            let rows = Array.from(document.querySelectorAll('.user-row'));
            const paginationContainer = document.getElementById('paginationPages');
            const previousButton = document.getElementById('prevPageButton');
            const nextButton = document.getElementById('nextPageButton');
            const refreshButton = document.getElementById('refreshTable');
            const itemsPerPage = 6;
            let currentPage = 1;
            let visibleRows = [...rows];


            refreshButton.addEventListener('click', () => {
                const icon = refreshButton.querySelector('i');
                icon.classList.add('fa-spin');

                fetch(window.location.pathname + '?ajax_refresh=1')
                    .then(res => res.text())
                    .then(html => {
                        const tbody = document.getElementById('usersTableBody');
                        tbody.innerHTML = html;
                        rows = Array.from(tbody.querySelectorAll('.user-row'));
                        filterUsers();

                        setTimeout(() => icon.classList.remove('fa-spin'), 600);
                    });
            });

            function filterUsers() {
                const search = searchInput.value.toLowerCase().trim();
                const role = roleFilter.value;

                visibleRows = rows.filter(row => {
                    const text = row.dataset.search;
                    const userRole = row.dataset.role;
                    const matchesSearch = text.includes(search);
                    const matchesRole = role === 'ALL' || role === userRole;
                    return matchesSearch && matchesRole;
                });

                currentPage = 1;
                renderPage();
                generatePagination();
            }

            function renderPage() {
                rows.forEach(row => {
                    row.style.display = 'none';
                });

                const start = (currentPage - 1) * itemsPerPage;
                const end = start + itemsPerPage;
                const pageItems = visibleRows.slice(start, end);

                pageItems.forEach(row => {
                    row.style.display = '';
                });

                const maxPage = Math.ceil(
                    visibleRows.length /
                    itemsPerPage
                );

                previousButton.disabled = currentPage === 1;
                nextButton.disabled = currentPage === maxPage || maxPage === 0;
            }

            function generatePagination() {
                paginationContainer.innerHTML = '';

                const totalPages = Math.ceil(
                    visibleRows.length /
                    itemsPerPage
                );

                if (totalPages <= 1) {
                    return;
                }

                for (let i = 1; i <= totalPages; i++) {
                    const button = document.createElement('button');
                    button.className = 'pagination-page';
                    button.textContent = i;

                    if (i === currentPage) {
                        button.classList.add('active');
                    }

                    button.addEventListener('click', () => {
                        currentPage = i;
                        renderPage();
                        generatePagination();
                    });
                    paginationContainer.appendChild(button);
                }
            }

            previousButton.addEventListener('click', () => {
                if (currentPage > 1) {
                    currentPage--;
                    renderPage();
                    generatePagination();
                }
            });

            nextButton.addEventListener('click', () => {
                const maxPage = Math.ceil(
                    visibleRows.length /
                    itemsPerPage
                );
                if (currentPage < maxPage) {
                    currentPage++;
                    renderPage();
                    generatePagination();
                }
            });
            searchInput.addEventListener(
                'input',
                filterUsers
            );
            roleFilter.addEventListener(
                'change',
                filterUsers
            );
            filterUsers();
        });
    </script>
</body>

</html>