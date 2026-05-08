<?php if ($reservationMessage || $reservationError): ?>
    <div id="toastNotification"
        class="toast-notification <?php echo $reservationError ? 'toast-error' : 'toast-success'; ?>">
        <div class="toast-content">
            <?php if ($reservationError): ?>
                <?php echo htmlspecialchars($reservationError); ?>
            <?php else: ?>
                <?php echo htmlspecialchars($reservationMessage); ?>
            <?php endif; ?>
        </div>
        <button type="button" class="toast-close" id="closeToast">&times;</button>
    </div>
<?php endif; ?>