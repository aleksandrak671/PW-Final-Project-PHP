<?php
// dodawanie produktu do koszyka

require_once 'includes/config.php';

requireLogin();

$ksiazkaId = (int)($_POST['ksiazka_id'] ?? 0);
$ilosc = (int)($_POST['ilosc'] ?? 1);

if ($ksiazkaId <= 0 || $ilosc <= 0) {
    setFlashMessage('Nieprawidłowe dane.', 'error');
    header('Location: index.php');
    exit;
}

// sprawdzenie czy ksiazka istnieje
$stmt = $pdo->prepare("SELECT id, stan_magazynowy FROM ksiazki WHERE id = ?");
$stmt->execute([$ksiazkaId]);
$ksiazka = $stmt->fetch();

if (!$ksiazka) {
    setFlashMessage('Książka nie została znaleziona.', 'error');
    header('Location: index.php');
    exit;
}

if ($ksiazka['stan_magazynowy'] < $ilosc) {
    setFlashMessage('Niewystarczająca ilość w magazynie.', 'error');
    header('Location: ksiazka.php?id=' . $ksiazkaId);
    exit;
}

$userId = getCurrentUser()['id'];

// sprawdzenie czy juz jest w koszyku
$stmt = $pdo->prepare("SELECT id, ilosc FROM koszyk WHERE uzytkownik_id = ? AND ksiazka_id = ?");
$stmt->execute([$userId, $ksiazkaId]);
$pozycja = $stmt->fetch();

if ($pozycja) {
    // aktualizacja ilosci
    $nowaIlosc = $pozycja['ilosc'] + $ilosc;
    $stmt = $pdo->prepare("UPDATE koszyk SET ilosc = ? WHERE id = ?");
    $stmt->execute([$nowaIlosc, $pozycja['id']]);
} else {
    // dodanie nowej pozycji
    $stmt = $pdo->prepare("INSERT INTO koszyk (uzytkownik_id, ksiazka_id, ilosc) VALUES (?, ?, ?)");
    $stmt->execute([$userId, $ksiazkaId, $ilosc]);
}

setFlashMessage('success', 'Książka została dodana do koszyka.');
header('Location: koszyk.php');
exit;
