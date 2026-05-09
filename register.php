<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_register'])) {
    $fn = trim($_POST['first_name'] ?? '');
    $ln = trim($_POST['last_name'] ?? '');
    $em = trim($_POST['email'] ?? '');
    $pw = $_POST['password'] ?? '';

    if (empty($fn) || empty($ln) || empty($em) || strlen($pw) < 6) {
        echo json_encode(['status' => 'error', 'message' => 'Wypełnij poprawnie wszystkie pola (hasło min. 6 znaków).']);
        exit;
    }

    try {
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$em]);

        if ($check->fetch()) {
            echo json_encode(['status' => 'error', 'message' => 'Ten email jest już zajęty.']);
        } else {
            $hash = password_hash($pw, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (role_id, first_name, last_name, email, password_hash) VALUES (2, ?, ?, ?, ?)");

            if ($stmt->execute([$fn, $ln, $em, $hash])) {
                $userId = $pdo->lastInsertId();

                $_SESSION['user'] = [
                    'id' => $userId,
                    'first_name' => $fn,
                    'last_name' => $ln,
                    'email' => $em,
                    'role' => 'UZYTKOWNIK'
                ];

                echo json_encode(['status' => 'success', 'message' => 'Rejestracja pomyślna! Zaraz zostaniesz zalogowany.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Błąd zapisu w bazie.']);
            }
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Błąd serwera.']);
    }
    exit;
}
?>

<div id="registerModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Rejestracja użytkownika</h2>
            <span class="close" onclick="document.getElementById('registerModal').style.display='none'">&times;</span>
        </div>

        <form id="registerForm" method="POST">
            <input type="hidden" name="ajax_register" value="1">

            <div class="form-group">
                <label>Imię</label>
                <input type="text" name="first_name" required>
            </div>

            <div class="form-group">
                <label>Nazwisko</label>
                <input type="text" name="last_name" required>
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>

            <div class="form-group">
                <label>Hasło</label>
                <input type="password" name="password" required>
            </div>

            <div id="registerMessage"></div>

            <div class="modal-footer">
                <button type="button" class="btn-cancel"
                    onclick="document.getElementById('registerModal').style.display='none'">Anuluj</button>
                <button type="submit" class="btn-register-submit">Zarejestruj się</button>
            </div>
        </form>
    </div>
</div>