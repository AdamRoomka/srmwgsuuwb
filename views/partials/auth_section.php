<div class="header-user">
    <?php if ($currentUser): ?>
        <div class="user-box">
            <div>
                <div><?php echo htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']); ?></div>
                <div class="user-role"><?php echo htmlspecialchars($currentUser['role']); ?></div>
            </div>
        </div>
        <a class="logout-link" href="?logout=1">Wyloguj</a>

        <?php if ($currentUser['role'] === 'ADMINISTRATOR'): ?>
            <button onclick="window.location.href='admin.php';" class="btn btn-primary">Panel Administratora</button>
        <?php endif; ?>
    <?php else: ?>
        <div>
            <form method="post" class="login-form-inline">
                <input type="email" name="login_email" placeholder="Email" required>
                <input type="password" name="login_password" placeholder="Hasło" required>
                <button type="submit" class="btn btn-primary">Zaloguj</button>
                <button class="btn btn-primary" href="#" class="btn"
                    onclick="document.getElementById('registerModal').style.display='block'; return false;">
                    Zarejestruj się
                </button>
            </form>

            <?php if ($loginError): ?>
                <div class="login-error"><?php echo htmlspecialchars($loginError); ?></div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>