<?php
// panel administracyjny - szczegoly zamowienia

require_once '../includes/config.php';
$isAdmin = true;

requireLogin('../login.php');
requireAdmin();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: zamowienia.php');
    exit;
}

// aktualizacja statusu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status'])) {
    $nowyStatus = $_POST['status'];
    $dozwolone = ['nowe', 'w_realizacji', 'wyslane', 'dostarczone', 'anulowane'];
    
    if (in_array($nowyStatus, $dozwolone)) {
        $stmt = $pdo->prepare("UPDATE zamowienia SET status = ? WHERE id = ?");
        $stmt->execute([$nowyStatus, $id]);
        setFlashMessage('success', 'Status zamówienia został zaktualizowany.');
    }
}

// pobieranie zamowienia
$stmt = $pdo->prepare("
    SELECT z.*, u.login, u.email as user_email 
    FROM zamowienia z 
    LEFT JOIN uzytkownicy u ON z.uzytkownik_id = u.id 
    WHERE z.id = ?
");
$stmt->execute([$id]);
$zamowienie = $stmt->fetch();

if (!$zamowienie) {
    header('Location: zamowienia.php');
    exit;
}

// pobieranie pozycji zamowienia
$stmt = $pdo->prepare("
    SELECT pz.*, k.tytul 
    FROM pozycje_zamowienia pz 
    JOIN ksiazki k ON pz.ksiazka_id = k.id 
    WHERE pz.zamowienie_id = ?
");
$stmt->execute([$id]);
$pozycje = $stmt->fetchAll();

$pageTitle = 'Zamówienie #' . $id;
include '../includes/header.php';
?>

<div class="admin-header">
    <h1>Zamówienie #<?php echo $id; ?></h1>
    <a href="zamowienia.php" class="btn">Powrót do listy</a>
</div>

<div class="order-details">
    <div class="order-section">
        <h2>Dane zamówienia</h2>
        <p><strong>Data:</strong> <?php echo date('d.m.Y H:i', strtotime($zamowienie['data_zamowienia'])); ?></p>
        <p><strong>Status:</strong> 
            <span class="status status-<?php echo $zamowienie['status']; ?>">
                <?php 
                $statusy = [
                    'nowe' => 'Nowe',
                    'w_realizacji' => 'W realizacji',
                    'wyslane' => 'Wysłane',
                    'dostarczone' => 'Dostarczone',
                    'anulowane' => 'Anulowane'
                ];
                echo $statusy[$zamowienie['status']] ?? $zamowienie['status'];
                ?>
            </span>
        </p>
        <p><strong>Suma:</strong> <?php echo formatPrice($zamowienie['suma_zamowienia']); ?></p>
    </div>
    
    <div class="order-section">
        <h2>Dane odbiorcy</h2>
        <p><strong>Imię i nazwisko:</strong> <?php echo h($zamowienie['imie_odbiorcy'] . ' ' . $zamowienie['nazwisko_odbiorcy']); ?></p>
        <p><strong>Email:</strong> <?php echo h($zamowienie['email']); ?></p>
        <p><strong>Telefon:</strong> <?php echo h($zamowienie['telefon']); ?></p>
        <p><strong>Adres:</strong> <?php echo h($zamowienie['ulica'] . ' ' . $zamowienie['nr_domu']); ?></p>
        <p><strong>Miasto:</strong> <?php echo h($zamowienie['kod_pocztowy'] . ' ' . $zamowienie['miasto']); ?></p>
    </div>
    
    <div class="order-section">
        <h2>Zmień status</h2>
        <form method="post" style="display: flex; gap: 10px; align-items: center;">
            <select name="status" class="form-control" style="max-width: 200px;">
                <option value="nowe" <?php echo $zamowienie['status'] === 'nowe' ? 'selected' : ''; ?>>Nowe</option>
                <option value="w_realizacji" <?php echo $zamowienie['status'] === 'w_realizacji' ? 'selected' : ''; ?>>W realizacji</option>
                <option value="wyslane" <?php echo $zamowienie['status'] === 'wyslane' ? 'selected' : ''; ?>>Wysłane</option>
                <option value="dostarczone" <?php echo $zamowienie['status'] === 'dostarczone' ? 'selected' : ''; ?>>Dostarczone</option>
                <option value="anulowane" <?php echo $zamowienie['status'] === 'anulowane' ? 'selected' : ''; ?>>Anulowane</option>
            </select>
            <button type="submit" class="btn">Zapisz</button>
        </form>
    </div>
</div>

<h2>Pozycje zamówienia</h2>
<table class="admin-table">
    <thead>
        <tr>
            <th>Książka</th>
            <th>Cena</th>
            <th>Ilość</th>
            <th>Wartość</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($pozycje as $p): ?>
            <tr>
                <td><?php echo h($p['tytul']); ?></td>
                <td><?php echo formatPrice($p['cena']); ?></td>
                <td><?php echo $p['ilosc']; ?></td>
                <td><?php echo formatPrice($p['cena'] * $p['ilosc']); ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="3"><strong>Suma:</strong></td>
            <td><strong><?php echo formatPrice($zamowienie['suma_zamowienia']); ?></strong></td>
        </tr>
    </tfoot>
</table>

<?php include '../includes/footer.php'; ?>
