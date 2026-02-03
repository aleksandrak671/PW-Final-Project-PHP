<?php
// panel administracyjny - lista zamowien

require_once '../includes/config.php';
$isAdmin = true;

requireLogin('../login.php');
requireAdmin();

// pobieranie zamowien
$stmt = $pdo->query("
    SELECT z.*, u.login 
    FROM zamowienia z 
    LEFT JOIN uzytkownicy u ON z.uzytkownik_id = u.id 
    ORDER BY z.data_zamowienia DESC
");
$zamowienia = $stmt->fetchAll();

$pageTitle = 'Zarządzanie zamówieniami';
include '../includes/header.php';
?>

<div class="admin-header">
    <h1>Zamówienia</h1>
</div>

<table class="admin-table">
    <thead>
        <tr>
            <th>Nr</th>
            <th>Data</th>
            <th>Klient</th>
            <th>Email</th>
            <th>Kwota</th>
            <th>Status</th>
            <th>Akcje</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($zamowienia as $z): ?>
            <tr>
                <td>#<?php echo $z['id']; ?></td>
                <td><?php echo date('d.m.Y H:i', strtotime($z['data_zamowienia'])); ?></td>
                <td><?php echo h($z['imie_odbiorcy'] . ' ' . $z['nazwisko_odbiorcy']); ?></td>
                <td><?php echo h($z['email']); ?></td>
                <td><?php echo formatPrice($z['suma_zamowienia']); ?></td>
                <td>
                    <span class="status status-<?php echo $z['status']; ?>">
                        <?php 
                        $statusy = [
                            'nowe' => 'Nowe',
                            'w_realizacji' => 'W realizacji',
                            'wyslane' => 'Wysłane',
                            'dostarczone' => 'Dostarczone',
                            'anulowane' => 'Anulowane'
                        ];
                        echo $statusy[$z['status']] ?? $z['status'];
                        ?>
                    </span>
                </td>
                <td>
                    <a href="zamowienie_szczegoly.php?id=<?php echo $z['id']; ?>" class="btn btn-small">Szczegóły</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include '../includes/footer.php'; ?>
