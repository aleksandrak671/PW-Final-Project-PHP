<?php
// panel administracyjny - strona glowna
// wyswietla statystyki i podsumowanie

require_once '../includes/config.php';
$isAdmin = true;

requireLogin('../login.php');
requireAdmin();

// pobieranie statystyk
$stats = [];

$stmt = $pdo->query("SELECT COUNT(*) FROM ksiazki WHERE aktywna = 1");
$stats['ksiazki'] = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM uzytkownicy");
$stats['uzytkownicy'] = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM zamowienia");
$stats['zamowienia'] = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM zamowienia WHERE status = 'nowe'");
$stats['nowe_zamowienia'] = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COALESCE(SUM(suma_zamowienia), 0) FROM zamowienia WHERE status != 'anulowane'");
$stats['przychod'] = $stmt->fetchColumn();

// ostatnie zamowienia
$stmt = $pdo->query("
    SELECT z.*, u.login 
    FROM zamowienia z 
    LEFT JOIN uzytkownicy u ON z.uzytkownik_id = u.id 
    ORDER BY z.data_zamowienia DESC 
    LIMIT 5
");
$ostatnie_zamowienia = $stmt->fetchAll();

$pageTitle = 'Panel administracyjny';
include '../includes/header.php';
?>

<div class="admin-header">
    <h1>Panel administracyjny</h1>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-number"><?php echo $stats['ksiazki']; ?></div>
        <div class="stat-label">Książek</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?php echo $stats['uzytkownicy']; ?></div>
        <div class="stat-label">Użytkowników</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?php echo $stats['zamowienia']; ?></div>
        <div class="stat-label">Zamówień</div>
    </div>
    <div class="stat-card highlight">
        <div class="stat-number"><?php echo $stats['nowe_zamowienia']; ?></div>
        <div class="stat-label">Nowych zamówień</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?php echo formatPrice($stats['przychod']); ?></div>
        <div class="stat-label">Przychód</div>
    </div>
</div>

<div class="admin-section">
    <h2>Ostatnie zamówienia</h2>
    <?php if (empty($ostatnie_zamowienia)): ?>
        <p>Brak zamówień.</p>
    <?php else: ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Nr</th>
                    <th>Data</th>
                    <th>Klient</th>
                    <th>Kwota</th>
                    <th>Status</th>
                    <th>Akcje</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ostatnie_zamowienia as $z): ?>
                    <tr>
                        <td>#<?php echo $z['id']; ?></td>
                        <td><?php echo date('d.m.Y H:i', strtotime($z['data_zamowienia'])); ?></td>
                        <td><?php echo h($z['imie_odbiorcy'] . ' ' . $z['nazwisko_odbiorcy']); ?></td>
                        <td><?php echo formatPrice($z['suma_zamowienia']); ?></td>
                        <td><span class="status status-<?php echo $z['status']; ?>"><?php echo $z['status']; ?></span></td>
                        <td><a href="zamowienie_szczegoly.php?id=<?php echo $z['id']; ?>" class="btn btn-small">Szczegóły</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <a href="zamowienia.php" class="btn">Zobacz wszystkie</a>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
