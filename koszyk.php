<?php
// strona koszyka zakupowego
// wyswietla produkty dodane do koszyka z mozliwoscia edycji ilosci

require_once 'includes/config.php';

$produkty = [];
$suma = 0;

// pobieranie danych produktow z koszyka (z bazy danych)
if (isLoggedIn()) {
    $userId = getCurrentUser()['id'];
    
    $stmt = $pdo->prepare("
        SELECT k.id as koszyk_id, k.ilosc, ks.*, a.imie, a.nazwisko 
        FROM koszyk k 
        JOIN ksiazki ks ON k.ksiazka_id = ks.id 
        JOIN autorzy a ON ks.autor_id = a.id 
        WHERE k.uzytkownik_id = ?
    ");
    $stmt->execute([$userId]);
    $produkty = $stmt->fetchAll();
    
    // obliczanie sumy
    foreach ($produkty as $p) {
        $cena = $p['cena_promocyjna'] ?: $p['cena'];
        $suma += $cena * $p['ilosc'];
    }
}

$pageTitle = 'Koszyk';
include 'includes/header.php';
?>

<div class="page-header">
    <h1>Twój koszyk</h1>
</div>

<?php if (!isLoggedIn()): ?>
    <div class="empty-state">
        <p>Musisz się zalogować, aby zobaczyć koszyk.</p>
        <a href="login.php" class="btn btn-primary">Zaloguj się</a>
    </div>
<?php elseif (empty($produkty)): ?>
    <div class="empty-state">
        <p>Twój koszyk jest pusty.</p>
        <a href="index.php" class="btn btn-primary">Przeglądaj książki</a>
    </div>
<?php else: ?>
    <table class="cart-table">
        <thead>
            <tr>
                <th>Produkt</th>
                <th>Cena</th>
                <th>Ilość</th>
                <th>Wartość</th>
                <th>Akcje</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($produkty as $p): ?>
                <?php 
                $cena = $p['cena_promocyjna'] ?: $p['cena'];
                $wartosc = $cena * $p['ilosc'];
                ?>
                <tr>
                    <td class="product-info">
                        <a href="ksiazka.php?id=<?php echo $p['id']; ?>">
                            <strong><?php echo h($p['tytul']); ?></strong>
                        </a>
                        <br>
                        <small><?php echo h($p['imie'] . ' ' . $p['nazwisko']); ?></small>
                    </td>
                    <td>
                        <?php if ($p['cena_promocyjna']): ?>
                            <span class="old-price"><?php echo formatPrice($p['cena']); ?></span><br>
                            <span class="new-price"><?php echo formatPrice($p['cena_promocyjna']); ?></span>
                        <?php else: ?>
                            <?php echo formatPrice($p['cena']); ?>
                        <?php endif; ?>
                    </td>
                    <td>
                        <form action="aktualizuj_koszyk.php" method="POST" class="quantity-form">
                            <input type="hidden" name="id" value="<?php echo $p['koszyk_id']; ?>">
                            <input type="number" name="ilosc" value="<?php echo $p['ilosc']; ?>" min="1" max="<?php echo $p['stan_magazynowy']; ?>">
                            <button type="submit" class="btn btn-small">Zmień</button>
                        </form>
                    </td>
                    <td><strong><?php echo formatPrice($wartosc); ?></strong></td>
                    <td>
                        <a href="usun_z_koszyka.php?id=<?php echo $p['koszyk_id']; ?>" class="btn btn-danger btn-small">Usuń</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3"><strong>Suma:</strong></td>
                <td colspan="2"><strong class="total-price"><?php echo formatPrice($suma); ?></strong></td>
            </tr>
        </tfoot>
    </table>
    
    <div class="cart-actions">
        <a href="index.php" class="btn btn-secondary">Kontynuuj zakupy</a>
        <a href="zamowienie.php" class="btn btn-primary btn-large">Przejdź do zamówienia</a>
    </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
