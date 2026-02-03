<?php
// strona moich zamowien
// wyswietla liste zamowien zalogowanego uzytkownika

require_once 'includes/config.php';
requireLogin();

// pobranie zamowien uzytkownika
$stmt = $pdo->prepare("
    SELECT * FROM zamowienia 
    WHERE uzytkownik_id = ? 
    ORDER BY data_zamowienia DESC
");
$stmt->execute([$_SESSION['user_id']]);
$zamowienia = $stmt->fetchAll();

$pageTitle = 'Moje zamówienia';
include 'includes/header.php';
?>

<div class="page-header">
    <h1>Moje zamówienia</h1>
</div>

<?php if (empty($zamowienia)): ?>
    <div class="empty-state">
        <p>Nie masz jeszcze żadnych zamówień.</p>
        <a href="index.php" class="btn btn-primary">Przeglądaj książki</a>
    </div>
<?php else: ?>
    <table class="orders-table">
        <thead>
            <tr>
                <th>Nr zamówienia</th>
                <th>Data</th>
                <th>Kwota</th>
                <th>Status</th>
                <th>Szczegóły</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($zamowienia as $z): ?>
                <tr>
                    <td>#<?php echo $z['id']; ?></td>
                    <td><?php echo date('d.m.Y H:i', strtotime($z['data_zamowienia'])); ?></td>
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
                        <button class="btn btn-small" onclick="toggleDetails(<?php echo $z['id']; ?>)">
                            Pokaż
                        </button>
                    </td>
                </tr>
                <tr id="details-<?php echo $z['id']; ?>" class="order-details-row" style="display: none;">
                    <td colspan="5">
                        <?php
                        $stmt = $pdo->prepare("
                            SELECT zp.*, k.tytul 
                            FROM pozycje_zamowienia zp 
                            JOIN ksiazki k ON zp.ksiazka_id = k.id 
                            WHERE zp.zamowienie_id = ?
                        ");
                        $stmt->execute([$z['id']]);
                        $pozycje = $stmt->fetchAll();
                        ?>
                        <div class="order-details-content">
                            <h4>Zamówione produkty:</h4>
                            <ul>
                                <?php foreach ($pozycje as $poz): ?>
                                    <li>
                                        <?php echo h($poz['tytul']); ?> 
                                        x <?php echo $poz['ilosc']; ?> 
                                        = <?php echo formatPrice($poz['cena_jednostkowa'] * $poz['ilosc']); ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                            <p>
                                <strong>Adres dostawy:</strong><br>
                                <?php echo h($z['imie_odbiorcy'] . ' ' . $z['nazwisko_odbiorcy']); ?><br>
                                <?php echo h($z['ulica'] . ' ' . $z['numer_domu']); ?><br>
                                <?php echo h($z['kod_pocztowy'] . ' ' . $z['miasto']); ?>
                            </p>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <script>
    function toggleDetails(id) {
        var row = document.getElementById('details-' + id);
        row.style.display = row.style.display === 'none' ? 'table-row' : 'none';
    }
    </script>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
