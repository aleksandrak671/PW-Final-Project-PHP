<?php
// usuwanie produktu z koszyka

require_once 'includes/config.php';

requireLogin();

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: koszyk.php');
    exit;
}

$userId = getCurrentUser()['id'];

// usuniecie pozycji z koszyka
$stmt = $pdo->prepare("DELETE FROM koszyk WHERE id = ? AND uzytkownik_id = ?");
$stmt->execute([$id, $userId]);

setFlashMessage('Pozycja została usunięta z koszyka.', 'success');
header('Location: koszyk.php');
exit;
