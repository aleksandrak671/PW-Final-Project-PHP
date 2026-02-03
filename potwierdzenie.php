<?php
// strona potwierdzenia zamowienia
// wyswietlana po zlozeniu zamowienia

require_once 'includes/config.php';

if (!isset($_SESSION['ostatnie_zamowienie'])) {
    header('Location: index.php');
    exit;
}

$zamowienie_id = $_SESSION['ostatnie_zamowienie'];
unset($_SESSION['ostatnie_zamowienie']);

// pobranie danych zamowienia
$stmt = $pdo->prepare("SELECT * FROM zamowienia WHERE id = ?");
$stmt->execute([$zamowienie_id]);
$zamowienie = $stmt->fetch();

// pobranie pozycji zamowienia
$stmt = $pdo->prepare("
    SELECT zp.*, k.tytul 
    FROM pozycje_zamowienia zp 
    JOIN ksiazki k ON zp.ksiazka_id = k.id 
    WHERE zp.zamowienie_id = ?
");
$stmt->execute([$zamowienie_id]);
$pozycje = $stmt->fetchAll();

$pageTitle = 'Potwierdzenie zamówienia';
include 'includes/header.php';
?>

<div class="confirmation-page">
    <div class="success-icon">&#10003;</div>
    
    <h1>Dziękujemy za zamówienie!</h1>
    
    <p class="order-number">Numer zamówienia: <strong>#<?php echo $zamowienie_id; ?></strong></p>
    
    <p>Potwierdzenie zostało wysłane na adres: <strong><?php echo h($zamowienie['email']); ?></strong></p>
    
    <div class="order-details">
        <h3>Szczegóły zamówienia</h3>
        
        <table>
            <thead>
                <tr>
                    <th>Produkt</th>
                    <th>Ilość</th>
                    <th>Cena</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pozycje as $poz): ?>
                    <tr>
                        <td><?php echo h($poz['tytul']); ?></td>
                        <td><?php echo $poz['ilosc']; ?></td>
                        <td><?php echo formatPrice($poz['cena_jednostkowa'] * $poz['ilosc']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2"><strong>Suma:</strong></td>
                    <td><strong><?php echo formatPrice($zamowienie['suma_zamowienia']); ?></strong></td>
                </tr>
            </tfoot>
        </table>
        
        <h3>Adres dostawy</h3>
        <p>
            <?php echo h($zamowienie['imie_odbiorcy'] . ' ' . $zamowienie['nazwisko_odbiorcy']); ?><br>
            <?php echo h($zamowienie['ulica'] . ' ' . $zamowienie['numer_domu']); ?><br>
            <?php echo h($zamowienie['kod_pocztowy'] . ' ' . $zamowienie['miasto']); ?>
        </p>
    </div>
    
    <div class="actions">
        <a href="index.php" class="btn btn-primary">Kontynuuj zakupy</a>
        <?php if (isLoggedIn()): ?>
            <a href="moje_zamowienia.php" class="btn btn-secondary">Moje zamówienia</a>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
