<?php
// aktualizacja ilosci produktu w koszyku

require_once 'includes/config.php';

requireLogin();

$id = (int)($_POST['id'] ?? 0);
$ilosc = (int)($_POST['ilosc'] ?? 1);

if ($id <= 0) {
    header('Location: koszyk.php');
    exit;
}

$userId = getCurrentUser()['id'];

// sprawdzenie czy pozycja nalezy do uzytkownika
$stmt = $pdo->prepare("SELECT k.id, k.ksiazka_id, ks.stan_magazynowy FROM koszyk k JOIN ksiazki ks ON k.ksiazka_id = ks.id WHERE k.id = ? AND k.uzytkownik_id = ?");
$stmt->execute([$id, $userId]);
$pozycja = $stmt->fetch();

if (!$pozycja) {
    header('Location: koszyk.php');
    exit;
}

// walidacja ilosci
if ($ilosc <= 0) {
    // usuniecie pozycji
    $stmt = $pdo->prepare("DELETE FROM koszyk WHERE id = ?");
    $stmt->execute([$id]);
    setFlashMessage('Pozycja została usunięta z koszyka.', 'success');
} else {
    // sprawdzenie stanu magazynowego
    if ($ilosc > $pozycja['stan_magazynowy']) {
        $ilosc = $pozycja['stan_magazynowy'];
        setFlashMessage('Ilość została ograniczona do stanu magazynowego.', 'info');
    }
    
    $stmt = $pdo->prepare("UPDATE koszyk SET ilosc = ? WHERE id = ?");
    $stmt->execute([$ilosc, $id]);
}

header('Location: koszyk.php');
exit;
